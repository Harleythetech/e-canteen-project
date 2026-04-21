<div class="mx-auto max-w-lg text-center">
    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        {{-- Success icon --}}
        <div
            class="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
            <flux:icon.check-circle class="size-10 text-green-500" />
        </div>

        <flux:heading size="xl" class="mb-2">Order Confirmed!</flux:heading>
        <flux:subheading class="mb-6">Your order has been placed successfully.</flux:subheading>

        {{-- Order details --}}
        <div class="mb-6 rounded-lg bg-zinc-50 p-4 dark:bg-zinc-900">
            <div class="mb-2 text-sm text-zinc-600 dark:text-zinc-400">Order Number</div>
            <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="text-zinc-500">Status</div>
                <div class="font-semibold capitalize text-orange-600">{{ $order->status }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="text-zinc-500">Total</div>
                <div class="font-semibold text-zinc-900 dark:text-zinc-100">₱{{ number_format($order->total, 0) }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="text-zinc-500">Pickup Time</div>
                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->pickup_time }}</div>
            </div>
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <div class="text-zinc-500">Items</div>
                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->items->sum('quantity') }}</div>
            </div>
        </div>

        <p class="mb-6 text-sm text-zinc-600 dark:text-zinc-400">
            You'll receive a QR code once your payment is confirmed. Show it at the counter to pick up your order.
        </p>

        <div class="flex flex-col gap-3">
            <flux:button href="{{ route('orders.show', $order) }}" variant="primary" class="w-full" wire:navigate
                icon="eye">
                Track My Order
            </flux:button>
            <flux:button href="{{ route('menu') }}" variant="ghost" class="w-full" wire:navigate>
                Back to Menu
            </flux:button>
        </div>
    </div>
</div>