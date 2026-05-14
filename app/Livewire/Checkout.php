<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PayMongoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\Computed;

#[Layout('layouts::student')]
#[Title('Checkout')]
class Checkout extends Component
{
    public string $specialInstructions = '';
    public string $pickupTime = '';

    #[Computed]
    public function pickupTimeSlots(): array
    {
        $now = \Carbon\Carbon::now();
        $openHour = 7;
        $closeHour = 17;

        // Round up to the next 15-minute slot, at least 15 minutes from now
        $future = $now->copy()->addMinutes(15);
        $minuteRemainder = $future->minute % 15;
        if ($minuteRemainder > 0) {
            $future->addMinutes(15 - $minuteRemainder);
        }
        $earliest = $future->second(0);

        $dayStart = $now->copy()->setTime($openHour, 0, 0);
        $dayEnd = $now->copy()->setTime($closeHour, 0, 0);

        if ($earliest->lt($dayStart)) {
            $start = $dayStart;
        } elseif ($earliest->gt($dayEnd)) {
            $start = $now->copy()->addDay()->setTime($openHour, 0, 0);
            $dayEnd = $start->copy()->setTime($closeHour, 0, 0);
        } else {
            $start = $earliest;
        }

        $slots = [];
        while ($start->lte($dayEnd)) {
            $slots[] = $start->format('g:i A');
            $start->addMinutes(15);
        }

        return $slots;
    }

    public function mount()
    {
        // Cancel any abandoned pending orders for this user.
        // This fires when the user navigates back to checkout after
        // leaving PayMongo without paying (GCash/Maya expire, GrabPay cancel, etc.)
        Order::where('user_id', auth()->id())
            ->where('status', 'pending')
            ->whereNotNull('paymongo_checkout_id')
            ->where('created_at', '<=', now()->subMinutes(5))
            ->with('items')
            ->get()
            ->each(fn(Order $order) => $order->cancelAndRestoreStock());

        if (app(CartService::class)->isEmpty()) {
            $this->redirect(route('menu'), navigate: true);
        }
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        app(CartService::class)->update($productId, $quantity);
    }

    public function removeItem(int $productId): void
    {
        app(CartService::class)->remove($productId);

        if (app(CartService::class)->isEmpty()) {
            $this->redirect(route('menu'), navigate: true);
        } else {
            $this->dispatch('toast', type: 'info', message: 'Item removed from cart.');
        }
    }

    public function placeOrder(): void
    {
        $this->validate([
            'pickupTime' => ['required', 'string'],
            'specialInstructions' => ['nullable', 'string', 'max:500'],
        ]);

        $cart = app(CartService::class);

        if ($cart->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Your cart is empty.');
            return;
        }

        // Verify stock availability
        foreach ($cart->items() as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || !$product->is_available || $product->stock < $item['quantity']) {
                $this->dispatch('toast', type: 'error', message: "{$item['name']} is no longer available in the requested quantity.");
                return;
            }
        }

        // Create order
        $subtotal = $cart->subtotal();
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'pickup_time' => $this->pickupTime,
            'special_instructions' => $this->specialInstructions ?: null,
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        // Create order items and decrement stock
        foreach ($cart->items() as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
            ]);

            Product::where('id', $productId)->decrement('stock', $item['quantity']);
        }

        // Create PayMongo checkout session
        try {
            $payMongo = app(PayMongoService::class);
            $session = $payMongo->createCheckoutSession(
                $order->load('items'),
                route('orders.confirmed', $order),
                route('orders.payment-cancelled', $order),
            );

            $order->update(['paymongo_checkout_id' => $session['checkout_id']]);

            $cart->clear();

            $this->redirect($session['checkout_url']);
        } catch (\Throwable $e) {
            // Rollback: delete order and restore stock
            foreach ($cart->items() as $productId => $item) {
                Product::where('id', $productId)->increment('stock', $item['quantity']);
            }
            $order->items()->delete();
            $order->delete();

            $this->dispatch('toast', type: 'error', message: 'Payment processing failed. Please try again.');
        }
    }

    public function render()
    {
        $cart = app(CartService::class);

        return view('livewire.checkout', [
            'cartItems' => $cart->items(),
            'cartCount' => $cart->count(),
            'subtotal' => $cart->subtotal(),
        ]);
    }
}
