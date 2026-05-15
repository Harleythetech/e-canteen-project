<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',       // URL-friendly version of the name, e.g. "beverages"
        'sort_order', // Controls the display order in the menu filter tabs
        'is_active',  // Inactive categories are hidden from the student menu
    ];

    /**
     * Cast is_active to a true/false boolean instead of 0/1.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * All products that belong to this category.
     * Usage: $category->products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope: only return categories that are marked active.
     * Inactive categories are hidden from students but still visible to staff/admin.
     * Usage: Category::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
