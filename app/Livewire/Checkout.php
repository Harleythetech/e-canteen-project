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
    // Bound to the special instructions textarea in the form
    public string $specialInstructions = '';

    // Bound to the pickup time dropdown — must be one of the generated slots
    public string $pickupTime = '';

    /**
     * Generates the list of available pickup time slots shown in the dropdown.
     * Slots are in 15-minute increments between 7:00 AM and 5:00 PM.
     * The earliest slot is always at least 15 minutes from now.
     * If the canteen is already closed for the day, slots roll over to the next day.
     *
     * #[Computed] caches the result for the lifetime of the request.
     */
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
            // It's before opening time — start from 7:00 AM today
            $start = $dayStart;
        } elseif ($earliest->gt($dayEnd)) {
            // Canteen is closed for today — roll over to tomorrow 7:00 AM
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

    /**
     * Runs when the checkout page is first loaded.
     * Cleans up any abandoned pending orders the student left behind
     * (e.g. they opened PayMongo but closed the tab without paying).
     * Orders older than 5 minutes with a checkout session are auto-cancelled
     * and their stock is restored.
     * If the cart is empty after cleanup, redirects to the menu.
     */
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

    /**
     * Updates the quantity of an item in the cart from the checkout page.
     * Shows an error toast if the requested quantity exceeds available stock.
     */
    public function updateQuantity(int $productId, int $quantity): void
    {
        try {
            app(CartService::class)->update($productId, $quantity);
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    /**
     * Removes an item from the cart on the checkout page.
     * If the cart becomes empty after removal, redirects back to the menu.
     */
    public function removeItem(int $productId): void
    {
        app(CartService::class)->remove($productId);

        if (app(CartService::class)->isEmpty()) {
            $this->redirect(route('menu'), navigate: true);
        } else {
            $this->dispatch('toast', type: 'info', message: 'Item removed from cart.');
        }
    }

    /**
     * The main checkout action — called when the student clicks "Place Order".
     *
     * Steps:
     * 1. Validate pickup time and special instructions.
     * 2. Re-check stock for every item (in case something sold out since the cart was built).
     * 3. Create the Order record and OrderItem records, decrement stock.
     * 4. Create a PayMongo checkout session and redirect the student to it.
     * 5. If PayMongo fails, roll back everything (delete order, restore stock) and show an error.
     */
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

        // Re-verify stock — another student may have bought the last item
        foreach ($cart->items() as $productId => $item) {
            $product = Product::find($productId);
            if (!$product || !$product->is_available || $product->stock < $item['quantity']) {
                $this->dispatch('toast', type: 'error', message: "{$item['name']} is no longer available in the requested quantity.");
                return;
            }
        }

        // Create the order record in the database
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

        // Create one OrderItem per cart entry and reduce stock immediately
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

        // Create a PayMongo checkout session and redirect the student to pay
        try {
            $payMongo = app(PayMongoService::class);
            $session = $payMongo->createCheckoutSession(
                $order->load('items'),
                route('orders.confirmed', $order),   // success_url
                route('orders.payment-cancelled', $order), // cancel_url
            );

            // Save the checkout session ID so we can poll/verify it later
            $order->update(['paymongo_checkout_id' => $session['checkout_id']]);

            $cart->clear();

            // Hard redirect to PayMongo's hosted payment page
            $this->redirect($session['checkout_url']);
        } catch (\Throwable $e) {
            // Something went wrong with PayMongo — undo everything
            foreach ($cart->items() as $productId => $item) {
                Product::where('id', $productId)->increment('stock', $item['quantity']);
            }
            $order->items()->delete();
            $order->delete();

            $this->dispatch('toast', type: 'error', message: 'Payment processing failed. Please try again.');
        }
    }

    /**
     * Passes cart data to the checkout view for rendering the order summary.
     */
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
