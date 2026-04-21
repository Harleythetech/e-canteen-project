<div class="mx-auto max-w-2xl" wire:poll.5s>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Order {{ $order->order_number }}</flux:heading>
            <flux:subheading>Placed {{ $order->created_at->diffForHumans() }}</flux:subheading>
        </div>
        <flux:button href="{{ route('orders.index') }}" variant="ghost" icon="arrow-left" wire:navigate>
            All Orders
        </flux:button>
    </div>

    {{-- Status tracker --}}
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        @php
            $steps = ['pending' => 'Pending', 'paid' => 'Paid', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Completed'];
            $currentIdx = array_search($order->status, array_keys($steps));
            $isCancelled = $order->status === 'cancelled';
        @endphp

        @if ($isCancelled)
            <div class="flex items-center gap-3 rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                <flux:icon.x-circle class="size-8 text-red-500" />
                <div>
                    <p class="font-semibold text-red-700 dark:text-red-400">Order Cancelled</p>
                    <p class="text-sm text-red-600 dark:text-red-500">This order has been cancelled.</p>
                </div>
            </div>
        @else
            <nav aria-label="Order progress">
                <ol class="flex items-center justify-between">
                    @foreach ($steps as $key => $label)
                        @php
                            $stepIdx = array_search($key, array_keys($steps));
                            $isCompleted = $stepIdx < $currentIdx;
                            $isCurrent = $stepIdx === $currentIdx;
                        @endphp
                        <li class="flex flex-1 flex-col items-center text-center">
                            <div @class([
                                'flex size-10 items-center justify-center rounded-full text-sm font-bold transition',
                                'bg-green-500 text-white' => $isCompleted,
                                'bg-orange-500 text-white ring-4 ring-orange-100 dark:ring-orange-900/30' => $isCurrent,
                                'bg-zinc-100 text-zinc-400 dark:bg-zinc-700 dark:text-zinc-500' => !$isCompleted && !$isCurrent,
                            ])>
                                @if ($isCompleted)
                                    <flux:icon.check class="size-5" />
                                @else
                                    {{ $stepIdx + 1 }}
                                @endif
                            </div>
                            <span @class([
                                'mt-2 text-xs font-medium',
                                'text-green-600 dark:text-green-400' => $isCompleted,
                                'text-orange-600 dark:text-orange-400' => $isCurrent,
                                'text-zinc-400 dark:text-zinc-500' => !$isCompleted && !$isCurrent,
                            ])>{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
            </nav>

            @if ($order->status === 'ready')
                <div class="mt-6 rounded-lg bg-green-50 p-4 text-center dark:bg-green-900/20">
                    <flux:icon.check-badge class="mx-auto mb-2 size-12 text-green-500" />
                    <p class="text-lg font-bold text-green-700 dark:text-green-400">Your order is ready!</p>
                    <p class="text-sm text-green-600 dark:text-green-500">Show the QR code below at the counter.</p>
                </div>
            @endif
        @endif
    </div>

    {{-- QR Code (shown when paid or beyond) --}}
    @if (in_array($order->status, ['paid', 'preparing', 'ready']))
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <flux:heading class="mb-4">Pickup QR Code</flux:heading>
            <div class="mx-auto flex size-48 items-center justify-center"
                aria-label="QR code for order {{ $order->order_number }}">
                {!! $qrSvg !!}
            </div>
            <p class="mt-2 text-xs text-zinc-500">Scan at counter to claim your order</p>
        </div>
    @endif

    {{-- Order items --}}
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <flux:heading>Order Details</flux:heading>
        </div>
        <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
            @foreach ($order->items as $item)
                <div class="flex items-center justify-between p-4">
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->product_name }}</p>
                        <p class="text-sm text-zinc-500">₱{{ number_format($item->unit_price, 0) }} × {{ $item->quantity }}
                        </p>
                    </div>
                    <span
                        class="font-semibold text-zinc-900 dark:text-zinc-100">₱{{ number_format($item->lineTotal(), 0) }}</span>
                </div>
            @endforeach
        </div>
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <div class="flex justify-between text-base font-bold">
                <span class="text-zinc-900 dark:text-zinc-100">Total</span>
                <span class="text-orange-600">₱{{ number_format($order->total, 0) }}</span>
            </div>
        </div>
    </div>

    {{-- Meta info --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-xs text-zinc-500">Pickup Time</p>
            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->pickup_time ?? 'N/A' }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-xs text-zinc-500">Payment</p>
            <p class="font-semibold capitalize text-zinc-900 dark:text-zinc-100">
                {{ $order->payment_method ?? 'Pending' }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-xs text-zinc-500">Paid At</p>
            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->paid_at?->format('g:i A') ?? '—' }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-xs text-zinc-500">Completed</p>
            <p class="font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $order->completed_at?->format('g:i A') ?? '—' }}</p>
        </div>
    </div>

    @if ($order->special_instructions)
        <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="mb-1 text-xs font-medium text-zinc-500">Special Instructions</p>
            <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $order->special_instructions }}</p>
        </div>
    @endif

    {{-- Cancel button (only for pending) --}}
    @if ($order->status === 'pending' && auth()->id() === $order->user_id)
        <div class="text-center">
            <flux:button wire:click="cancelOrder" wire:confirm="Are you sure you want to cancel this order?"
                variant="danger" icon="x-mark">
                Cancel Order
            </flux:button>
        </div>
    @endif
</div>