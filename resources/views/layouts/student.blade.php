<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-900">
    {{-- Header --}}
    <flux:header container class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <x-app-logo href="{{ route('menu') }}" wire:navigate />

        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="squares-2x2" :href="route('menu')" :current="request()->routeIs('menu')"
                wire:navigate>
                {{ __('Menu') }}
            </flux:navbar.item>
            <flux:navbar.item icon="clipboard-document-list" :href="route('orders.index')"
                :current="request()->routeIs('orders.*')" wire:navigate>
                {{ __('My Orders') }}
            </flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        {{-- Cart button --}}
        <a href="{{ route('checkout') }}" wire:navigate
            class="relative me-2 inline-flex items-center rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
            aria-label="View cart">
            <flux:icon.shopping-cart class="size-5" />
            @if(app(\App\Services\CartService::class)->count() > 0)
                <span
                    class="absolute -top-0.5 -end-0.5 flex size-5 items-center justify-center rounded-full bg-orange-500 text-[10px] font-bold text-white"
                    aria-label="{{ app(\App\Services\CartService::class)->count() }} items in cart">
                    {{ app(\App\Services\CartService::class)->count() }}
                </span>
            @endif
        </a>

        <x-desktop-user-menu />
    </flux:header>

    {{-- Mobile nav --}}
    <flux:header class="lg:hidden border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-0!">
        <flux:navbar class="w-full justify-around">
            <flux:navbar.item icon="squares-2x2" :href="route('menu')" :current="request()->routeIs('menu')"
                wire:navigate class="text-xs!">
                {{ __('Menu') }}
            </flux:navbar.item>
            <flux:navbar.item icon="clipboard-document-list" :href="route('orders.index')"
                :current="request()->routeIs('orders.*')" wire:navigate class="text-xs!">
                {{ __('Orders') }}
            </flux:navbar.item>
            <flux:navbar.item icon="shopping-cart" :href="route('checkout')" :current="request()->routeIs('checkout')"
                wire:navigate class="text-xs!">
                {{ __('Cart') }}
            </flux:navbar.item>
            <flux:navbar.item icon="cog-6-tooth" :href="route('profile.edit')"
                :current="request()->routeIs('profile.*')" wire:navigate class="text-xs!">
                {{ __('Settings') }}
            </flux:navbar.item>
        </flux:navbar>
    </flux:header>

    <flux:main container>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>

</html>