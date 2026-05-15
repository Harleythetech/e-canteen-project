<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',        // URL-friendly version of the name, e.g. "chicken-adobo"
        'description',
        'price',
        'image_path',  // Relative path inside storage/app/public, e.g. "products/abc.jpg"
        'stock',
        'is_available',
        'sort_order',  // Controls display order in the menu
    ];

    /**
     * Cast database columns to proper PHP types.
     * - price is kept as a 2-decimal string to avoid float precision issues
     * - is_available becomes a true/false boolean
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    /**
     * The category this product belongs to (e.g. Meals, Snacks).
     * Usage: $product->category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * All order line items that include this product.
     * Used for sales reporting and stock calculations.
     * Usage: $product->orderItems
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope: only return products marked as available.
     * Usage: Product::available()->get()
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope: only return products that have stock greater than 0.
     * Usage: Product::inStock()->get()
     * Combined with available() in MenuBrowser to show only orderable items.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Returns the full public URL for the product image.
     * Returns null if no image has been uploaded.
     * Example: "https://e-canteen.test/storage/products/abc.jpg"
     */
    public function imageUrl(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::url($this->image_path);
    }
}
