# Layouts & UI

## UI Framework

The app uses **Flux UI** — the official Livewire component library. Flux provides pre-built components like `<flux:button>`, `<flux:input>`, `<flux:modal>`, `<flux:badge>`, `<flux:sidebar>`, etc. All Flux components support dark mode automatically.

**Dark mode** is enabled by default (`class="dark"` on the `<html>` tag). Users can toggle it via the Appearance settings page.

---

## Student Layout (`layouts/student.blade.php`)

Used by: `MenuBrowser`, `Checkout`, `OrderHistory`, `OrderStatus`, `OrderConfirmed`

### Desktop (lg and above)
- **Top header** — App logo, Menu and My Orders nav links, cart icon with badge, user menu
- **Main content** — Full-width, max 7xl, with padding

### Mobile (below lg)
- **Sticky top bar** — App logo and user menu
- **Fixed bottom tab bar** — 4 tabs: Menu, Orders, Cart, Settings
- **Main content** — Extra bottom padding to clear the tab bar

### Mobile Bottom Tab Bar
The tab bar is built with inline styles (not Tailwind) so Alpine.js can dynamically apply dark/light mode colors. It uses `env(safe-area-inset-bottom)` padding for iPhone home indicator support.

Active tab is highlighted in orange (`rgb(249,115,22)`). The Cart tab shows a badge with item count.

### Safe Area Support
The viewport meta tag includes `viewport-fit=cover` to enable `env(safe-area-inset-bottom)` on notched iPhones. Both the tab bar and the main content padding account for this.

---

## Staff Layout (`layouts/staff.blade.php`)

Used by: `Staff\Dashboard`, `Staff\MenuManagement`

### Desktop
- **Sticky collapsible sidebar** — App logo, navigation groups (Orders, Management), user menu at bottom
- Navigation items: Dashboard, Product Statistics, Menu Management

### Mobile
- **Collapsible sidebar** — Toggled by hamburger button in the mobile header
- **Mobile header** — Hamburger toggle, user profile dropdown with logout

---

## Admin Layout (`layouts/admin.blade.php`)

Used by: `Admin\Overview`, `Admin\UserManagement`, `Admin\SalesReports`

### Desktop
- **Sticky collapsible sidebar** — App logo, navigation groups (Dashboard, Management, Analytics), user menu at bottom
- Navigation items: Overview, User Management, Sales Reports

### Mobile
- Same pattern as staff layout

---

## Shared Head Partial (`partials/head.blade.php`)

Included in all layouts. Contains:
- `charset` and `viewport` meta tags (with `viewport-fit=cover`)
- Dynamic `<title>` tag
- Favicon links (`.ico`, `.svg`, Apple touch icon)
- Google Fonts (Instrument Sans 400/500/600)
- Vite asset injection (`app.css`, `app.js`)
- `@fluxAppearance` — Flux dark mode initialization

---

## Toast Component (`components/toast.blade.php`)

Included in all three layouts just before `@fluxScripts`. See [Toast Notifications](./11-toast-notifications.md) for full details.

---

## Flux UI Components Used

| Component | Used For |
|---|---|
| `flux:button` | All buttons with variants (primary, ghost, filled, danger) |
| `flux:input` | Text inputs, search fields, file uploads |
| `flux:textarea` | Special instructions field |
| `flux:select` | Category dropdowns, role selectors, pickup time |
| `flux:checkbox` | Availability toggles, active flags |
| `flux:modal` | Product/category/user edit forms |
| `flux:badge` | Status indicators, role labels |
| `flux:heading` / `flux:subheading` | Page and section titles |
| `flux:callout` | Warning/error banners (payment cancelled, etc.) |
| `flux:sidebar` | Staff and admin navigation |
| `flux:header` | Desktop and mobile headers |
| `flux:navbar` | Desktop navigation links |
| `flux:icon.*` | All icons throughout the app |
| `flux:avatar` | User initials in menus |
| `flux:dropdown` | User profile menus |
| `flux:separator` | Visual dividers |
| `flux:spacer` | Pushes sidebar content to bottom |

---

## Alpine.js Usage

Alpine.js ships with Flux/Livewire and is used for client-side interactivity that doesn't need server communication:

| Feature | Where | What It Does |
|---|---|---|
| Toast system | `components/toast.blade.php` | Manages toast array, transitions, auto-dismiss |
| Order detail modal | `staff/dashboard.blade.php` | Stores order data as JS object, shows/hides modal |
| QR scanner | `staff/dashboard.blade.php` | Controls camera, handles scan results |
| Dark mode nav | `layouts/student.blade.php` | Applies correct colors to bottom tab bar |
| Order refresh | `staff/dashboard.blade.php` | `setInterval` calling `$wire.refreshOrders()` |

---

## Chart.js Usage

Charts are rendered using Chart.js 4.5 with Alpine.js `x-data` for initialization.

| Chart | Location | Type | Data |
|---|---|---|---|
| Hourly Sales | Staff Dashboard (stats tab) | Line (dual axis) | Revenue + order count by hour |
| Stock Health | Staff Dashboard (stats tab) | Doughnut | Critical/Low/Healthy product counts |
| Top Products | Staff Dashboard (stats tab) | Horizontal Bar | Units sold per product today |
| 7-Day Revenue Trend | Admin Overview | Line (dual axis) | Daily revenue + orders |
| Order Status Breakdown | Admin Overview | Doughnut | Orders by status |
| Category Sales | Admin Overview | Bar | Revenue by category |
| Daily Sales | Admin Sales Reports | Line | Revenue per day in period |
| Top Products | Admin Sales Reports | Bar | Units sold in period |

All charts respect dark mode by checking `document.documentElement.classList.contains('dark')` on initialization.
