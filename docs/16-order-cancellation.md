# Order Cancellation & Stock Restoration

## Overview

Orders can only be cancelled when they are in `pending` status. Cancellation always restores stock for every item in the order. The logic lives in one place — `Order::cancelAndRestoreStock()` — used by all cancellation paths.

---

## `Order::cancelAndRestoreStock()`

```php
public function cancelAndRestoreStock(): bool
{
    if ($this->status !== 'pending') {
        return false;
    }

    $this->update(['status' => 'cancelled']);

    foreach ($this->items as $item) {
        if ($item->product_id) {
            Product::where('id', $item->product_id)
                ->increment('stock', $item->quantity);
        }
    }

    Log::info('Order cancelled and stock restored', [
        'order_number' => $this->order_number,
    ]);

    return true;
}
```

- Returns `false` if the order is not `pending` (idempotent — safe to call multiple times)
- Only restores stock for items where `product_id` is not null (products that still exist)
- Logs every cancellation

---

## Cancellation Paths

### 1. Student Cancels Manually

On the `OrderStatus` page, a **Cancel Order** button appears when:
- Order status is `pending`
- The authenticated user is the order owner

`OrderStatus::cancelOrder()` calls `cancelAndRestoreStock()` and dispatches an info toast.

### 2. Payment Cancel URL

When PayMongo redirects to the `cancel_url` (`/orders/{order}/payment-cancelled`), `PaymentCancelController` runs:

1. Does a final PayMongo poll — confirms the order isn't actually paid
2. If still pending: calls `cancelAndRestoreStock()`, redirects to `/checkout` with a warning callout
3. If already paid: redirects to the order status page

### 3. Checkout Mount Detection

When the student navigates back to `/checkout` (e.g., after closing the PayMongo tab):

`Checkout::mount()` finds all of the user's pending orders that:
- Have a `paymongo_checkout_id` (were sent to PayMongo)
- Are older than 5 minutes (grace period to avoid cancelling an order they're still paying)

Each is cancelled and stock restored automatically before the checkout page loads.

### 4. Scheduled Auto-Cancel

`CancelStaleOrders` runs every 5 minutes via the scheduler. It finds all `pending` orders older than 30 minutes and calls `cancelAndRestoreStock()` on each.

This is the safety net that catches everything else — browser closed, user never returned, GrabPay/GCash never redirected back.

---

## Cancellation Flow Summary

```
Order placed (pending)
    │
    ├── Student pays → paid (no cancellation)
    │
    ├── Student cancels manually (OrderStatus page)
    │       └── cancelAndRestoreStock()
    │
    ├── PayMongo redirects to cancel_url
    │       └── PaymentCancelController → cancelAndRestoreStock()
    │
    ├── Student navigates back to /checkout (>5 min later)
    │       └── Checkout::mount() → cancelAndRestoreStock()
    │
    └── Order is >30 min old (scheduler)
            └── CancelStaleOrders → cancelAndRestoreStock()
```

---

## What Happens to Stock

Stock is decremented when the order is placed (in `Checkout::placeOrder()`):

```php
Product::where('id', $productId)->decrement('stock', $item['quantity']);
```

Stock is restored when the order is cancelled:

```php
Product::where('id', $item->product_id)->increment('stock', $item->quantity);
```

If `Checkout::placeOrder()` throws an exception during PayMongo session creation, the rollback restores stock immediately before the order is deleted:

```php
foreach ($cart->items() as $productId => $item) {
    Product::where('id', $productId)->increment('stock', $item['quantity']);
}
$order->items()->delete();
$order->delete();
```
