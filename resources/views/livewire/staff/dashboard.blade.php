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

                <div wire:poll.3s class="space-y-3">
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
                                </div>
                                <div class="shrink-0">
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
</flux:main>