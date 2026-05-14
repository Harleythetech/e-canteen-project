# Database Schema

## Tables

### `users`

Stores all user accounts regardless of role.

| Column | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| name | string | Full name |
| email | string | Unique |
| password | string | Hashed (bcrypt) |
| role | string(20) | `student`, `staff`, or `admin` |
| is_active | boolean | Deactivated users cannot log in |
| email_verified_at | timestamp | Nullable |
| two_factor_secret | text | Nullable — set when 2FA enabled |
| two_factor_recovery_codes | text | Nullable |
| two_factor_confirmed_at | timestamp | Nullable |
| remember_token | string | Nullable |
| created_at / updated_at | timestamp | Auto-managed |

---

### `categories`

Menu categories (Meals, Snacks, Beverages, Desserts).

| Column | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| name | string | Display name |
| slug | string | Unique URL-safe identifier |
| sort_order | integer | Controls display order |
| is_active | boolean | Inactive categories are hidden from menu |
| created_at / updated_at | timestamp | Auto-managed |

---

### `products`

Individual menu items.

| Column | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| category_id | bigint | FK → categories.id (cascade delete) |
| name | string | Display name |
| slug | string | Unique URL-safe identifier |
| description | text | Nullable |
| price | decimal(10,2) | In Philippine Peso |
| image_path | string | Nullable — relative path in `storage/app/public` |
| stock | integer | Current inventory count |
| is_available | boolean | Staff can toggle without deleting |
| sort_order | integer | Controls display order |
| created_at / updated_at | timestamp | Auto-managed |

---

### `orders`

Customer orders. Each order belongs to one user and has many items.

| Column | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| user_id | bigint | FK → users.id (cascade delete) |
| order_number | string | Unique, e.g. `ORD-ABCDE1234` |
| status | string(20) | See order lifecycle below |
| pickup_time | time | Selected by student at checkout |
| special_instructions | text | Nullable — dietary notes, requests |
| subtotal | decimal(10,2) | Sum of line items |
| total | decimal(10,2) | Same as subtotal (no fees currently) |
| payment_method | string | Nullable — `gcash`, `card`, `grab_pay`, `paymaya` |
| paymongo_checkout_id | string | Nullable — PayMongo session ID |
| paymongo_payment_id | string | Nullable — PayMongo payment ID |
| paid_at | timestamp | Nullable — set when payment confirmed |
| completed_at | timestamp | Nullable — set when order completed |
| created_at / updated_at | timestamp | Auto-managed |

**Indexes:** `status`, `(user_id, status)`, `paymongo_checkout_id`

---

### `order_items`

Line items within an order. Product name and price are snapshotted at order time so historical orders remain accurate even if the product is later edited or deleted.

| Column | Type | Notes |
|---|---|---|
| id | bigint | Primary key |
| order_id | bigint | FK → orders.id (cascade delete) |
| product_id | bigint | Nullable FK → products.id (null on delete) |
| product_name | string | Snapshot of product name at order time |
| quantity | integer | |
| unit_price | decimal(10,2) | Snapshot of price at order time |
| created_at / updated_at | timestamp | Auto-managed |

---

## Entity Relationship Diagram

```
users
  │
  └──hasMany──▶ orders
                  │
                  └──hasMany──▶ order_items
                                    │
                                    └──belongsTo──▶ products
                                                        │
                                                        └──belongsTo──▶ categories
```

---

## Order Lifecycle

Orders move through a strict state machine defined in `app/Models/Order.php`:

```
                    ┌─────────────┐
                    │   pending   │ ◀── Created at checkout
                    └──────┬──────┘
                           │ Payment confirmed
                           ▼
                    ┌─────────────┐
                    │    paid     │ ◀── PayMongo webhook or polling
                    └──────┬──────┘
                           │ Staff clicks "Start Preparing"
                           ▼
                    ┌─────────────┐
                    │  preparing  │
                    └──────┬──────┘
                           │ Staff clicks "Mark Ready"
                           ▼
                    ┌─────────────┐
                    │    ready    │ ◀── QR code shown to student
                    └──────┬──────┘
                           │ Staff scans QR code
                           ▼
                    ┌─────────────┐
                    │  completed  │
                    └─────────────┘

  pending ──▶ cancelled  (student cancels, or auto-cancel after 30 min)
  paid    ──▶ cancelled  (admin only)
```

Transitions are enforced by `Order::canTransitionTo(string $status)`. Invalid transitions are silently rejected. The `paid_at` and `completed_at` timestamps are automatically set by `Order::transitionTo()`.
