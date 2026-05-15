# E-Canteen — System Overview

A web-based online ordering system for the **Pamantasan ng Lungsod ng San Pablo (PLSP)** school canteen. Students browse the menu, add items to a cart, pay via PayMongo (GCash, Card, GrabPay, PayMaya), and pick up orders using QR codes scanned by staff.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3+) |
| Reactive UI | Livewire 4 |
| UI Components | Flux UI 2.12 (official Livewire component library) |
| CSS | Tailwind CSS 4 |
| JavaScript | Alpine.js (ships with Flux/Livewire) |
| Charts | Chart.js 4.5 |
| QR Scanner | html5-qrcode 2.3.8 |
| QR Generator | BaconQrCode (SVG) |
| Authentication | Laravel Fortify (login, register, 2FA, password reset) |
| Payments | PayMongo REST API |
| Database | PostgreSQL |
| Build Tool | Vite 8 |
| Testing | Pest 4 |

---

## User Roles

The system has three roles, each with a completely separate interface:

| Role | Entry Point | What They Do |
|---|---|---|
| **Student** | `/menu` | Browse menu, add to cart, pay, track orders |
| **Staff** | `/staff` | Manage order queue, advance statuses, scan QR codes |
| **Admin** | `/admin` | Manage users, products, categories, view reports |

---

## Project Structure

```
app/
├── Console/Commands/       # Artisan commands (CancelStaleOrders)
├── Http/
│   ├── Controllers/        # Traditional controllers (webhooks, payment cancel)
│   └── Middleware/         # EnsureUserHasRole
├── Livewire/               # Livewire components (the "controllers" of this app)
│   ├── Admin/              # Admin-only components
│   ├── Staff/              # Staff-only components
│   └── *.php               # Student-facing components
├── Models/                 # Eloquent models
├── Policies/               # Authorization policies
├── Providers/              # AppServiceProvider, FortifyServiceProvider
└── Services/               # CartService, PayMongoService, QrCodeService

resources/views/
├── layouts/                # student.blade.php, staff.blade.php, admin.blade.php
├── livewire/               # Blade templates for each Livewire component
├── pages/auth/             # Login, register, 2FA, password reset pages
├── pages/                  # Static pages: privacy.blade.php, terms.blade.php, support.blade.php
└── components/             # Shared Blade components (toast, app-logo, etc.)

database/
├── migrations/             # All table definitions
└── seeders/                # Test data (users, categories, products)

routes/
├── web.php                 # All HTTP routes
└── console.php             # Scheduled commands
```
