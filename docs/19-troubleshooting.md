# Troubleshooting

## Payment Issues

### Order stays "pending" after payment

**Cause:** The PayMongo webhook didn't fire (common in local dev since `localhost` is not publicly accessible).

**Fix:** The app automatically polls PayMongo when the student lands on the confirmation page or order status page. If the order is still pending after visiting those pages, check `storage/logs/laravel.log` for `PayMongo: checkout session polled` entries.

Look for the `status` and `payments_count` values in the log. If `payments_count` is 0, the payment hasn't been processed by PayMongo yet.

---

### "Payment processing failed. Please try again."

**Cause:** The PayMongo API call to create a checkout session failed.

**Fix:** Check `storage/logs/laravel.log` for `PayMongo checkout session creation failed`. Common causes:
- Invalid or missing `PAYMONGO_SECRET_KEY` in `.env`
- PayMongo API is down
- Network timeout (30-second limit)

---

### Webhook signature verification fails

**Cause:** `PAYMONGO_WEBHOOK_SECRET` doesn't match the secret in your PayMongo dashboard.

**Fix:** Go to PayMongo Dashboard → Developers → Webhooks, copy the webhook secret, and update `PAYMONGO_WEBHOOK_SECRET` in `.env`.

---

## Cart Issues

### Cart is empty after returning from PayMongo

**Expected behavior.** The cart is cleared in `Checkout::placeOrder()` after the PayMongo session is created and before the redirect. If payment fails, the order is rolled back but the cart is not cleared — items remain for retry.

---

### "Only X items available" error when adding to cart

**Cause:** The requested quantity exceeds current stock.

**Fix:** This is correct behavior. The student needs to reduce their quantity.

---

## Order Issues

### Order was auto-cancelled unexpectedly

**Cause:** The `CancelStaleOrders` command cancelled a pending order older than 30 minutes.

**Fix:** This is expected behavior for unpaid orders. If a student paid but the order was cancelled before the payment was confirmed, check the PayMongo logs — the payment may have been processed after the cancellation window.

---

### "This order cannot be cancelled"

**Cause:** The order is no longer in `pending` status (it's been paid, is being prepared, etc.).

**Fix:** Only pending orders can be cancelled by students. Admins can cancel paid orders from the admin panel.

---

## QR Scanner Issues

### Camera doesn't start / "Camera not available"

**Cause:** The app is not served over HTTPS. Browser camera APIs require a secure context.

**Fix:** In production, ensure HTTPS is configured. For local development, use `localhost` (which is treated as secure) rather than an IP address.

---

### "Camera permission denied"

**Cause:** The browser blocked camera access.

**Fix:** Click the camera/lock icon in the browser address bar and allow camera access for the site. On mobile, check app permissions in device settings.

---

## UI Issues

### Toast notifications appear on the left side

**Cause:** The toast container uses `right: 1rem` via inline style. If you see toasts on the left, the inline style may have been accidentally removed.

**Fix:** Ensure the toast component's container div has `style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; ..."`.

---

### Mobile content hidden behind bottom nav bar

**Cause:** The bottom nav bar is fixed and overlaps page content.

**Fix:** The student layout's `<main>` has `style="padding-bottom: max(8rem, calc(4rem + env(safe-area-inset-bottom)));"` which should clear the nav. If content is still hidden, check that this style hasn't been removed from `layouts/student.blade.php`.

---

### Dark mode colors look wrong in modals

**Cause:** Using `bg-zinc-50` or `bg-white` inside a dark modal creates invisible text (white text on white background).

**Fix:** Use `border border-zinc-200 dark:border-zinc-700` with transparent backgrounds instead of solid light backgrounds inside modals.

---

## Staff Dashboard Issues

### Order detail modal switches to a different order after a few seconds

**Cause (historical):** This was caused by `wire:poll` triggering a full Livewire re-render that morphed the modal content.

**Current fix:** The modal is built with pure Alpine.js and stores order data as a JavaScript object. Livewire re-renders cannot affect it. The orders list refreshes via `setInterval(() => $wire.refreshOrders(), 5000)` which calls a no-op method that triggers a re-render without resetting Alpine state.

---

## Database Issues

### Migrations fail with "column already exists"

**Cause:** Running migrations on a partially migrated database.

**Fix:**
```bash
php artisan migrate:fresh --seed
```
> Warning: This destroys all data.

---

### `ilike` operator not supported

**Cause:** `ilike` is PostgreSQL-specific (case-insensitive LIKE). If you're using SQLite or MySQL, this will fail.

**Fix:** The app is designed for PostgreSQL. For SQLite (local dev), replace `ilike` with `like` in `Admin\UserManagement` and `MenuBrowser`.

---

## Logs

All important events are logged to `storage/logs/laravel.log`:

| Log Entry | Meaning |
|---|---|
| `PayMongo: checkout session polled` | App polled PayMongo to check payment status |
| `PayMongo: payment confirmed via polling` | Order marked paid via polling |
| `PayMongo webhook received` | Webhook arrived and was processed |
| `PayMongo webhook: invalid signature` | Webhook signature mismatch |
| `Order cancelled and stock restored` | Order cancelled via any method |
| `Stale order auto-cancelled` | Scheduler cancelled an old order |
| `OrderConfirmed: could not poll PayMongo` | Polling failed (non-fatal) |
