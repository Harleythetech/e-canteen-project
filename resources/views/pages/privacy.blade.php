<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Privacy Policy — E-Canteen</title>
</head>
<body class="min-h-screen bg-orange-50/50 antialiased dark:bg-zinc-950">

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-orange-100/60 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/80">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-500">
                    <x-app-logo-icon class="size-5 stroke-white" />
                </div>
                <div>
                    <span class="block text-lg font-bold text-zinc-900 dark:text-zinc-100">E-CANTEEN</span>
                    <span class="block text-zinc-400 dark:text-zinc-500" style="font-size: 11px; margin-top: -2px;">Pre-Order System</span>
                </div>
            </a>
            <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-500 hover:text-orange-600 dark:text-zinc-400 dark:hover:text-orange-400">
                ← Back to Home
            </a>
        </nav>
    </header>

    <main class="mx-auto max-w-3xl px-6 py-16">
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Privacy Policy</h1>
        <p class="mt-2 text-sm text-zinc-400 dark:text-zinc-500">Last updated: {{ date('F Y') }}</p>

        <div class="mt-10 space-y-8 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">1. Information We Collect</h2>
                <p>When you register and use E-Canteen, we collect information you provide directly — such as your name, email address, and password. We also collect order data including items purchased, payment status, and timestamps.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">2. How We Use Your Information</h2>
                <p>We use your information to process and fulfill your orders, send order confirmations and status updates, and improve the platform. We do not sell or share your personal data with third parties for marketing purposes.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">3. Payment Information</h2>
                <p>Payments are processed securely through PayMongo. We do not store your card or wallet credentials. Payment data is handled in accordance with PayMongo's privacy and security standards.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">4. QR Codes</h2>
                <p>QR codes generated for order pickup are unique to each order and expire once the order is claimed or cancelled. They are not linked to any persistent personal identifier beyond your order record.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">5. Data Retention</h2>
                <p>Order and account data is retained for operational and reporting purposes. You may request deletion of your account by contacting canteen staff or the system administrator.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">6. Contact</h2>
                <p>For privacy-related concerns, please reach out through the <a href="{{ route('support') }}" class="text-orange-500 hover:underline">Support</a> page.</p>
            </section>

        </div>
    </main>

    <footer class="border-t border-zinc-100 bg-white py-8 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
            <p class="text-xs text-zinc-400 dark:text-zinc-500">&copy; {{ date('Y') }} E-Canteen. All rights reserved.</p>
            <nav class="flex gap-6">
                <a href="{{ route('privacy') }}" class="text-sm font-medium text-orange-500">Privacy</a>
                <a href="{{ route('terms') }}" class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Terms</a>
                <a href="{{ route('support') }}" class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Support</a>
            </nav>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
