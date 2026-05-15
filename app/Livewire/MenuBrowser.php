<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::student')]
#[Title('Menu')]
class MenuBrowser extends Component
{
    #[Url]
    public ?int $category = null;

    public string $search = '';

    public function addToCart(int $productId, int $quantity = 1): void
    {
        $product = \App\Models\Product::find($productId);
        $name = $product?->name ?? 'Item';

        try {
            app(CartService::class)->add($productId, $quantity);
            $this->dispatch('cart-updated');
            $this->dispatch('toast', type: 'success', message: "{$name} added to cart.");
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function removeFromCart(int $productId): void
    {
        $product = \App\Models\Product::find($productId);
        app(CartService::class)->remove($productId);
        $this->dispatch('cart-updated');
        $name = $product?->name ?? 'Item';
        $this->dispatch('toast', type: 'info', message: "{$name} removed from cart.");
    }

    public function updateCartQuantity(int $productId, int $quantity): void
    {
        try {
            app(CartService::class)->update($productId, $quantity);
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $categories = Category::active()->orderBy('sort_order')->get();

        $products = Product::available()
            ->inStock()
            ->when($this->category, fn($q) => $q->where('category_id', $this->category))
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->get();

        $cart = app(CartService::class);

        return view('livewire.menu-browser', [
            'categories' => $categories,
            'products' => $products,
            'cartItems' => $cart->items(),
            'cartCount' => $cart->count(),
            'cartSubtotal' => $cart->subtotal(),
        ]);
    }
}
