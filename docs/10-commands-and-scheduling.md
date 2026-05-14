# Commands & Scheduling

## Artisan Commands

### `orders:cancel-stale`

Cancels pending orders that have not been paid within the timeout window and restores their stock.

```bash
# Cancel orders pending for more than 30 minutes (default)
php artisan orders:cancel-stale

# Custom timeout
php artisan orders:cancel-stale --minutes=15
```

**What it does:**
1. Finds all orders with `status = pending` and `created_at <= now - minutes`
2. Calls `cancelAndRestoreStock()` on each
3. Outputs a line for each cancelled order
4. Reports total count

**Example output:**
```
  Cancelled: ORD-ABCDE1234
  Cancelled: ORD-FGHIJ5678
Cancelled 2 stale order(s).
```

---

## Scheduler

The scheduler is configured in `routes/console.php`:

```php
Schedule::command(CancelStaleOrders::class)->everyFiveMinutes();
```

This runs `orders:cancel-stale` every 5 minutes automatically.

### Activating the Scheduler

Add this single cron entry to your server (runs every minute, Laravel handles the rest):

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Testing the Scheduler Locally

```bash
# Run the scheduler once (executes any due commands)
php artisan schedule:run

# Run a specific command directly
php artisan orders:cancel-stale

# See what's scheduled
php artisan schedule:list
```

---

## Queue

The app uses a database queue driver. The queue is used for email sending (PayMongo sends email receipts via their own system, but Laravel queues are available for future use).

Start the queue worker:

```bash
php artisan queue:listen --tries=1
```

Or with the all-in-one dev command:

```bash
composer run dev
```

---

## Other Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Re-run migrations from scratch (destroys all data)
php artisan migrate:fresh --seed

# Create a storage symlink (needed for product images)
php artisan storage:link

# Run tests
php artisan test
# or
composer run test
```
