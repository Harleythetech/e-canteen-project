# Payment Flow

## Overview

Payments are processed through **PayMongo**, a Philippine payment gateway. The system supports GCash, Credit/Debit Card, GrabPay, and PayMaya.

---

## Full Payment Sequence

```
Student                     App (Laravel)                    PayMongo
   │                              │                               │
   │  1. Click "Pay with PayMongo"│                               │
   ├─────────────────────────────▶│                               │
   │                              │  2. Create Order (pending)    │
   │                              │  3. Create OrderItems         │
   │                              │  4. Decrement stock           │
   │                              │  5. POST /checkout_sessions ──┼──▶
   │                              │◀─────────────────────────────┤
   │                              │  Returns checkout_url         │
   │                              │  + checkout_id                │
   │                              │  6. Save checkout_id to order │
   │                              │  7. Clear cart                │
   │◀─────────────────────────────│  8. Redirect to checkout_url  │
   │                              │                               │
   │  9. Choose payment method    │                               │
   │  10. Complete payment ───────┼───────────────────────────────▶
   │                              │                               │
   │                              │◀── 11. Webhook: payment.paid ─┤
   │                              │  Update order → paid          │
   │                              │  Set paid_at, payment_method  │
   │                              │                               │
   │◀─ 12. Redirect to success_url┤                               │
   │                              │                               │
   │  13. OrderConfirmed page     │                               │
   │  (polls PayMongo if pending) │                               │
   │                              │  14. GET /checkout_sessions/{id}
   │                              │◀─────────────────────────────┤
   │                              │  Check payments array         │
   │                              │  Update order → paid          │
   │                              │                               │
   │  15. View order status       │                               │
   │  16. Show QR code ◀──────────│  Generate SVG QR              │
```

---

## Why Two Confirmation Methods?

PayMongo webhooks are reliable in production but **cannot reach localhost** during development. The app uses a dual approach:

### Method 1 — Webhook (Production)
- PayMongo sends a `POST` to `/webhooks/paymongo` when payment succeeds
- The signature is verified using `PAYMONGO_WEBHOOK_SECRET`
- `PayMongoWebhookController` calls `handlePaymentPaid()` which updates the order

### Method 2 — Polling (Fallback / Local Dev)
- When the student lands on `OrderConfirmed` or `OrderStatus`, the app calls `PayMongoService::confirmCheckoutSession()`
- This fetches the checkout session from PayMongo's API and checks the `payments` array
- If a payment with `status: paid` is found, the order is updated immediately
- This is idempotent — safe to call multiple times

---

## Payment Status Detection

PayMongo's API response for a paid checkout session (confirmed via test logs):

```json
{
  "data": {
    "attributes": {
      "status": "active",
      "payment_status": null,
      "payments": [
        {
          "id": "pay_xxxx",
          "attributes": {
            "status": "paid"
          }
        }
      ],
      "payment_method_used": "gcash"
    }
  }
}
```

The app checks three conditions (any one is sufficient):
1. `status === 'completed'`
2. `payment_status === 'paid'`
3. `payments[0].attributes.status === 'paid'` ← the reliable one in practice

---

## Cancelled / Failed Payments

PayMongo's behavior when payment fails varies by method:

| Method | Behavior on Fail/Expire |
|---|---|
| GCash | Stays on PayMongo's page, no redirect |
| Maya | Stays on PayMongo's page, no redirect |
| GrabPay | Redirects back to payment method selection |

Since PayMongo doesn't reliably redirect to `cancel_url`, the app handles abandoned orders in three ways:

### 1. Cancel URL Handler (`PaymentCancelController`)
If PayMongo does redirect to `cancel_url` (`/orders/{order}/payment-cancelled`):
- Does a final poll to confirm the order isn't actually paid
- If still pending: cancels order, restores stock, redirects to checkout with a warning message

### 2. Checkout Mount Detection
When the student navigates back to `/checkout`:
- `Checkout::mount()` finds any of their pending orders with a checkout ID older than 5 minutes
- Cancels them and restores stock automatically

### 3. Scheduled Auto-Cancel
`CancelStaleOrders` command runs every 5 minutes:
- Finds all `pending` orders older than 30 minutes
- Calls `cancelAndRestoreStock()` on each
- Catches anything that slipped through (browser closed, never returned, etc.)

---

## Stock Management

Stock is managed carefully to prevent overselling:

| Event | Stock Change |
|---|---|
| Order placed (checkout) | Decremented immediately |
| Order cancelled (any method) | Restored via `cancelAndRestoreStock()` |
| Payment fails / rollback | Restored in `Checkout::placeOrder()` catch block |

The `cancelAndRestoreStock()` method on the `Order` model is the single source of truth for cancellation — used by the cancel URL handler, checkout mount, scheduled command, and manual student cancellation.

---

## Test Mode

PayMongo provides a test environment with simulated payment pages:

- **GCash / Maya**: Shows "Authorize Test Payment" and "Expire/Fail Test Payment" buttons
- **GrabPay**: Shows "Authorize" and "Cancel" buttons
- **Card**: Use test card numbers from PayMongo docs

Test API keys start with `sk_test_` and `pk_test_`.
