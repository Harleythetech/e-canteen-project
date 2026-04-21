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

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /** Valid status transitions */
    private const TRANSITIONS = [
        'pending' => ['paid', 'cancelled'],
        'paid' => ['preparing', 'cancelled'],
        'preparing' => ['ready'],
        'ready' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(Str::random(9));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;

        if ($newStatus === 'paid') {
            $this->paid_at = now()->toDateTimeString();
        }

        if ($newStatus === 'completed') {
            $this->completed_at = now()->toDateTimeString();
        }

        return $this->save();
    }

    // Status scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePreparing($query)
    {
        return $query->where('status', 'preparing');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'paid', 'preparing', 'ready']);
    }
}
