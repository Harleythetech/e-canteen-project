# E-Canteen — System Architecture

A Laravel-based online ordering system for the Pamantasan ng Lungsod ng San Pablo (PLSP) school canteen. Students browse the menu, add items to a cart, pay via PayMongo (GCash, Card, GrabPay, PayMaya), and pick up orders using QR codes scanned by staff.

---

## Technology Stack

| Layer         | Technology                                                     |
| ------------- | -------------------------------------------------------------- |
| **Backend**   | Laravel 13 (PHP 8.4+)                                         |
| **Frontend**  | Livewire 4 + Flux UI + Alpine.js (ships with Livewire)        |
| **Styling**   | Tailwind CSS 4 (via `@tailwindcss/vite`)                      |
| **JS Libs**   | Chart.js (dashboard charts), html5-qrcode (QR scanner)        |
| **Auth**      | Laravel Fortify (login, register, 2FA, password reset)         |
| **Payments**  | PayMongo REST API (GCash, Card, GrabPay, PayMaya)              |
| **QR Codes**  | BaconQrCode (SVG generation)                                   |
| **Database**  | PostgreSQL                                                     |
| **Build**     | Vite 8                                                         |
| **Testing**   | Pest 4                                                         |

---

## Where Is the MVC?

Laravel follows the **MVC (Model-View-Controller)** pattern, but this project uses **Livewire** which replaces traditional Controllers with reactive **Livewire Components**. Here's how MVC maps to this codebase:

### Model → `app/Models/`

The Models are Eloquent classes that represent database tables and contain business logic, relationships, and validation. They are the **single source of truth** for data.

| Model       | Table          | Purpose                                   |
| ----------- | -------------- | ----------------------------------------- |
| `User`      | `users`        | Students, staff, and admin accounts       |
| `Category`  | `categories`   | Menu categories (Meals, Snacks, etc.)     |
| `Product`   | `products`     | Individual food/drink items               |
| `Order`     | `orders`       | Customer orders with status state machine |
| `OrderItem` | `order_items`  | Line items within an order                |

**Key Model Relationships:**

```
User ──hasMany──▶ Order ──hasMany──▶ OrderItem
                                          │
Category ──hasMany──▶ Product ◀──belongsTo─┘
```

**Order State Machine** (defined in `Order.php`):

```
pending ──▶ paid ──▶ preparing ──▶ ready ──▶ completed
  │           │
  ▼           ▼
cancelled  cancelled
```

Transitions are enforced by `Order::canTransitionTo()` and `Order::transitionTo()`. The `paid_at` and `completed_at` timestamps are auto-set on the respective transitions.

### View → `resources/views/`

Blade templates that render the UI. Organized by role and feature:

```
resources/views/
├── welcome.blade.php                  # Landing page
├── layouts/
│   ├── student.blade.php              # Student layout (top navbar + bottom mobile nav)
│   ├── admin.blade.php                # Admin layout (sidebar navigation)
│   ├── staff.blade.php                # Staff layout (sidebar navigation)
│   └── auth/                          # Auth page layouts (login, register, etc.)
├── livewire/
│   ├── menu-browser.blade.php         # Product catalog grid + cart sidebar
│   ├── checkout.blade.php             # Cart review + pickup time + payment
│   ├── order-confirmed.blade.php      # Post-payment confirmation
│   ├── order-history.blade.php        # "My Orders" list with filters
│   ├── order-status.blade.php         # Single order detail + QR code
│   ├── admin/
│   │   ├── overview.blade.php         # Dashboard stats + charts
│   │   ├── menu-management.blade.php  # Product/category CRUD
│   │   ├── user-management.blade.php  # User CRUD
│   │   └── sales-reports.blade.php    # Revenue reports + charts
│   └── staff/
│       └── dashboard.blade.php        # Order queue + QR scanner + stats
├── pages/
│   ├── auth/                          # Login, register, forgot-password, 2FA, etc.
│   └── settings/                      # Profile, security, appearance
└── components/                        # Shared Blade components (logo, patterns, etc.)
```

### Controller → `app/Livewire/` (Livewire Components)

In a traditional Laravel app, Controllers handle HTTP requests. Here, **Livewire Components** serve the same role — they receive user actions, run business logic, and return updated views. Each component is a PHP class paired with a Blade template.

| Component                   | File                                 | Role     | Purpose                                   |
| --------------------------- | ------------------------------------ | -------- | ----------------------------------------- |
| `MenuBrowser`               | `app/Livewire/MenuBrowser.php`       | Student  | Browse menu, search, add/remove cart items|
| `Checkout`                  | `app/Livewire/Checkout.php`          | Student  | Review cart, pick time, create order, pay |
| `OrderConfirmed`            | `app/Livewire/OrderConfirmed.php`    | Student  | Post-payment confirmation page            |
| `OrderHistory`              | `app/Livewire/OrderHistory.php`      | Student  | List orders with active/completed filter  |
| `OrderStatus`               | `app/Livewire/OrderStatus.php`       | Student  | View order detail + QR code for pickup    |
| `Staff\Dashboard`           | `app/Livewire/Staff/Dashboard.php`   | Staff    | Order queue, advance statuses, QR scanner |
| `Staff\MenuManagement`      | `app/Livewire/Staff/MenuManagement.php` | Staff | Product + category CRUD                   |
| `Admin\Overview`            | `app/Livewire/Admin/Overview.php`    | Admin    | Dashboard stats, charts, low stock alerts |
| `Admin\UserManagement`      | `app/Livewire/Admin/UserManagement.php` | Admin | User CRUD + activation toggle             |
| `Admin\SalesReports`        | `app/Livewire/Admin/SalesReports.php`| Admin    | Revenue reports by period                 |

There are also **two traditional Controllers**:

| Controller                    | File                                              | Purpose                            |
| ----------------------------- | ------------------------------------------------- | ---------------------------------- |
| `PaymentCancelController`    | `app/Http/Controllers/PaymentCancelController.php` | Handles payment cancellation, polls PayMongo before cancelling, restores cart |
| `PayMongoWebhookController`  | `app/Http/Controllers/PayMongoWebhookController.php` | Receives PayMongo payment webhooks |

This is an invokable controller because webhooks are server-to-server HTTP calls — they don't render UI, so Livewire isn't appropriate.

---

## Supporting Architecture

### Services → `app/Services/`

Service classes encapsulate reusable business logic that doesn't belong in a Model or Component:

| Service           | Purpose                                                             |
| ----------------- | ------------------------------------------------------------------- |
| `CartService`     | Session-based shopping cart (add, update, remove, subtotal, clear)  |
| `PayMongoService` | PayMongo API integration (checkout sessions, webhook verification, payment processing) |
| `QrCodeService`   | Generates SVG QR codes for order pickup verification                |

### Policies → `app/Policies/`

Authorization rules that determine who can perform actions:

| Policy          | Rules                                                                                  |
| --------------- | -------------------------------------------------------------------------------------- |
| `OrderPolicy`   | **view**: owner, staff, or admin · **updateStatus**: staff or admin · **cancel**: owner if pending, or admin |
| `ProductPolicy`  | **create/update/delete**: admin only                                                   |

### Middleware → `app/Http/Middleware/`

| Middleware          | Purpose                                                                        |
| ------------------- | ------------------------------------------------------------------------------ |
| `EnsureUserHasRole` | Registered as `role` — checks user role against allowed roles, also blocks deactivated users |

Used in routes as `role:staff,admin` or `role:admin`.

### Providers → `app/Providers/`

| Provider              | Purpose                                                               |
| --------------------- | --------------------------------------------------------------------- |
| `AppServiceProvider`  | Uses CarbonImmutable, production password rules, prohibits destructive DB commands |
| `FortifyServiceProvider` | Configures auth actions, views, rate limiting, role-based redirects |

---

## Database Schema

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│    users      │     │  categories  │     │   products   │
├──────────────┤     ├──────────────┤     ├──────────────┤
│ id           │     │ id           │     │ id           │
│ name         │     │ name         │     │ category_id  │──▶ categories.id
│ email        │     │ slug         │     │ name         │
│ password     │     │ sort_order   │     │ slug         │
│ role         │     │ is_active    │     │ description  │
│ is_active    │     │ timestamps   │     │ price        │
│ 2FA columns  │     └──────────────┘     │ image_path   │
│ timestamps   │                          │ stock        │
└──────┬───────┘                          │ is_available │
       │                                  │ sort_order   │
       │ hasMany                          │ timestamps   │
       ▼                                  └──────────────┘
┌──────────────┐                                 ▲
│   orders      │                                 │ belongsTo
├──────────────┤     ┌──────────────┐             │
│ id           │     │ order_items   │             │
│ user_id      │──┐  ├──────────────┤             │
│ order_number │  │  │ id           │             │
│ status       │  │  │ order_id     │──▶ orders.id│
│ pickup_time  │  │  │ product_id   │─────────────┘
│ special_inst │  │  │ product_name │
│ subtotal     │  │  │ quantity     │
│ total        │  │  │ unit_price   │
│ payment_*    │  │  │ timestamps   │
│ paid_at      │  │  └──────────────┘
│ completed_at │  │
│ timestamps   │  └── hasMany ──▶ order_items
└──────────────┘
```

---

## Routes

### Public Routes (no auth)

| Method | URI         | Handler          | Name       |
| ------ | ----------- | ---------------- | ---------- |
| GET    | `/`         | `welcome` view   | `home`     |
| GET    | `/privacy`  | `pages.privacy`  | `privacy`  |
| GET    | `/terms`    | `pages.terms`    | `terms`    |
| GET    | `/support`  | `pages.support`  | `support`  |

### Student Routes (auth required)

| Method | URI                                   | Handler                    | Name                        |
| ------ | ------------------------------------- | -------------------------- | --------------------------- |
| GET    | `/dashboard`                          | Role-based redirect        | `dashboard`                 |
| GET    | `/menu`                               | `MenuBrowser`              | `menu`                      |
| GET    | `/checkout`                           | `Checkout`                 | `checkout`                  |
| GET    | `/orders`                             | `OrderHistory`             | `orders.index`              |
| GET    | `/orders/{order}`                     | `OrderStatus`              | `orders.show`               |
| GET    | `/orders/{order}/confirmed`           | `OrderConfirmed`           | `orders.confirmed`          |
| GET    | `/orders/{order}/payment-cancelled`   | `PaymentCancelController`  | `orders.payment-cancelled`  |

### Staff Routes (`role:staff,admin`)

| Method | URI           | Handler                  | Name              |
| ------ | ------------- | ------------------------ | ----------------- |
| GET    | `/staff`      | `Staff\Dashboard`        | `staff.dashboard` |
| GET    | `/staff/menu` | `Staff\MenuManagement`   | `staff.menu`      |

### Admin Routes (`role:admin`)

| Method | URI              | Handler                 | Name              |
| ------ | ---------------- | ----------------------- | ----------------- |
| GET    | `/admin`         | `Admin\Overview`        | `admin.dashboard` |
| GET    | `/admin/users`   | `Admin\UserManagement`  | `admin.users`     |
| GET    | `/admin/reports` | `Admin\SalesReports`    | `admin.reports`   |

### Webhook (no auth/CSRF)

| Method | URI                  | Handler                     | Name                |
| ------ | -------------------- | --------------------------- | ------------------- |
| POST   | `/webhooks/paymongo` | `PayMongoWebhookController` | `webhooks.paymongo` |

---

## Key Business Flows

### 1. Order & Payment Flow

```
Student                    System                         PayMongo
  │                          │                               │
  ├─ Browse menu ──────────▶ │ MenuBrowser loads products    │
  ├─ Add to cart ──────────▶ │ CartService stores in session │
  ├─ Checkout ─────────────▶ │ Checkout component            │
  │                          │  ├─ Validates stock           │
  │                          │  ├─ Creates Order (pending)   │
  │                          │  ├─ Creates OrderItems        │
  │                          │  ├─ Decrements stock          │
  │                          │  └─ Creates checkout session ─┼──▶ PayMongo API
  │                          │                               │
  ├─ Redirect to PayMongo ◀─┤                               │
  │                          │                               │
  ├─ Pay (GCash/Card/etc) ──┼───────────────────────────────▶│
  │                          │                               │
  │                          │◀── Webhook: payment.paid ─────┤
  │                          │  └─ Order → paid              │
  │                          │                               │
  ├─ Redirected back ───────▶│ OrderConfirmed page           │
  │                          │                               │
  ├─ View QR code ──────────▶│ OrderStatus shows QR          │
  └──────────────────────────┘                               │
```

### 2. Order Fulfillment Flow (Staff)

```
Staff                         System
  │                             │
  ├─ View order queue ────────▶ │ Staff\Dashboard lists orders
  ├─ Mark Paid (pending→paid) ▶ │ advanceOrder() → transitionTo('paid')
  ├─ Start Preparing ─────────▶ │ advanceOrder() → transitionTo('preparing')
  ├─ Mark Ready ──────────────▶ │ advanceOrder() → transitionTo('ready')
  │                             │
  ├─ Scan student QR code ────▶ │ processQrCode()
  │                             │  └─ Order (ready) → completed
  └─────────────────────────────┘
```

### 3. Authentication Flow

```
User                          Fortify                      System
  │                             │                            │
  ├─ POST /login ──────────────▶│ Validates credentials      │
  │                             │ Rate limited (5/min)       │
  │                             │                            │
  │  If 2FA enabled:           │                            │
  ├─ 2FA challenge ────────────▶│ Validates TOTP code        │
  │                             │                            │
  ├─ Redirect by role: ◀───────┤                            │
  │   admin  → /admin          │                            │
  │   staff  → /staff          │                            │
  │   student → /menu          │                            │
  └─────────────────────────────┘                            │
```

---

## Role-Based Access Summary

| Feature                    | Student | Staff | Admin |
| -------------------------- | :-----: | :---: | :---: |
| Browse menu & order        |    ✓    |       |       |
| View own orders & QR code  |    ✓    |       |       |
| Cancel own pending order   |    ✓    |       |       |
| Manage order queue         |         |   ✓   |   ✓   |
| Scan QR codes              |         |   ✓   |   ✓   |
| View today's stats         |         |   ✓   |   ✓   |
| Dashboard overview         |         |       |   ✓   |
| Menu/category CRUD         |         |       |   ✓   |
| User management            |         |       |   ✓   |
| Sales reports              |         |       |   ✓   |
| Cancel any order           |         |       |   ✓   |
| Settings & 2FA             |    ✓    |   ✓   |   ✓   |

---

## Seeded Test Data

| Role    | Email                 | Password   |
| ------- | --------------------- | ---------- |
| Student | `student@example.com` | `password` |
| Staff   | `staff@example.com`   | `password` |
| Admin   | `admin@example.com`   | `password` |

> Seeded passwords are set directly via `Hash::make()` and bypass validation. When registering new accounts manually, the password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol (e.g. `Password1!`).

**Categories:** Meals, Snacks, Beverages, Desserts

**Sample Products:** Chicken Adobo (₱75), Pancit Canton (₱60), Sari-Sari (₱65), Lumpia (₱35), Burger (₱45), French Fries (₱40), Iced Coffee (₱35), Fresh Mango Juice (₱30)
