<div class="mx-auto max-w-3xl">
    <div class="mb-6">
        <flux:heading size="xl">Checkout</flux:heading>
        <flux:subheading>Review your order and complete payment</flux:subheading>
    </div>

    @if (session('payment_cancelled'))
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            {{ session('payment_cancelled') }}
        </flux:callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Order items --}}
        <div class="lg:col-span-3">
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading>Order Items ({{ $cartCount }})</flux:heading>
                </div>

                <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @foreach ($cartItems as $productId => $item)
                        <div class="flex items-center gap-4 p-4" wire:key="checkout-{{ $productId }}">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</p>
                                <p class="text-sm text-zinc-500">₱{{ number_format($item['price'], 0) }} each</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button wire:click="updateQuantity({{ $productId }}, {{ $item['quantity'] - 1 }})"
                                    class="flex size-7 items-center justify-center rounded-md border border-zinc-300 text-zinc-600 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-orange-500 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                    aria-label="Decrease quantity">
                                    <flux:icon.minus class="size-3" />
                                </button>
                                <span
                                    class="w-6 text-center text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $productId }}, {{ $item['quantity'] + 1 }})"
                                    class="flex size-7 items-center justify-center rounded-md border border-zinc-300 text-zinc-600 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-orange-500 dark:border-zinc-600 dark:text-zinc-400 dark:hover:bg-zinc-700"
                                    aria-label="Increase quantity">
                                    <flux:icon.plus class="size-3" />
                                </button>
                            </div>

                            <span class="w-16 text-end text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                ₱{{ number_format($item['price'] * $item['quantity'], 0) }}
                            </span>

                            <button wire:click="removeItem({{ $productId }})"
                                class="text-zinc-400 hover:text-red-500 focus-visible:outline-2 focus-visible:outline-orange-500"
                                aria-label="Remove {{ $item['name'] }}">
                                <flux:icon.trash class="size-4" />
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Special instructions --}}
            <div class="mt-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:textarea wire:model="specialInstructions" :label="__('Special Instructions')"
                    placeholder="Any allergies or special requests?" rows="2" />
            </div>
        </div>

        {{-- Order summary --}}
        <div class="lg:col-span-2">
            <div
                class="sticky top-20 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:heading class="mb-4">Order Summary</flux:heading>

                {{-- Pickup time --}}
                <div class="mb-4">
                    <flux:select wire:model="pickupTime" :label="__('Pickup Time')" placeholder="Select a time..."
                        required>
                        @foreach ($this->pickupTimeSlots as $slot)
                            <flux:select.option value="{{ $slot }}">
                                {{ $slot }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('pickupTime')
                        <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                    @enderror
                </div>

                <flux:separator class="my-4" />

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Subtotal</span>
                        <span>₱{{ number_format($subtotal, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Service fee</span>
                        <span>Free</span>
                    </div>
                </div>

                <flux:separator class="my-4" />

                <div class="mb-4 flex justify-between text-lg font-bold text-zinc-900 dark:text-zinc-100">
                    <span>Total</span>
                    <span class="text-orange-600">₱{{ number_format($subtotal, 0) }}</span>
                </div>

                <flux:button wire:click="placeOrder" variant="primary" class="w-full" icon="lock-closed"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="placeOrder">Pay with PayMongo</span>
                    <span wire:loading wire:target="placeOrder">Processing...</span>
                </flux:button>

                <p class="mt-3 text-center text-xs text-zinc-500">
                    You'll be redirected to PayMongo's secure payment page.
                </p>

                <flux:button href="{{ route('menu') }}" variant="ghost" class="mt-2 w-full" wire:navigate>
                    ← Continue Shopping
                </flux:button>
            </div>
        </div>
    </div>
</div>