# PLSP E-Canteen — Documentation Index

A web-based online ordering system for the Pamantasan ng Lungsod ng San Pablo (PLSP) school canteen.

**Repository:** https://github.com/Harleythetech/e-canteen-project

---

## Table of Contents

| # | Document | Description |
|---|---|---|
| 01 | [Overview](./01-overview.md) | What the system does, tech stack, user roles, project structure |
| 02 | [Setup & Installation](./02-setup.md) | How to install, configure, and run the app from scratch |
| 03 | [Database Schema](./03-database.md) | All tables, columns, relationships, and the order state machine |
| 04 | [Architecture](./04-architecture.md) | Models, Livewire components, services, middleware, policies, controllers, providers |
| 05 | [Payment Flow](./05-payment-flow.md) | Full PayMongo integration — checkout sessions, webhooks, polling, cancellations |
| 06 | [Routes Reference](./06-routes.md) | All HTTP routes with methods, URIs, handlers, and names |
| 07 | [Student Guide](./07-student-guide.md) | How to register, browse, order, pay, and track orders |
| 08 | [Staff Guide](./08-staff-guide.md) | How to manage the order queue, use the QR scanner, and manage the menu |
| 09 | [Admin Guide](./09-admin-guide.md) | How to use the dashboard, manage users, and view sales reports |
| 10 | [Commands & Scheduling](./10-commands-and-scheduling.md) | Artisan commands, the scheduler, and queue setup |
| 11 | [Toast Notifications](./11-toast-notifications.md) | How the notification system works and where toasts are dispatched |
| 12 | [Layouts & UI](./12-layouts-and-ui.md) | Layout structure, Flux UI components, Alpine.js usage, Chart.js charts |
| 13 | [Authentication](./13-authentication.md) | Fortify setup, 2FA, role-based redirects, deactivated accounts |
| 14 | [Cart System](./14-cart-system.md) | Session-based cart, CartService methods, stock validation |
| 15 | [QR Code System](./15-qr-code-system.md) | QR generation, camera scanner, manual lookup, security |
| 16 | [Order Cancellation](./16-order-cancellation.md) | All cancellation paths and stock restoration logic |
| 17 | [Seeded Test Data](./17-seeded-data.md) | Test accounts, categories, and products |
| 18 | [Environment Variables](./18-environment-variables.md) | All `.env` keys explained with a production checklist |
| 19 | [Troubleshooting](./19-troubleshooting.md) | Common issues and how to fix them |

---

## Quick Reference

### Test Accounts
| Role | Email | Password |
|---|---|---|
| Student | student@example.com | password |
| Staff | staff@example.com | password |
| Admin | admin@example.com | password |

### Key URLs
| URL | Purpose |
|---|---|
| `/menu` | Student menu browser |
| `/checkout` | Student checkout |
| `/orders` | Student order history |
| `/staff` | Staff order dashboard |
| `/staff/menu` | Staff menu management |
| `/admin` | Admin overview |
| `/admin/users` | Admin user management |
| `/admin/reports` | Admin sales reports |
| `/webhooks/paymongo` | PayMongo webhook endpoint |

### Order Status Flow
```
pending → paid → preparing → ready → completed
pending → cancelled
paid    → cancelled (admin only)
```

### One-Command Setup
```bash
composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed && npm run build && php artisan storage:link
```
