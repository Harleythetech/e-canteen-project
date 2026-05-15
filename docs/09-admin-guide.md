# Admin User Guide

Admin accounts have full access to `/admin`, `/staff`, and student routes.

---

## Overview Dashboard

The overview page (`/admin`) provides a system-wide snapshot.

### Stats Cards
- **Total Revenue** — All-time revenue from paid orders
- **Today's Revenue** — Revenue from today's paid orders
- **Total Orders** — All-time order count
- **Today's Orders** — Orders placed today
- **Active Orders** — Currently in queue (pending/paid/preparing/ready)
- **Total Students** — Registered student accounts
- **Total Products** — Products in the system
- **Low Stock** — Available products with 5 or fewer units

### 7-Day Revenue Trend
A dual-axis line chart showing daily revenue (₱) and order count for the past 7 days.

### Order Status Breakdown
A doughnut chart showing the proportion of orders by status (all time).

### Category Sales Distribution
A bar chart showing revenue by product category (all time, paid orders only).

### Recent Orders
The 10 most recent orders with customer name, order number, status, total, and timestamp.

### Low Stock Alert
Products with 5 or fewer units remaining, sorted by stock ascending. Use this to restock before items run out.

---

## User Management

The user management page (`/admin/users`) lets you create and manage all user accounts.

### Stats
Three cards showing the count of students, staff, and admins.

### Filtering and Search
- **Role filter** — All, Students, Staff, Admins
- **Search** — Real-time search by name or email

### Creating a User
1. Click **Add User**
2. Fill in: Name, Email, Role, Password
3. Check/uncheck **Active**
4. Click **Create**

Password requirements: min 8 characters, uppercase, lowercase, number, and symbol.

### Editing a User
1. Click the pencil icon on any row
2. Modify fields as needed
3. Leave password blank to keep the existing password
4. Click **Update**

> The pencil and trash icons are hidden for your own account — use the Settings pages to manage your own profile and password.

### Deleting a User
Click the trash icon on any row. A confirmation modal will appear before the deletion is carried out. You cannot delete your own account.

### Activating / Deactivating Users
Click the green/red status badge in the Status column to toggle a user's active status.

- **Deactivated users** are immediately logged out on their next request and cannot log back in
- You cannot deactivate your own account — the badge on your own row is non-interactive

---

## Sales Reports

The sales reports page (`/admin/reports`) provides revenue analytics.

### Period Selection
- **Today** — Current day
- **This Week** — Monday to Sunday
- **This Month** — First to last day of current month
- **Custom** — Pick any date range

### Summary Cards
- Total Revenue (paid orders in period)
- Total Orders (all statuses)
- Completed Orders
- Cancelled Orders
- Average Order Value

### Daily Sales Breakdown
A table showing date, revenue, and order count for each day in the selected period.

### Top Products
The 10 best-selling products in the period, showing units sold and total revenue generated.

### Order Status Breakdown
Count of orders by status for the selected period.

---

## Menu Management

Admins have the same menu management access as staff. See the [Staff Guide — Menu Management](./08-staff-guide.md#menu-management) section.

---

## Access to Staff Dashboard

Admins can also access the staff dashboard at `/staff` to manage the order queue and use the QR scanner. See the [Staff Guide](./08-staff-guide.md).
