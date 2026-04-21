<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">My Orders</flux:heading>
            <flux:subheading>Track and manage your orders</flux:subheading>
        </div>
        <flux:button href="{{ route('menu') }}" variant="primary" icon="plus" wire:navigate>
            New Order
        </flux:button>
    </div>

    {{-- Filter tabs --}}
    <div class="mb-6 flex gap-2" role="tablist" aria-label="Order filters">
        @foreach (['active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'all' => 'All'] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')" @class([
                'rounded-full px-4 py-2 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500',
                'bg-orange-500 text-white shadow-sm' => $filter === $key,
                'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600' => $filter !== $key,
            ]) role="tab"
                aria-selected="{{ $filter === $key ? 'true' : 'false' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Orders list --}}
    @if ($orders->isEmpty())
        <div
            class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white py-16 text-center dark:border-zinc-700 dark:bg-zinc-800">
            <flux:icon.clipboard-document-list class="mb-4 size-12 text-zinc-300 dark:text-zinc-600" />
            <flux:heading>No orders found</flux:heading>
            <flux:subheading>{{ $filter === 'active' ? 'You have no active orders.' : 'No orders match this filter.' }}
            </flux:subheading>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" wire:navigate
                    class="block rounded-xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</span>
                                @php
                                    $badgeColor = match ($order->status) {
                                        'pending' => 'yellow',
                                        'paid' => 'blue',
                                        'preparing' => 'orange',
                                        'ready' => 'green',
                                        'completed' => 'zinc',
                                        'cancelled' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$badgeColor" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                            </div>
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $order->items->pluck('product_name')->take(3)->join(', ') }}
                                @if ($order->items->count() > 3)
                                    +{{ $order->items->count() - 3 }} more
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-zinc-400">{{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-end">
                            <span class="text-lg font-bold text-orange-600">₱{{ number_format($order->total, 0) }}</span>
                            <p class="text-xs text-zinc-500">{{ $order->items->sum('quantity') }} items</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>