<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartService
{
    private const SESSION_KEY = 'cart';
    private const TTL_MINUTES = 30;

    public function add(int $productId, int $quantity = 1): void
    {
        $product = Product::available()->inStock()->findOrFail($productId);

        $cart = $this->items();
        $existing = $cart[$productId] ?? null;
        $newQty = ($existing['quantity'] ?? 0) + $quantity;

        if ($newQty > $product->stock) {
            throw new \InvalidArgumentException("Only {$product->stock} items available.");
        }

        $cart[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => $newQty,
            'image_path' => $product->image_path,
        ];

        $this->save($cart);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->items();

        if ($quantity <= 0) {
            $this->remove($productId);
            return;
        }

        if (!isset($cart[$productId])) {
            return;
        }

        $product = Product::available()->inStock()->findOrFail($productId);

        if ($quantity > $product->stock) {
            throw new \InvalidArgumentException("Only {$product->stock} items available.");
        }

        $cart[$productId]['quantity'] = $quantity;
        $this->save($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->items();
        unset($cart[$productId]);
        $this->save($cart);
    }

    public function items(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'quantity'));
    }

    public function subtotal(): float
    {
        return collect($this->items())->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return empty($this->items());
    }

    private function save(array $cart): void
    {
        Session::put(self::SESSION_KEY, $cart);
    }
}
