# Cart System

## Overview

The cart is session-based, managed entirely by `CartService` (`app/Services/CartService.php`). Cart data is stored in the PHP session under the key `cart` and persists across page loads until the session expires or the cart is cleared.

---

## Cart Data Structure

Each cart item is stored as an associative array keyed by `product_id`:

```php
[
    42 => [
        'product_id' => 42,
        'name'       => 'Chicken Adobo with Rice',
        'price'      => 75.00,
        'quantity'   => 2,
        'image_path' => 'products/chicken-adobo.jpg',
    ],
    // ...
]
```

---

## CartService Methods

| Method | Description |
|---|---|
| `add(productId, quantity = 1)` | Adds item to cart. Validates product is available and in stock. Throws `InvalidArgumentException` if quantity exceeds stock. |
| `update(productId, quantity)` | Updates quantity. Calls `remove()` if quantity ≤ 0. Validates against current stock. |
| `remove(productId)` | Removes item from cart. |
| `items()` | Returns the full cart array. |
| `count()` | Returns total item quantity (sum of all quantities). |
| `subtotal()` | Returns total price (sum of price × quantity for all items). |
| `clear()` | Empties the cart entirely. |
| `isEmpty()` | Returns `true` if cart has no items. |

---

## Cart Lifecycle

```
Student adds item (MenuBrowser::addToCart)
    └── CartService::add() → saves to session

Student adjusts quantity (MenuBrowser::updateCartQuantity)
    └── CartService::update() → saves to session

Student removes item (MenuBrowser::removeFromCart or Checkout::removeItem)
    └── CartService::remove() → saves to session

Student places order (Checkout::placeOrder)
    └── CartService::items() → read for order creation
    └── CartService::clear() → empties cart after PayMongo redirect

Payment fails (Checkout::placeOrder catch block)
    └── Cart is NOT cleared — items remain for retry
```

---

## Cart Display

### Desktop — Sidebar
The `MenuBrowser` component passes `cartItems`, `cartCount`, and `cartSubtotal` to its view. The desktop sidebar shows all items with remove buttons and a checkout link.

### Mobile — Sticky Bar
When `cartCount > 0`, a sticky bar appears above the bottom tab bar showing item count and subtotal. Tapping it goes to checkout.

### Checkout Page
The checkout page shows the full cart with quantity controls and remove buttons. Removing the last item redirects back to the menu.

### Cart Icon Badge
The desktop header and mobile bottom nav Cart tab both show a live badge with the item count, rendered server-side on each page load.

---

## Stock Validation

Stock is validated at two points:

1. **When adding to cart** — `CartService::add()` checks `product->stock >= requested quantity`
2. **When placing order** — `Checkout::placeOrder()` re-validates each item against current stock before creating the order

This double-check prevents race conditions where stock changes between adding to cart and checking out.
