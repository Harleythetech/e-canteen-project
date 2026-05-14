# Toast Notification System

## Overview

Every user action that succeeds or fails shows a toast notification in the top-right corner of the screen. Toasts auto-dismiss after 4 seconds and can be manually dismissed by clicking the × button.

---

## How It Works

The toast system is built with Alpine.js and lives in `resources/views/components/toast.blade.php`. It is included in all three layouts (student, staff, admin) just before `@fluxScripts`.

### The Component

The component listens for a browser-level `toast` event dispatched by any Livewire component:

```php
// In any Livewire component:
$this->dispatch('toast', type: 'success', message: 'Product saved!');
```

Alpine catches this event and adds the toast to a reactive array. Each toast has:
- A unique ID (timestamp)
- A type (`success`, `error`, `warning`, `info`)
- A message string
- A `visible` flag for transition control

### Toast Types

| Type | Color | Icon | Use Case |
|---|---|---|---|
| `success` | Green | ✓ circle | Action completed successfully |
| `error` | Red | ✗ circle | Action failed or validation error |
| `warning` | Amber | ⚠ triangle | Caution or non-fatal issue |
| `info` | Blue | ℹ circle | Neutral information |

---

## Where Toasts Are Dispatched

### Student — `MenuBrowser`
| Action | Type | Message |
|---|---|---|
| Add to cart | success | `"{name} added to cart."` |
| Remove from cart | info | `"{name} removed from cart."` |

### Student — `Checkout`
| Action | Type | Message |
|---|---|---|
| Remove item | info | `"Item removed from cart."` |
| Cart empty on submit | error | `"Your cart is empty."` |
| Item out of stock | error | `"{name} is no longer available in the requested quantity."` |
| Payment session failed | error | `"Payment processing failed. Please try again."` |

### Student — `OrderStatus`
| Action | Type | Message |
|---|---|---|
| Cancel order (success) | info | `"Your order has been cancelled."` |
| Cancel order (failed) | error | `"This order cannot be cancelled."` |

### Staff — `Staff\Dashboard`
| Action | Type | Message |
|---|---|---|
| Advance order (success) | success | `"Order {number} marked as {status}."` |
| Advance order (failed) | error | `"Could not advance order status."` |

### Staff — `Staff\MenuManagement`
| Action | Type | Message |
|---|---|---|
| Save product (create) | success | `"Product created successfully!"` |
| Save product (update) | success | `"Product updated successfully!"` |
| Toggle availability | success | `"{name} marked as available/unavailable."` |
| Delete product | success | `"{name} deleted."` |
| Save category (create) | success | `"Category created successfully!"` |
| Save category (update) | success | `"Category updated successfully!"` |
| Delete category (success) | success | `"{name} deleted."` |
| Delete category (has products) | error | `"Cannot delete a category that has products."` |

### Admin — `Admin\UserManagement`
| Action | Type | Message |
|---|---|---|
| Create user | success | `"User created successfully!"` |
| Update user | success | `"User updated successfully!"` |
| Toggle active (success) | success | `"{name} has been activated/deactivated."` |
| Toggle own account | error | `"You cannot deactivate your own account."` |

---

## Adding Toasts to New Components

To dispatch a toast from any Livewire component:

```php
// Success
$this->dispatch('toast', type: 'success', message: 'Done!');

// Error
$this->dispatch('toast', type: 'error', message: 'Something went wrong.');

// Warning
$this->dispatch('toast', type: 'warning', message: 'Low stock remaining.');

// Info
$this->dispatch('toast', type: 'info', message: 'Item removed.');
```

The toast component is already included in all layouts — no additional setup needed.

---

## Positioning

The toast container is fixed to the **top-right** of the viewport using inline styles (not Tailwind classes, to avoid purging issues):

```html
style="position: fixed; top: 1rem; right: 1rem; z-index: 9999; ..."
```

`z-index: 9999` ensures toasts always appear above modals, sidebars, and other overlays.
