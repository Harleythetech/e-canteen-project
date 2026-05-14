# Seeded Test Data

Run `php artisan db:seed` to populate the database with the following test data.

---

## Test Accounts

| Role | Name | Email | Password |
|---|---|---|---|
| Student | Test Student | student@example.com | password |
| Staff | Canteen Staff | staff@example.com | password |
| Admin | Admin User | admin@example.com | password |

All accounts are active and email-verified.

---

## Categories

| Name | Slug | Sort Order |
|---|---|---|
| Meals | meals | 1 |
| Snacks | snacks | 2 |
| Beverages | beverages | 3 |
| Desserts | desserts | 4 |

---

## Products

| Name | Category | Price | Stock |
|---|---|---|---|
| Chicken Adobo with Rice | Meals | ₱75.00 | 50 |
| Pancit Canton | Meals | ₱60.00 | 50 |
| Sari-Sari (Shanghai Blend) | Meals | ₱65.00 | 30 |
| Lumpia Shanghai (5pcs) | Snacks | ₱35.00 | 80 |
| Burger | Snacks | ₱45.00 | 40 |
| French Fries | Snacks | ₱40.00 | 60 |
| Iced Coffee | Beverages | ₱35.00 | 100 |
| Fresh Mango Juice | Beverages | ₱30.00 | 60 |

All products are available and in stock. No products are seeded for the Desserts category.

---

## Re-seeding

To reset the database and re-seed from scratch:

```bash
php artisan migrate:fresh --seed
```

> **Warning:** This destroys all existing data including orders, users, and products.
