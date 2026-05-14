<flux:main>
    {{-- Stats strip --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Today's Orders</p>
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $todayStats['total_orders'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Revenue Today</p>
            <p class="text-2xl font-bold text-orange-600">₱{{ number_format($todayStats['total_revenue'], 0) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Active Orders</p>
            <p class="text-2xl font-bold text-blue-600">{{ $todayStats['pending_orders'] }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-sm text-zinc-500">Completed Today</p>
            <p class="text-2xl font-bold text-green-600">{{ $todayStats['completed_today'] }}</p>
        </div>
    </div>

    @if ($tab === 'stats')
        {{-- Product Statistics --}}
        <flux:heading size="lg" class="mb-4">Product Statistics — Today</flux:heading>

        {{-- Hourly Sales + Stock Distribution --}}
        <div class="mb-6 grid gap-6 lg:grid-cols-3">
            {{-- Hourly Sales Line Chart --}}
            <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Hourly Sales Today</h3>
                <div class="relative h-64">
                    <canvas id="hourlySalesChart" x-data="{
                                                chart: null,
                                                init() {
                                                    const ctx = document.getElementById('hourlySalesChart');
                                                    const isDark = document.documentElement.classList.contains('dark');
                                                    const gridColor = isDark ? 'rgba(113,113,122,0.3)' : 'rgba(228,228,231,0.8)';
                                                    const textColor = isDark ? '#a1a1aa' : '#71717a';
                                                    this.chart = new Chart(ctx, {
                                                        type: 'line',
                                                        data: {
                                                            labels: @js($hourlyLabels),
                                                            datasets: [{
                                                                label: 'Revenue (₱)',
                                                                data: @js($hourlyRevenue),
                                                                borderColor: '#ea580c',
                                                                backgroundColor: 'rgba(234,88,12,0.1)',
                                                                fill: true,
                                                                tension: 0.3,
                                                                yAxisID: 'y',
                                                            }, {
                                                                label: 'Orders',
                                                                data: @js($hourlyOrders),
                                                                borderColor: '#3b82f6',
                                                                backgroundColor: 'rgba(59,130,246,0.1)',
                                                                fill: false,
                                                                tension: 0.3,
                                                                yAxisID: 'y1',
                                                            }]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            interaction: { mode: 'index', intersect: false },
                                                            plugins: { legend: { labels: { color: textColor } } },
                                                            scales: {
                                                                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                                                                y: { position: 'left', ticks: { color: textColor }, grid: { color: gridColor }, title: { display: true, text: 'Revenue (₱)', color: textColor } },
                                                                y1: { position: 'right', ticks: { color: textColor, stepSize: 1 }, grid: { drawOnChartArea: false }, title: { display: true, text: 'Orders', color: textColor } },
                                                            }
                                                        }
                                                    });
                                                }
                                            }"></canvas>
                </div>
            </div>

            {{-- Stock Distribution Doughnut --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Stock Health</h3>
                <div class="relative mx-auto max-w-[220px]">
                    <canvas id="stockChart" x-data="{
                                                chart: null,
                                                init() {
                                                    const ctx = document.getElementById('stockChart');
                                                    const isDark = document.documentElement.classList.contains('dark');
                                                    this.chart = new Chart(ctx, {
                                                        type: 'doughnut',
                                                        data: {
                                                            labels: @js(array_keys($stockDistribution)),
                                                            datasets: [{
                                                                data: @js(array_values($stockDistribution)),
                                                                backgroundColor: ['#ef4444', '#f59e0b', '#22c55e'],
                                                                borderWidth: 0,
                                                            }]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            plugins: {
                                                                legend: {
                                                                    position: 'bottom',
                                                                    labels: { color: isDark ? '#a1a1aa' : '#71717a', padding: 12 }
                                                                }
                                                            },
                                                            cutout: '60%',
                                                        }
                                                    });
                                                }
                                            }"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Products Bar Chart + Table --}}
        @if ($topProducts->isNotEmpty())
            <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Top Products Sold Today</h3>
                <div class="relative h-64">
                    <canvas id="topProductsChart" x-data="{
                                                                    chart: null,
                                                                    init() {
                                                                        const ctx = document.getElementById('topProductsChart');
                                                                        const isDark = document.documentElement.classList.contains('dark');
                                                                        const gridColor = isDark ? 'rgba(113,113,122,0.3)' : 'rgba(228,228,231,0.8)';
                                                                        const textColor = isDark ? '#a1a1aa' : '#71717a';
                                                                        this.chart = new Chart(ctx, {
                                                                            type: 'bar',
                                                                            data: {
                                                                                labels: @js($topProducts->pluck('name')->values()),
                                                                                datasets: [{
                                                                                    label: 'Units Sold',
                                                                                    data: @js($topProducts->pluck('today_sold')->values()),
                                                                                    backgroundColor: 'rgba(234,88,12,0.8)',
                                                                                    borderRadius: 6,
                                                                                }]
                                                                            },
                                                                            options: {
                                                                                indexAxis: 'y',
                                                                                responsive: true,
                                                                                maintainAspectRatio: false,
                                                                                plugins: { legend: { display: false } },
                                                                                scales: {
                                                                                    x: { ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
                                                                                    y: { ticks: { color: textColor }, grid: { display: false } },
                                                                                }
                                                                            }
                                                                        });
                                                                    }
                                                                }"></canvas>
                </div>
            </div>

            {{-- Table kept below chart for details --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-3 font-medium text-zinc-500">#</th>
                                <th class="px-4 py-3 font-medium text-zinc-500">Product</th>
                                <th class="px-4 py-3 text-end font-medium text-zinc-500">Sold Today</th>
                                <th class="px-4 py-3 text-end font-medium text-zinc-500">Stock Left</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach ($topProducts as $i => $product)
                                <tr>
                                    <td class="px-4 py-3 text-zinc-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-end font-semibold text-orange-600">{{ $product->today_sold }}</td>
                                    <td class="px-4 py-3 text-end">
                                        <span @class([
                                            'font-medium',
                                            'text-red-500' => $product->stock <= 5,
                                            'text-amber-500' => $product->stock > 5 && $product->stock <= 15,
                                            'text-green-600' => $product->stock > 15,
                                        ])>{{ $product->stock }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-700 dark:bg-zinc-800">
                <flux:icon.chart-bar class="mx-auto mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading>No sales data yet</flux:heading>
                <flux:subheading>Product statistics will appear here once orders come in.</flux:subheading>
            </div>
        @endif
    @else
        {{-- Live Orders + QR Scanner side by side --}}
        {{-- Alpine-powered order detail modal — lives outside Livewire's morph scope --}}
        <div
            x-data="{
                open: false,
                order: null,
                show(data) { this.order = data; this.open = true; },
                close() { this.open = false; this.order = null; },
                badgeClass(status) {
                    return {
                        'pending':   'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                        'paid':      'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'preparing': 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                        'ready':     'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                        'completed': 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                        'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                    }[status] ?? 'bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300';
                }
            }"
            x-on:keydown.escape.window="close()"
        >
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Live Orders (left, 2/3 width) --}}
            <div class="lg:col-span-2">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <flux:heading size="lg">Live Orders</flux:heading>
                    <div class="flex flex-wrap gap-2">
                        @foreach (['active' => 'Active', 'pending' => 'Pending', 'paid' => 'Paid', 'preparing' => 'Preparing', 'ready' => 'Ready'] as $key => $label)
                            <button wire:click="$set('statusFilter', '{{ $key }}')" @class([
                                'rounded-full px-3 py-1.5 text-xs font-medium transition focus-visible:outline-2 focus-visible:outline-orange-500',
                                'bg-orange-500 text-white' => $statusFilter === $key,
                                'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300' => $statusFilter !== $key,
                            ])>{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div
                    x-data="{}"
                    x-init="setInterval(() => $wire.refreshOrders(), 5000)"
                    class="space-y-3"
                >
                    @forelse ($orders as $order)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="font-bold text-zinc-900 dark:text-zinc-100">{{ $order->order_number }}</span>
                                        @php
                                            $badgeColor = match ($order->status) {
                                                'pending' => 'yellow',
                                                'paid' => 'blue',
                                                'preparing' => 'orange',
                                                'ready' => 'green',
                                                default => 'zinc',
                                            };
                                        @endphp
                                        <flux:badge :color="$badgeColor" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                                    </div>
                                    <p class="mt-1 text-sm text-zinc-500">
                                        {{ $order->user->name }} · {{ $order->items->pluck('product_name')->join(', ') }}
                                    </p>
                                    <p class="text-xs text-zinc-400">
                                        Pickup: {{ $order->pickup_time ?? 'N/A' }} · ₱{{ number_format($order->total, 0) }}
                                    </p>
                                    @if ($order->special_instructions)
                                        <p class="mt-1 flex items-center gap-1 text-xs font-medium text-amber-600 dark:text-amber-400">
                                            <flux:icon.chat-bubble-left-ellipsis class="size-3.5 shrink-0" />
                                            {{ Str::limit($order->special_instructions, 60) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <button
                                        @click="show(@js([
                                            'id'                   => $order->id,
                                            'order_number'         => $order->order_number,
                                            'created_at'           => $order->created_at->format('M d, Y · g:i A'),
                                            'status'               => $order->status,
                                            'customer_name'        => $order->user->name,
                                            'customer_email'       => $order->user->email,
                                            'pickup_time'          => $order->pickup_time ?? 'N/A',
                                            'payment_method'       => $order->payment_method ? ucfirst($order->payment_method) : 'Payment pending',
                                            'special_instructions' => $order->special_instructions,
                                            'total'                => '₱' . number_format($order->total, 0),
                                            'items'                => $order->items->map(fn($i) => [
                                                'name'      => $i->product_name,
                                                'unit'      => '₱' . number_format($i->unit_price, 0),
                                                'qty'       => $i->quantity,
                                                'subtotal'  => '₱' . number_format($i->unit_price * $i->quantity, 0),
                                            ])->values()->all(),
                                        ]))"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-xs font-medium text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-600"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        Details
                                    </button>
                                    @if ($order->status === 'pending')
                                        <flux:button wire:click="advanceOrder({{ $order->id }})" size="sm" variant="primary"
                                            icon="banknotes">
                                            Mark Paid
                                        </flux:button>
                                    @elseif ($order->status === 'paid')
                                        <flux:button wire:click="advanceOrder({{ $order->id }})" size="sm" variant="primary"
                                            icon="fire">
                                            Start Preparing
                                        </flux:button>
                                    @elseif ($order->status === 'preparing')
                                        <flux:button wire:click="advanceOrder({{ $order->id }})" size="sm" variant="primary"
                                            icon="check">
                                            Mark Ready
                                        </flux:button>
                                    @elseif ($order->status === 'ready')
                                        <flux:button wire:click="advanceOrder({{ $order->id }})" size="sm" variant="filled"
                                            icon="check-circle">
                                            Complete
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white py-12 dark:border-zinc-700 dark:bg-zinc-800">
                            <flux:icon.inbox class="mb-3 size-12 text-zinc-300 dark:text-zinc-600" />
                            <flux:heading>No orders</flux:heading>
                            <flux:subheading>Waiting for incoming orders...</flux:subheading>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- QR Scanner (right, 1/3 width) --}}
            <div>
                <flux:heading size="lg" class="mb-4">QR Scanner</flux:heading>

                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800" x-data="{
                                            scanner: null,
                                            scanning: false,
                                            error: null,
                                            lastScanned: null,
                                            cooldown: false,
                                            scanResult: null,
                                            scanResultType: null,
                                            async startScanner() {
                                                if (this.scanning) return;
                                                this.error = null;

                                                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                                                    this.error = 'Camera not available. Make sure you are using HTTPS.';
                                                    return;
                                                }

                                                try {
                                                    this.scanner = new window.Html5Qrcode('qr-reader-video');
                                                    this.scanning = true;
                                                    await this.scanner.start(
                                                        { facingMode: 'environment' },
                                                        { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 16 / 9 },
                                                        (code) => {
                                                            if (this.cooldown) return;
                                                            if (this.lastScanned === code) return;

                                                            this.cooldown = true;
                                                            this.lastScanned = code;
                                                            $wire.set('scannedCode', code);
                                                            $wire.processQrCode();

                                                            setTimeout(() => {
                                                                this.cooldown = false;
                                                                this.lastScanned = null;
                                                            }, 3000);
                                                        },
                                                    );
                                                } catch (e) {
                                                    this.scanning = false;
                                                    if (e.toString().includes('NotAllowedError') || e.toString().includes('Permission')) {
                                                        this.error = 'Camera permission denied. Please allow camera access in your browser settings and try again.';
                                                    } else {
                                                        this.error = 'Could not start camera: ' + (e.message || e);
                                                    }
                                                }
                                            },
                                            stopScanner() {
                                                if (this.scanner && this.scanning) {
                                                    this.scanner.stop().catch(() => {});
                                                    this.scanning = false;
                                                }
                                            }
                                        }" x-on:scanner-result.window="
                                            scanResultType = $event.detail.type;
                                            scanResult = $event.detail.message;
                                            setTimeout(() => { scanResult = null; scanResultType = null; }, 4000);
                                        " x-on:livewire:navigated.window="stopScanner()">

                    {{-- Scan result messages --}}
                    <template x-if="scanResult && scanResultType === 'success'">
                        <div
                            class="mb-4 flex items-center gap-2 rounded-lg bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/20 dark:text-green-400">
                            <flux:icon.check-circle class="size-5 shrink-0" />
                            <span x-text="scanResult"></span>
                        </div>
                    </template>
                    <template x-if="scanResult && scanResultType === 'error'">
                        <div
                            class="mb-4 flex items-center gap-2 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
                            <flux:icon.x-circle class="size-5 shrink-0" />
                            <span x-text="scanResult"></span>
                        </div>
                    </template>
                    <template x-if="scanResult && scanResultType === 'info'">
                        <div
                            class="mb-4 flex items-center gap-2 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                            <flux:icon.information-circle class="size-5 shrink-0" />
                            <span x-text="scanResult"></span>
                        </div>
                    </template>

                    {{-- Error message --}}
                    <template x-if="error">
                        <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
                            x-text="error"></div>
                    </template>

                    {{-- Camera scanner area --}}
                    <div class="mb-4" wire:ignore>
                        <div id="qr-reader-video"
                            class="aspect-video overflow-hidden rounded-lg bg-zinc-100 dark:bg-zinc-700 [&_video]:!h-full [&_video]:!w-full [&_video]:!object-cover"
                            x-show="scanning" x-cloak></div>
                        <div x-show="!scanning"
                            class="flex aspect-video items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                            <button type="button" @click="startScanner()"
                                class="flex flex-col items-center gap-2 text-zinc-500 hover:text-orange-500 transition">
                                <flux:icon.camera class="size-12" />
                                <span class="text-sm font-medium">Tap to scan</span>
                            </button>
                        </div>
                    </div>

                    {{-- Manual entry fallback --}}
                    <form wire:submit="processQrCode" class="flex gap-2">
                        <flux:input wire:model="scannedCode" placeholder="Order number..." class="flex-1" />
                        <flux:button type="submit" variant="primary" icon="magnifying-glass">
                            Look up
                        </flux:button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Order Detail Modal — inside x-data scope, fixed to viewport --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        x-cloak
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60" @click="close()"></div>

        {{-- Panel --}}
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative z-10 flex w-full max-w-lg flex-col rounded-2xl bg-white shadow-2xl dark:bg-zinc-900"
            style="max-height: 90vh;"
        >
            <template x-if="order">
                <div class="flex min-h-0 flex-col" style="max-height: 90vh;">
                    {{-- Sticky Header --}}
                    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <div>
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100" x-text="order.order_number"></h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400" x-text="order.created_at"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="badgeClass(order.status)" x-text="order.status.charAt(0).toUpperCase() + order.status.slice(1)"></span>
                            <button @click="close()" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Scrollable Body --}}
                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-4">
                        {{-- Customer & Pickup --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Customer</p>
                                <p class="font-semibold text-zinc-900 dark:text-zinc-100" x-text="order.customer_name"></p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="order.customer_email"></p>
                            </div>
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <p class="mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">Pickup Time</p>
                                <p class="font-semibold text-zinc-900 dark:text-zinc-100" x-text="order.pickup_time"></p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="order.payment_method"></p>
                            </div>
                        </div>

                        {{-- Special Instructions --}}
                        <template x-if="order.special_instructions">
                            <div class="rounded-lg border border-amber-300 bg-amber-500/10 p-3 dark:border-amber-600/50">
                                <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">⚠ Special Instructions</p>
                                <p class="text-sm text-zinc-900 dark:text-zinc-100" x-text="order.special_instructions"></p>
                            </div>
                        </template>
                        <template x-if="!order.special_instructions">
                            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <p class="text-sm text-zinc-400 dark:text-zinc-500">No special instructions</p>
                            </div>
                        </template>

                        {{-- Order Items --}}
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="border-b border-zinc-200 px-3 py-2 dark:border-zinc-700">
                                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Order Items</p>
                            </div>
                            <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                                <template x-for="item in order.items" :key="item.name">
                                    <div class="flex items-center justify-between px-3 py-2.5">
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100" x-text="item.name"></p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400" x-text="item.unit + ' × ' + item.qty"></p>
                                        </div>
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100" x-text="item.subtotal"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center justify-between border-t border-zinc-200 px-3 py-2.5 dark:border-zinc-700">
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">Total</span>
                                <span class="font-bold text-orange-600" x-text="order.total"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Sticky Footer --}}
                    <div class="flex shrink-0 justify-between gap-2 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <button @click="close()" class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                            Close
                        </button>
                        <template x-if="order.status === 'pending'">
                            <button :wire:click="'advanceOrder(' + order.id + ')'" @click="close()" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                                Mark Paid
                            </button>
                        </template>
                        <template x-if="order.status === 'paid'">
                            <button :wire:click="'advanceOrder(' + order.id + ')'" @click="close()" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                                Start Preparing
                            </button>
                        </template>
                        <template x-if="order.status === 'preparing'">
                            <button :wire:click="'advanceOrder(' + order.id + ')'" @click="close()" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                                Mark Ready
                            </button>
                        </template>
                        <template x-if="order.status === 'ready'">
                            <button :wire:click="'advanceOrder(' + order.id + ')'" @click="close()" class="inline-flex items-center gap-2 rounded-lg bg-zinc-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200">
                                Complete
                            </button>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
        </div>{{-- end Alpine order modal wrapper --}}
</flux:main>