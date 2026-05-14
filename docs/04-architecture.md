# Application Architecture

## How Livewire Replaces Traditional MVC

Laravel normally uses Controllers to handle HTTP requests. This project uses **Livewire**, which replaces Controllers with reactive PHP components. Each Livewire component is a PHP class paired with a Blade template. When a user clicks a button, Livewire makes an AJAX request to the server, runs the PHP method, and updates only the changed parts of the DOM — no page reload needed.

```
Traditional Laravel:          This Project:
  Route → Controller            Route → Livewire Component
  Controller → View               Component has public properties (state)
                                  Component has public methods (actions)
                                  Component renders a Blade template
```

---

## Models (`app/Models/`)

Eloquent classes representing database tables. They contain relationships, business logic, and query scopes.

### `User`
- Roles: `student`, `staff`, `admin`
- Helper methods: `isStudent()`, `isStaff()`, `isAdmin()`, `initials()`
- Supports 2FA via `TwoFactorAuthenticatable` trait
- `is_active` flag — deactivated users are logged out by middleware

### `Category`
- Scope: `scopeActive()` — filters to `is_active = true`

### `Product`
- Scopes: `scopeAvailable()`, `scopeInStock()`
- `imageUrl()` — returns public storage URL or null

### `Order`
- State machine via `canTransitionTo()` and `transitionTo()`
- `cancelAndRestoreStock()` — cancels order and increments stock for each item
- `generateOrderNumber()` — generates unique `ORD-XXXXXXXXX` identifier
- Scopes: `scopePending()`, `scopePaid()`, `scopePreparing()`, `scopeReady()`, `scopeCompleted()`, `scopeActive()`

### `OrderItem`
- `lineTotal()` — returns `quantity × unit_price`
- Product name and price are snapshotted — not linked live to the product

---

## Livewire Components (`app/Livewire/`)

### Student Components

**`MenuBrowser`** — `/menu`
- Loads available, in-stock products filtered by category and search
- `addToCart(productId)` — adds item, dispatches `toast` success
- `removeFromCart(productId)` — removes item, dispatches `toast` info
- `updateCartQuantity(productId, quantity)` — updates quantity
- Passes cart state to view for the sidebar and mobile bar

**`Checkout`** — `/checkout`
- On mount: cancels any abandoned pending orders older than 5 minutes
- Generates 15-minute pickup time slots between 7 AM and 5 PM
- `placeOrder()` — validates stock, creates Order + OrderItems, decrements stock, creates PayMongo checkout session, redirects to payment page
- On PayMongo failure: rolls back order and restores stock

**`OrderConfirmed`** — `/orders/{order}/confirmed`
- On mount: polls PayMongo API to confirm payment if order is still pending
- Handles the case where the webhook hasn't fired yet (local dev, delays)

**`OrderHistory`** — `/orders`
- Filters orders by `active`, `completed`, or `cancelled`

**`OrderStatus`** — `/orders/{order}`
- On mount: polls PayMongo if order is still pending
- `cancelOrder()` — cancels order and restores stock (pending only)
- Polls every 5 seconds via `wire:poll`
- Shows QR code SVG when order is `paid`, `preparing`, or `ready`

### Staff Components

**`Staff\Dashboard`** — `/staff`
- Two tabs: `orders` (live queue) and `stats` (charts)
- `advanceOrder(orderId)` — moves order to next status, dispatches toast
- `processQrCode()` — looks up order by number, completes if ready
- `refreshOrders()` — called by JS `setInterval` every 5 seconds (avoids `wire:poll` resetting state)
- Order detail modal is pure Alpine.js — immune to Livewire re-renders
- Stats tab: hourly sales line chart, stock health doughnut, top products bar chart

**`Staff\MenuManagement`** — `/staff/menu`
- Two tabs: `products` and `categories`
- Full CRUD for products (name, category, description, price, stock, image, availability)
- Full CRUD for categories (name, active status)
- Image uploads stored in `storage/app/public/products`

### Admin Components

**`Admin\Overview`** — `/admin`
- Total/today revenue, orders, active orders, student count, product count, low stock count
- 7-day revenue trend line chart
- Order status breakdown doughnut chart
- Category sales distribution bar chart
- Recent orders table
- Low stock products list

**`Admin\UserManagement`** — `/admin/users`
- Search by name or email, filter by role
- Create/edit users with role assignment and password
- `toggleActive(id)` — activates or deactivates users (cannot deactivate yourself)

**`Admin\SalesReports`** — `/admin/reports`
- Period filters: today, this week, this month, custom date range
- Total revenue, orders, completed, cancelled, average order value
- Daily sales breakdown table
- Top 10 products by units sold

---

## Services (`app/Services/`)

### `CartService`
Session-based shopping cart. Cart data is stored in the PHP session under the key `cart`.

| Method | Description |
|---|---|
| `add(productId, quantity)` | Adds item, validates stock, throws if over limit |
| `update(productId, quantity)` | Updates quantity, removes if 0 |
| `remove(productId)` | Removes item |
| `items()` | Returns all cart items as array |
| `count()` | Total item quantity |
| `subtotal()` | Total price |
| `clear()` | Empties cart |
| `isEmpty()` | Returns true if cart is empty |

### `PayMongoService`
Handles all PayMongo API communication.

| Method | Description |
|---|---|
| `createCheckoutSession(order, successUrl, cancelUrl)` | Creates a PayMongo checkout session via REST API, returns `checkout_id` and `checkout_url` |
| `confirmCheckoutSession(order)` | Polls PayMongo to check if session is paid, updates order if so. Checks `status`, `payment_status`, and `payments[0].status` |
| `verifyWebhook(payload, signatureHeader)` | Verifies webhook signature using the PayMongo PHP SDK |
| `handlePaymentPaid(event)` | Processes a `checkout_session.payment.paid` webhook event, updates order to paid |

### `QrCodeService`
Generates SVG QR codes using BaconQrCode.

| Method | Description |
|---|---|
| `generateSvg(data, size)` | Returns an SVG string encoding the given data (order number) |

---

## Middleware (`app/Http/Middleware/`)

### `EnsureUserHasRole`
Registered as the `role` middleware alias. Used in routes as `role:staff,admin` or `role:admin`.

- Checks that the authenticated user's role is in the allowed list
- If the user's `is_active` is false, logs them out and redirects to login with a message

---

## Policies (`app/Policies/`)

### `OrderPolicy`
| Action | Who Can |
|---|---|
| `view` | Order owner, staff, admin |
| `updateStatus` | Staff, admin |
| `cancel` | Order owner (pending only), admin |

### `ProductPolicy`
| Action | Who Can |
|---|---|
| `create` | Admin, staff |
| `update` | Admin, staff |
| `delete` | Admin, staff |

---

## Controllers (`app/Http/Controllers/`)

### `PayMongoWebhookController`
Invokable controller at `POST /webhooks/paymongo`. Excluded from CSRF and auth middleware.

1. Reads raw request body and `Paymongo-Signature` header
2. Verifies signature via `PayMongoService::verifyWebhook()`
3. If event type is `checkout_session.payment.paid`, calls `handlePaymentPaid()`
4. Returns 200 always (to prevent PayMongo retries on app errors)

### `PaymentCancelController`
Invokable controller at `GET /orders/{order}/payment-cancelled`. The `cancel_url` for PayMongo checkout sessions.

1. Verifies the authenticated user owns the order
2. Does a final PayMongo poll — in case the user actually paid and was misdirected
3. If still pending, calls `cancelAndRestoreStock()` and redirects to checkout with a warning
4. If already paid, redirects to order status page

---

## Providers (`app/Providers/`)

### `AppServiceProvider`
- Uses `CarbonImmutable` for all date handling
- Sets default password rules: min 8 chars, mixed case, letters, numbers, symbols
- Prohibits destructive DB commands in production

### `FortifyServiceProvider`
- Configures auth views (login, register, 2FA, password reset)
- Rate limits login to 5 attempts/minute per email+IP
- Rate limits 2FA to 5 attempts/minute per session
- Role-based post-login redirects: admin → `/admin`, staff → `/staff`, student → `/menu`
