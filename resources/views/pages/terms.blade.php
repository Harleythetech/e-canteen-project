<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Terms of Service — E-Canteen</title>
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
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Terms of Service</h1>
        <p class="mt-2 text-sm text-zinc-400 dark:text-zinc-500">Last updated: {{ date('F Y') }}</p>

        <div class="mt-10 space-y-8 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">1. Acceptance of Terms</h2>
                <p>By creating an account and using E-Canteen, you agree to these Terms of Service. If you do not agree, please do not use the platform.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">2. Eligibility</h2>
                <p>E-Canteen is intended for use by enrolled students, faculty, and staff of Pamantasan ng Lungsod ng San Pablo. Accounts are personal and non-transferable.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">3. Orders and Payments</h2>
                <p>Orders are confirmed only upon successful payment. Once an order is paid and confirmed, it enters the preparation queue. Cancellations may only be requested within the allowed window before preparation begins.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">4. Pickup Policy</h2>
                <p>Orders must be picked up within the designated time window using the QR code provided. Unclaimed orders may be forfeited without refund after the pickup window expires.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">5. Refunds</h2>
                <p>Refunds for cancelled orders are subject to the canteen's refund policy and PayMongo's processing timelines. We are not responsible for delays caused by payment processors.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">6. Account Responsibility</h2>
                <p>You are responsible for maintaining the confidentiality of your account credentials. Any activity under your account is your responsibility. Report unauthorized access immediately.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">7. Modifications</h2>
                <p>We reserve the right to update these terms at any time. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>
            </section>

            <section>
                <h2 class="mb-2 text-base font-semibold text-zinc-900 dark:text-zinc-100">8. Contact</h2>
                <p>Questions about these terms? Visit our <a href="{{ route('support') }}" class="text-orange-500 hover:underline">Support</a> page.</p>
            </section>

        </div>
    </main>

    <footer class="border-t border-zinc-100 bg-white py-8 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
            <p class="text-xs text-zinc-400 dark:text-zinc-500">&copy; {{ date('Y') }} E-Canteen. All rights reserved.</p>
            <nav class="flex gap-6">
                <a href="{{ route('privacy') }}" class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Privacy</a>
                <a href="{{ route('terms') }}" class="text-sm font-medium text-orange-500">Terms</a>
                <a href="{{ route('support') }}" class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Support</a>
            </nav>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
