<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'pickup_time',
        'special_instructions',
        'subtotal',
        'total',
        'payment_method',
        'paymongo_checkout_id',
        'paymongo_payment_id',
        'paid_at',
        'completed_at',
    ];

    /**
     * Cast database columns to proper PHP types.
     * - subtotal/total are kept as 2-decimal strings to avoid float precision issues
     * - paid_at/completed_at become Carbon datetime objects
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Defines the allowed status transitions for the order state machine.
     * An order can only move forward (or be cancelled) — never backwards.
     *
     * pending → paid → preparing → ready → completed
     *   ↓         ↓
     * cancelled cancelled
     */
    private const TRANSITIONS = [
        'pending' => ['paid', 'cancelled'],
        'paid' => ['preparing', 'cancelled'],
        'preparing' => ['ready'],
        'ready' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * The user who placed this order.
     * Usage: $order->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * All line items (products) belonging to this order.
     * Usage: $order->items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generates a unique order number in the format ORD-XXXXXXXXX.
     * Keeps retrying until a non-duplicate is found (collision is extremely rare).
     * Called when creating a new order in Checkout::placeOrder().
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(Str::random(9));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Checks whether this order is allowed to move to the given status.
     * Uses the TRANSITIONS map above — returns false if the move is not valid.
     * Example: canTransitionTo('paid') returns true only if status is 'pending'.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Cancels this order and adds the stock back to each product.
     * Only works if the order is still 'pending' — returns false otherwise.
     * Safe to call multiple times since it checks status first.
     * Called by: PaymentCancelController, Checkout::mount(), CancelStaleOrders command.
     */
    public function cancelAndRestoreStock(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update(['status' => 'cancelled']);

        // Loop through each item and give the stock back to the product
        foreach ($this->items as $item) {
            if ($item->product_id) {
                \App\Models\Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
        }

        \Illuminate\Support\Facades\Log::info('Order cancelled and stock restored', [
            'order_number' => $this->order_number,
        ]);

        return true;
    }

    /**
     * Moves the order to a new status if the transition is allowed.
     * Automatically sets paid_at when status becomes 'paid',
     * and completed_at when status becomes 'completed'.
     * Returns true on success, false if the transition is not allowed.
     */
    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;

        // Record the exact time the payment was confirmed
        if ($newStatus === 'paid') {
            $this->paid_at = now()->toDateTimeString();
        }

        // Record the exact time the order was picked up / completed
        if ($newStatus === 'completed') {
            $this->completed_at = now()->toDateTimeString();
        }

        return $this->save();
    }

    // ─── Query Scopes ────────────────────────────────────────────────────────
    // Scopes let you filter orders cleanly: Order::pending()->get()

    /** Filter to only pending orders */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /** Filter to only paid orders */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /** Filter to only orders currently being prepared */
    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    /** Filter to only orders that are ready for pickup */
    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    /** Filter to only completed orders */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filter to all "in-progress" orders (pending, paid, preparing, ready).
     * Used in the staff dashboard to show the live order queue.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'paid', 'preparing', 'ready']);
    }
}
