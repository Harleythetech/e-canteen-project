# Staff User Guide

Staff accounts have access to `/staff` and `/staff/menu`. They can also access student routes if needed.

---

## Dashboard — Live Orders

The dashboard (`/staff`) has two tabs: **Dashboard** (live orders) and **Product Statistics**.

### Order Queue

The live orders panel shows all active orders (pending, paid, preparing, ready) by default. Use the filter buttons to narrow by status.

Each order card shows:
- Order number and status badge
- Customer name and items ordered
- Pickup time and total
- Special instructions preview (amber text with chat icon)
- Action button to advance the order
- **Details** button to open the full order modal

The list refreshes every 5 seconds automatically.

### Advancing Orders

Click the action button on each card to move the order to the next status:

| Current Status | Button | Next Status |
|---|---|---|
| Pending | Mark Paid | Paid |
| Paid | Start Preparing | Preparing |
| Preparing | Mark Ready | Ready |
| Ready | Complete | Completed |

A toast notification confirms each status change.

### Order Detail Modal

Click **Details** on any order card to open a modal showing:
- Full order number, date, and status
- Customer name and email
- Pickup time and payment method
- **Special instructions** (highlighted in amber if present)
- Complete itemized list with quantities and subtotals
- Total amount
- Action button (same as on the card)

The modal is built with Alpine.js and is immune to the 5-second refresh — it won't switch to a different order while you're reading it.

---

## QR Scanner

The QR scanner panel is on the right side of the dashboard.

### Camera Scanner
1. Click **Tap to scan** to activate the camera
2. Point the camera at the student's QR code
3. The system automatically detects the code and processes it
4. A success/error message appears for 4 seconds

### Manual Lookup
If the camera isn't available, type the order number in the text field and click **Look up**.

### Scanner Results
- **Green** — Order completed successfully
- **Red** — Order not found, or not ready for pickup
- **Yellow** — Order was already completed

Only orders with `ready` status can be completed via QR scan.

---

## Product Statistics Tab

Click **Product Statistics** in the sidebar (or the tab on the dashboard) to view today's analytics:

### Hourly Sales Chart
A line chart showing revenue (₱) and order count by hour from 7 AM to 5 PM.

### Stock Health Doughnut
Shows the distribution of products by stock level:
- **Critical (0–5 units)** — Red
- **Low (6–15 units)** — Amber
- **Healthy (16+ units)** — Green

### Top Products Bar Chart
Horizontal bar chart of the top 10 products by units sold today.

### Top Products Table
Detailed table showing rank, product name, units sold today, and current stock remaining.

---

## Menu Management

The menu management page (`/staff/menu`) has two tabs: **Products** and **Categories**.

### Products Tab

The products table shows all products with their category, price, stock, and availability status.

**Adding a Product:**
1. Click **Add Product**
2. Fill in: Name, Category, Description (optional), Price (₱), Stock quantity
3. Upload an image (optional, max 2MB, image files only)
4. Check/uncheck **Available for ordering**
5. Click **Create**

**Editing a Product:**
1. Click the pencil icon on any row
2. Modify fields as needed
3. Upload a new image to replace the existing one (leave blank to keep current)
4. Click **Update**

**Toggling Availability:**
Click the green/red badge in the Status column to instantly toggle a product's availability without editing the full form. Unavailable products are hidden from the student menu.

**Deleting a Product:**
Click the trash icon and confirm in the modal that appears. This permanently deletes the product.

### Categories Tab

**Adding a Category:**
1. Click **Add Category**
2. Enter a name
3. Check/uncheck **Active**
4. Click **Create**

**Editing a Category:**
Click the pencil icon to rename or toggle active status.

**Deleting a Category:**
Click the trash icon and confirm in the modal that appears. Only possible if the category has no products. The system will show an error if you try to delete a category with products.

---

## Notifications

All actions show toast notifications in the top-right corner:
- **Green** — Success (order advanced, product saved, etc.)
- **Red** — Error (cannot advance, cannot delete, etc.)
- **Blue** — Info
- **Amber** — Warning

Toasts auto-dismiss after 4 seconds. Click the × to dismiss early.
