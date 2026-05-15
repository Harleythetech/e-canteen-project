<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Support — E-Canteen</title>
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
        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">Support</h1>
        <p class="mt-3 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
            Need help with your order or account? Here are the quickest ways to get assistance.
        </p>

        <div class="mt-10 space-y-6">

            {{-- Contact card --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Canteen Counter</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">For urgent order issues, speak directly with canteen staff at the pickup counter during operating hours.</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Email Support</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Send us a message and we'll get back to you within one business day.</p>
                <a href="mailto:securo.support.ecanteen@gmail.com" class="mt-3 inline-block text-sm font-medium text-orange-500 hover:underline">securo.support.ecanteen@gmail.com</a>
            </div>

            {{-- FAQ --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <h2 class="mb-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">Frequently Asked Questions</h2>
                <div class="space-y-5 text-sm text-zinc-600 dark:text-zinc-400">

                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">My payment went through but my order isn't showing.</p>
                        <p class="mt-1">Wait a few minutes and refresh the page. If the issue persists, contact us with your payment reference number.</p>
                    </div>

                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">Can I cancel my order?</p>
                        <p class="mt-1">Orders can be cancelled before they enter preparation. Go to My Orders, open the order, and use the Cancel button if it's still available.</p>
                    </div>

                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">I lost my QR code. What do I do?</p>
                        <p class="mt-1">Open the order in My Orders — the QR code is always available there. Show it to canteen staff to claim your order.</p>
                    </div>

                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">How long does a refund take?</p>
                        <p class="mt-1">Refunds are processed through PayMongo and typically reflect within 3–7 business days depending on your payment method.</p>
                    </div>

                    <div>
                        <p class="font-medium text-zinc-800 dark:text-zinc-200">I forgot my password.</p>
                        <p class="mt-1">Use the <a href="{{ route('password.request') }}" class="text-orange-500 hover:underline">Forgot Password</a> link on the sign-in page to reset it via email.</p>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <footer class="border-t border-zinc-100 bg-white py-8 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row">
            <p class="text-xs text-zinc-400 dark:text-zinc-500">&copy; {{ date('Y') }} E-Canteen. All rights reserved.</p>
            <nav class="flex gap-6">
                <a href="{{ route('privacy') }}" class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Privacy</a>
                <a href="{{ route('terms') }}" class="text-sm text-zinc-400 hover:text-zinc-700 dark:text-zinc-500 dark:hover:text-zinc-300">Terms</a>
                <a href="{{ route('support') }}" class="text-sm font-medium text-orange-500">Support</a>
            </nav>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
