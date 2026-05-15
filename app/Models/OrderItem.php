<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name', // Snapshot of the product name at time of order (in case product is renamed/deleted later)
        'quantity',
        'unit_price',   // Snapshot of the price at time of order (in case price changes later)
    ];

    /**
     * Cast unit_price to a 2-decimal string to avoid float precision issues.
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    /**
     * The order this item belongs to.
     * Usage: $item->order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The product this item references.
     * Note: product_name and unit_price are stored as snapshots,
     * so this relationship may be null if the product was deleted.
     * Usage: $item->product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculates the total price for this line item.
     * Example: 3 × ₱75.00 = ₱225.00
     * Used in order summaries and receipts.
     */
    public function lineTotal(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
