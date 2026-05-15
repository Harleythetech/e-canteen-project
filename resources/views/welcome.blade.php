<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <title>E-Canteen — Skip the line. Order ahead.</title>
</head>

<body class="min-h-screen bg-orange-50/50 antialiased dark:bg-zinc-950">
    {{-- Skip to content (WCAG 2.4.1) --}}
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:rounded-lg focus:bg-orange-500 focus:px-4 focus:py-2 focus:text-white focus:outline-none">
        Skip to main content
    </a>

    {{-- Navbar --}}
    <header
        class="sticky top-0 z-40 border-b border-orange-100/60 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4" aria-label="Primary navigation">
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500" aria-hidden="true">
                    <x-app-logo-icon class="size-5 stroke-white" />
                </div>
                <div>
                    <span class="block text-lg font-bold text-zinc-900 dark:text-zinc-100">E-CANTEEN</span>
                    <span class="block text-xs text-zinc-400 dark:text-zinc-500"
                        style="font-size: 11px; margin-top: -2px;">Pre-Order
                        System</span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ match (auth()->user()->role) { 'admin' => route('admin.dashboard'), 'staff' => route('staff.dashboard'), default => route('menu')} }}"
                        class="text-sm font-medium text-zinc-600 hover:text-orange-600 dark:text-zinc-400 dark:hover:text-orange-400">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="text-sm font-medium text-zinc-600 hover:text-orange-600 dark:text-zinc-400 dark:hover:text-orange-400">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center rounded-full bg-orange-500 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                        Get Started
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main id="main-content">
        {{-- Hero --}}
        <section class="mx-auto max-w-7xl px-6 py-20 lg:py-28" aria-labelledby="hero-heading">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                {{-- Left --}}
                <div>
                    <div
                        class="mb-5 inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1 text-sm font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                        <span class="inline-block h-2 w-2 rounded-full bg-orange-500" aria-hidden="true"></span>
                        Now Available
                    </div>
                    <h1 id="hero-heading"
                        class="text-5xl font-bold tracking-tight text-zinc-900 lg:text-6xl dark:text-zinc-100"
                        style="line-height: 1.1;">
                        Skip the line.<br>Order ahead.
                    </h1>
                    <p class="mt-5 max-w-md text-base leading-relaxed text-zinc-500 dark:text-zinc-400">
                        Pre-order your meals, pay securely online, and pick up with a QR code. Fast, convenient, and
                        completely cashless.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center gap-2 rounded-full bg-orange-500 px-7 py-3 text-base font-semibold text-white shadow-md hover:bg-orange-600">
                            Order Now
                            <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Right: Order card --}}
                <div class="relative hidden lg:block" aria-hidden="true">
                    <div class="mx-auto" style="max-width: 380px;">
                        {{-- Card --}}
                        <div class="overflow-hidden rounded-2xl border border-zinc-100 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"
                            style="box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                            <div class="mb-4 flex items-center justify-between">
                                <div>
                                    <p style="font-size: 11px;" class="text-zinc-400 dark:text-zinc-500">Average wait</p>
                                    <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">2<span
                                            class="text-sm font-medium text-zinc-400 dark:text-zinc-500">min</span></p>
                                </div>
                                <div class="text-right">
                                    <p style="font-size: 11px;" class="text-zinc-400 dark:text-zinc-500">Order #2847</p>
                                    <p class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Ready to Pickup</p>
                                </div>
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-500"
                                    style="box-shadow: 0 4px 12px rgba(34,197,94,0.3);">
                                    <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="3" stroke="white">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </div>
                            </div>
                            {{-- QR --}}
                            <div class="mx-auto mb-2 flex items-center justify-center overflow-hidden rounded-xl bg-orange-50/50 dark:bg-zinc-800"
                                style="width: 192px; height: 192px;">
                                <svg width="144" height="144" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1" class="stroke-zinc-800 dark:stroke-zinc-300">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                                </svg>
                            </div>
                            <p class="text-center text-xs text-zinc-400 dark:text-zinc-500">Scan to claim your order</p>
                        </div>

                        {{-- Stats --}}
                        <div class="mt-4 flex gap-3">
                            <div
                                class="flex-1 rounded-xl border border-zinc-100 bg-orange-50/30 px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-900">
                                <p style="font-size: 11px;" class="text-zinc-400 dark:text-zinc-500">Time</p>
                                <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">11:30 AM</p>
                            </div>
                            <div
                                class="flex-1 rounded-xl border border-zinc-100 bg-orange-50/30 px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-900">
                                <p style="font-size: 11px;" class="text-zinc-400 dark:text-zinc-500">Total</p>
                                <p class="text-sm font-bold text-orange-600 dark:text-orange-400">₱145</p>
                            </div>
                            <div
                                class="flex-1 rounded-xl border border-zinc-100 bg-orange-50/30 px-4 py-2.5 dark:border-zinc-800 dark:bg-zinc-900">
                                <p style="font-size: 11px;" class="text-zinc-400 dark:text-zinc-500">Orders today</p>
                                <p class="text-sm font-bold text-green-600 dark:text-green-400">143</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section class="bg-white py-20 lg:py-28 dark:bg-zinc-900" aria-labelledby="how-heading">
            <div class="mx-auto max-w-4xl px-6 text-center">
                <h2 id="how-heading" class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">How it works</h2>
                <div class="mt-14 grid gap-10 md:grid-cols-3">
                    <div class="flex flex-col items-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-500 text-white"
                            style="box-shadow: 0 8px 20px rgba(249,115,22,0.25);" aria-hidden="true">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-zinc-900 dark:text-zinc-100">Browse & Order</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">View the menu, select
                            your items, and
                            customize your order in seconds.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-500 text-white"
                            style="box-shadow: 0 8px 20px rgba(249,115,22,0.25);" aria-hidden="true">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-zinc-900 dark:text-zinc-100">Pay Securely</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">Complete your payment
                            with GCash, card, or
                            other digital wallets.</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-500 text-white"
                            style="box-shadow: 0 8px 20px rgba(249,115,22,0.25);" aria-hidden="true">
                            <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-zinc-900 dark:text-zinc-100">Quick Pickup</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">Show your QR code at
                            the counter and
                            collect your order instantly.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA Banner --}}
        <section class="px-6 py-16" aria-labelledby="cta-heading">
            <div class="mx-auto max-w-3xl overflow-hidden rounded-3xl bg-orange-500 px-8 py-14 text-center"
                style="box-shadow: 0 10px 40px rgba(249,115,22,0.2);">
                <h2 id="cta-heading" class="text-3xl font-bold text-white">Ready to skip the line?</h2>
                <p class="mt-3 text-base text-orange-100">Join hundreds of students and staff
                    using E-Canteen every day.</p>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-2 rounded-full border-2 border-white bg-white px-7 py-3 text-base font-semibold text-orange-600 hover:bg-orange-50">
                    Create Free Account
                    <svg width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-100 bg-white py-8 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
            <div class="flex items-center gap-2">
                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-orange-500" aria-hidden="true">
                    <x-app-logo-icon class="size-4 stroke-white" />
                </div>
                <div>
                    <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">E-Canteen</p>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">&copy; {{ date('Y') }} All rights reserved.</p>
                </div>
            </div>
            <nav class="flex gap-6" aria-label="Footer">
                <a href="{{ route('privacy') }}"
                    class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Privacy</a>
                <a href="{{ route('terms') }}"
                    class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Terms</a>
                <a href="{{ route('support') }}"
                    class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Support</a>
            </nav>
        </div>
    </footer>

    @fluxScripts
</body>

</html>