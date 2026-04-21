<div class="flex flex-col gap-6 lg:flex-row">
    {{-- Main content --}}
    <div class="flex-1">
        {{-- Search --}}
        <div class="mb-6">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Search menu..." icon="magnifying-glass"
                clearable />
        </div>

        {{-- Category tabs --}}
        <div class="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="Menu categories">
            <button wire:click="$set('category', null)" @class([
                'rounded-full px-4 py-2 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500',
                'bg-orange-500 text-white shadow-sm' => $category === null,
                'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600' => $category !== null,
            ]) role="tab"
                aria-selected="{{ $category === null ? 'true' : 'false' }}">
                All
            </button>

            @foreach ($categories as $cat)
                <button wire:click="$set('category', {{ $cat->id }})" @class([
                    'rounded-full px-4 py-2 text-sm font-medium transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500',
                    'bg-orange-500 text-white shadow-sm' => $category === $cat->id,
                    'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600' => $category !== $cat->id,
                ]) role="tab" aria-selected="{{ $category === $cat->id ? 'true' : 'false' }}">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- Product grid --}}
        @if ($products->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <flux:icon.shopping-bag class="mb-4 size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading>No items found</flux:heading>
                <flux:subheading>Try a different category or search term.</flux:subheading>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" role="list">
                @foreach ($products as $product)
                    <div class="group flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800"
                        role="listitem">
                        {{-- Product image --}}
                        <div class="relative aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-700">
                            @if ($product->imageUrl())
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                    class="size-full object-cover transition group-hover:scale-105" loading="lazy">
                            @else
                                <div class="flex size-full items-center justify-center">
                                    <flux:icon.photo class="size-12 text-zinc-300 dark:text-zinc-500" />
                                </div>
                            @endif

                            @if ($product->stock <= 5 && $product->stock > 0)
                                <span
                                    class="absolute top-2 end-2 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white">
                                    {{ $product->stock }} left
                                </span>
                            @endif
                        </div>

                        {{-- Product info --}}
                        <div class="flex flex-1 flex-col p-4">
                            <div class="mb-1 flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h3>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product->category->name }}</p>
                                </div>
                                <span
                                    class="shrink-0 text-lg font-bold text-orange-600">₱{{ number_format($product->price, 0) }}</span>
                            </div>

                            @if ($product->description)
                                <p class="mb-3 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">{{ $product->description }}
                                </p>
                            @endif

                            <div class="mt-auto">
                                @if (isset($cartItems[$product->id]))
                                    <div
                                        class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-950/30">
                                        <button
                                            wire:click="updateCartQuantity({{ $product->id }}, {{ $cartItems[$product->id]['quantity'] - 1 }})"
                                            class="flex size-8 items-center justify-center rounded-md bg-white text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-orange-500 dark:bg-zinc-700 dark:text-zinc-200"
                                            aria-label="Decrease {{ $product->name }} quantity">
                                            <flux:icon.minus class="size-4" />
                                        </button>
                                        <span
                                            class="text-sm font-semibold text-orange-700 dark:text-orange-400">{{ $cartItems[$product->id]['quantity'] }}</span>
                                        <button
                                            wire:click="updateCartQuantity({{ $product->id }}, {{ $cartItems[$product->id]['quantity'] + 1 }})"
                                            class="flex size-8 items-center justify-center rounded-md bg-white text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-orange-500 dark:bg-zinc-700 dark:text-zinc-200"
                                            aria-label="Increase {{ $product->name }} quantity">
                                            <flux:icon.plus class="size-4" />
                                        </button>
                                    </div>
                                @else
                                    <flux:button wire:click="addToCart({{ $product->id }})" variant="primary" class="w-full"
                                        icon="plus">
                                        Add to Cart
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Cart sidebar (desktop) --}}
    <aside class="hidden w-80 shrink-0 lg:block" aria-label="Shopping cart">
        <div
            class="sticky top-20 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">
                    <flux:icon.shopping-cart class="inline size-5" /> Cart
                </flux:heading>
                @if ($cartCount > 0)
                    <flux:badge color="orange">{{ $cartCount }} {{ Str::plural('item', $cartCount) }}</flux:badge>
                @endif
            </div>

            @if (empty($cartItems))
                <div class="py-8 text-center">
                    <flux:icon.shopping-bag class="mx-auto mb-2 size-10 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="text-sm">Your cart is empty</flux:text>
                </div>
            @else
                <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @foreach ($cartItems as $productId => $item)
                        <div class="flex items-center justify-between gap-3 py-3" wire:key="cart-{{ $productId }}">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</p>
                                <p class="text-xs text-zinc-500">₱{{ number_format($item['price'], 0) }} ×
                                    {{ $item['quantity'] }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">₱{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                                <button wire:click="removeFromCart({{ $productId }})"
                                    class="text-zinc-400 hover:text-red-500 focus-visible:outline-2 focus-visible:outline-orange-500"
                                    aria-label="Remove {{ $item['name'] }} from cart">
                                    <flux:icon.x-mark class="size-4" />
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <div class="mb-4 flex items-center justify-between text-base font-bold">
                        <span class="text-zinc-900 dark:text-zinc-100">Subtotal</span>
                        <span class="text-orange-600">₱{{ number_format($cartSubtotal, 0) }}</span>
                    </div>
                    <flux:button href="{{ route('checkout') }}" variant="primary" class="w-full" icon="arrow-right"
                        wire:navigate>
                        Proceed to Checkout
                    </flux:button>
                </div>
            @endif
        </div>
    </aside>

    {{-- Mobile cart sticky bar --}}
    @if ($cartCount > 0)
        <div
            class="fixed inset-x-0 bottom-16 z-30 border-t border-zinc-200 bg-white p-3 shadow-lg lg:hidden dark:border-zinc-700 dark:bg-zinc-800">
            <a href="{{ route('checkout') }}" wire:navigate
                class="flex items-center justify-between rounded-xl bg-orange-500 px-5 py-3 text-white shadow-sm transition hover:bg-orange-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                <div class="flex items-center gap-3">
                    <flux:icon.shopping-cart class="size-5" />
                    <span class="font-semibold">{{ $cartCount }} {{ Str::plural('item', $cartCount) }}</span>
                </div>
                <span class="text-lg font-bold">₱{{ number_format($cartSubtotal, 0) }}</span>
            </a>
        </div>
    @endif
</div>