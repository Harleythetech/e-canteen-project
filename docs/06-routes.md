# Routes Reference

All routes are defined in `routes/web.php`.

---

## Public Routes

| Method | URI | View | Name |
|---|---|---|---|
| GET | `/` | `welcome` | `home` |
| GET | `/privacy` | `pages.privacy` | `privacy` |
| GET | `/terms` | `pages.terms` | `terms` |
| GET | `/support` | `pages.support` | `support` |

---

## Student Routes

Require: `auth` + `verified` middleware.

After login, `/dashboard` redirects based on role:
- `admin` → `/admin`
- `staff` → `/staff`
- `student` → `/menu`

| Method | URI | Component | Name |
|---|---|---|---|
| GET | `/dashboard` | Role redirect | `dashboard` |
| GET | `/menu` | `MenuBrowser` | `menu` |
| GET | `/checkout` | `Checkout` | `checkout` |
| GET | `/orders` | `OrderHistory` | `orders.index` |
| GET | `/orders/{order}` | `OrderStatus` | `orders.show` |
| GET | `/orders/{order}/confirmed` | `OrderConfirmed` | `orders.confirmed` |
| GET | `/orders/{order}/payment-cancelled` | `PaymentCancelController` | `orders.payment-cancelled` |

---

## Staff Routes

Prefix: `/staff` — Require: `auth` + `role:staff,admin` middleware.

| Method | URI | Component | Name |
|---|---|---|---|
| GET | `/staff` | `Staff\Dashboard` | `staff.dashboard` |
| GET | `/staff/menu` | `Staff\MenuManagement` | `staff.menu` |

> Note: The staff login page at `/staff/login` is excluded from auth middleware — it's just a view that points to the standard Fortify login.

---

## Admin Routes

Prefix: `/admin` — Require: `auth` + `role:admin` middleware.

| Method | URI | Component | Name |
|---|---|---|---|
| GET | `/admin` | `Admin\Overview` | `admin.dashboard` |
| GET | `/admin/users` | `Admin\UserManagement` | `admin.users` |
| GET | `/admin/reports` | `Admin\SalesReports` | `admin.reports` |

---

## Webhook Route

Excluded from `web` middleware group (no CSRF, no session, no auth).

| Method | URI | Controller | Name |
|---|---|---|---|
| POST | `/webhooks/paymongo` | `PayMongoWebhookController` | `webhooks.paymongo` |

---

## Settings Routes

Defined in `routes/settings.php` (included at the bottom of `web.php`). Handles profile editing, password changes, appearance settings, and 2FA management for all authenticated users.

---

## Auth Routes

Managed by Laravel Fortify. Key routes:

| Method | URI | Purpose |
|---|---|---|
| GET/POST | `/login` | Login form |
| POST | `/logout` | Logout |
| GET/POST | `/register` | Registration |
| GET/POST | `/forgot-password` | Password reset request |
| GET/POST | `/reset-password/{token}` | Password reset |
| GET/POST | `/two-factor-challenge` | 2FA verification |
| GET/POST | `/user/two-factor-authentication` | Enable/disable 2FA |
