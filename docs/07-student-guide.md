# Student User Guide

## Registering an Account

1. Go to `/register`
2. Enter your name, email, and a password (min 8 chars, must include uppercase, lowercase, number, and symbol)
3. Verify your email address
4. You'll be redirected to the menu

---

## Browsing the Menu

The menu page (`/menu`) shows all available, in-stock items.

- **Category tabs** — Filter by Meals, Snacks, Beverages, Desserts, or view All
- **Search bar** — Type to filter products by name in real time
- **Product cards** — Show name, category, price, and a low-stock badge if fewer than 5 remain
- **Add to Cart** — Click the button on any product card
- **Quantity controls** — Once in cart, use +/- buttons directly on the card
- **Cart sidebar** (desktop) — Shows all items, subtotal, and a checkout button
- **Cart bar** (mobile) — Fixed bar above the bottom nav showing item count and subtotal

---

## Checkout

1. Click the cart icon or "Proceed to Checkout"
2. Review your items — adjust quantities or remove items
3. Add **Special Instructions** if needed (allergies, requests)
4. Select a **Pickup Time** — 15-minute slots from 7 AM to 5 PM, at least 15 minutes from now
5. Click **Pay with PayMongo**
6. You'll be redirected to PayMongo's secure payment page

### Payment Methods
- **GCash** — Enter your GCash number
- **Credit/Debit Card** — Visa, Mastercard
- **GrabPay** — GrabPay wallet
- **PayMaya** — Maya wallet

---

## After Payment

After completing payment, you'll land on the **Order Confirmed** page showing:
- Your order number (e.g., `ORD-ABCDE1234`)
- Current status
- Total amount
- Pickup time
- Number of items

Click **Track My Order** to go to the order status page.

---

## Tracking Your Order

The order status page (`/orders/{order}`) shows:

### Status Tracker
A visual progress bar showing: Pending → Paid → Preparing → Ready → Completed

### QR Code
Once your payment is confirmed (`paid` status), a QR code appears. **Show this QR code at the counter** when picking up your order. The staff will scan it to mark your order as completed.

### Order Details
- All items with quantities and prices
- Total amount
- Pickup time
- Payment method
- Timestamps (paid at, completed at)

### Cancel Order
If your order is still `pending` (not yet paid), you can cancel it. Stock will be restored.

The page auto-refreshes every 5 seconds to show status updates.

---

## My Orders

The orders page (`/orders`) shows all your orders with three filter tabs:
- **Active** — pending, paid, preparing, ready
- **Completed** — picked up orders
- **Cancelled** — cancelled orders

Click any order to view its full details.

---

## Settings

Access via the Settings tab in the bottom nav (mobile) or the user menu (desktop).

- **Profile** — Update your name and email
- **Password** — Change your password
- **Appearance** — Toggle light/dark mode
- **Two-Factor Authentication** — Enable TOTP-based 2FA for extra security

---

## Mobile Navigation

On mobile, a fixed bottom tab bar provides quick access to:
- **Menu** — Browse products
- **Orders** — View your order history
- **Cart** — Go to checkout
- **Settings** — Profile and preferences
