<flux:main>
    <flux:heading size="xl" class="mb-6">Dashboard Overview</flux:heading>

    {{-- Low Stock Alert --}}
    @if ($lowStockProducts->isNotEmpty())
        <div x-data="{ show: true }" x-show="show" x-transition.opacity class="mb-6 rounded-xl p-4 shadow-sm" style="background-color: #D0342C;">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <flux:icon.exclamation-triangle class="size-5 text-white" />
                    <span class="text-sm font-bold text-white">Low Stock Alert</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white">
                        {{ $lowStockProducts->count() }} {{ Str::plural('item', $lowStockProducts->count()) }} need restocking
                    </span>
                    <button @click="show = false" class="rounded-md p-1 text-white/70 transition hover:bg-white/20 hover:text-white" aria-label="Dismiss">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($lowStockProducts as $product)
                    <div class="flex items-center justify-between rounded-lg bg-white/10 px-3 py-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white">{{ $product->name }}</p>
                            <p class="text-xs text-white/70">{{ $product->category->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="ml-3 shrink-0 text-right">
                            @if ($product->stock === 0)
                                <span class="rounded-md bg-white px-2 py-0.5 text-xs font-bold" style="color: #D0342C;">OUT OF STOCK</span>
                            @else
                                <span class="text-sm font-bold text-white">{{ $product->stock }}</span>
                                <p class="text-xs text-white/70">remaining</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Stats grid --}}
    <div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/30">
                    <span class="text-lg font-bold">₱</span>
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Today's Revenue</p>
                    <p class="text-xl font-bold text-orange-600">₱{{ number_format($stats['today_revenue'], 0) }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30">
                    <flux:icon.shopping-bag class="size-5" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Today's Orders</p>
                    <p class="text-xl font-bold text-blue-600">{{ $stats['today_orders'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30">
                    <flux:icon.users class="size-5" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Students</p>
                    <p class="text-xl font-bold text-green-600">{{ $stats['total_students'] }}</p>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30">
                    <flux:icon.exclamation-triangle class="size-5" />
                </div>
                <div>
                    <p class="text-xs text-zinc-500">Low Stock Items</p>
                    <p class="text-xl font-bold text-amber-600">{{ $stats['low_stock'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- 7-Day Revenue Trend (line chart) --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Revenue — Last 7 Days</h3>
            <div class="relative h-64">
                <canvas id="revenueTrendChart" x-data="{
                        chart: null,
                        init() {
                            const ctx = document.getElementById('revenueTrendChart');
                            const isDark = document.documentElement.classList.contains('dark');
                            const gridColor = isDark ? 'rgba(113,113,122,0.3)' : 'rgba(228,228,231,0.8)';
                            const textColor = isDark ? '#a1a1aa' : '#71717a';
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: @js($trendLabels),
                                    datasets: [{
                                        label: 'Revenue (₱)',
                                        data: @js($trendRevenue),
                                        borderColor: '#ea580c',
                                        backgroundColor: 'rgba(234,88,12,0.1)',
                                        fill: true,
                                        tension: 0.3,
                                        yAxisID: 'y',
                                    }, {
                                        label: 'Orders',
                                        data: @js($trendOrders),
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

        {{-- Order Status Doughnut --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Order Status</h3>
            <div class="relative mx-auto max-w-[220px]">
                @php
                    $statusChartColors = [
                        'pending' => '#eab308',
                        'paid' => '#3b82f6',
                        'preparing' => '#f97316',
                        'ready' => '#22c55e',
                        'completed' => '#6b7280',
                        'cancelled' => '#ef4444',
                    ];
                @endphp
                <canvas id="statusChart" x-data="{
                        chart: null,
                        init() {
                            const ctx = document.getElementById('statusChart');
                            const isDark = document.documentElement.classList.contains('dark');
                            this.chart = new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: @js(array_map('ucfirst', array_keys($statusBreakdown))),
                                    datasets: [{
                                        data: @js(array_values($statusBreakdown)),
                                        backgroundColor: @js(array_values(array_intersect_key($statusChartColors, $statusBreakdown))),
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
            {{-- Quick totals --}}
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500">Total Revenue</span>
                    <span
                        class="font-bold text-zinc-900 dark:text-zinc-100">₱{{ number_format($stats['total_revenue'], 0) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">Total Orders</span>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ $stats['total_orders'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">Active Now</span>
                    <span class="font-bold text-orange-600">{{ $stats['active_orders'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Revenue Pie + Recent Orders Table --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Recent orders --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading>Recent Orders</flux:heading>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-4 py-2 font-medium text-zinc-500">Order</th>
                                <th class="px-4 py-2 font-medium text-zinc-500">Customer</th>
                                <th class="px-4 py-2 font-medium text-zinc-500">Status</th>
                                <th class="px-4 py-2 text-end font-medium text-zinc-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $order->order_number }}
                                    </td>
                                    <td class="px-4 py-2 text-zinc-600 dark:text-zinc-400">{{ $order->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $color = match ($order->status) {
                                                'pending' => 'yellow', 'paid' => 'blue', 'preparing' => 'orange',
                                                'ready' => 'green', 'completed' => 'zinc', 'cancelled' => 'red', default => 'zinc',
                                            };
                                        @endphp
                                        <flux:badge :color="$color" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                                    </td>
                                    <td class="px-4 py-2 text-end font-semibold text-zinc-900 dark:text-zinc-100">
                                        ₱{{ number_format($order->total, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Category Revenue + Low Stock --}}
        <div class="space-y-6">
            {{-- Category Revenue Pie --}}
            @if ($categorySales->isNotEmpty())
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Revenue by Category</h3>
                    <div class="relative mx-auto max-w-[220px]">
                        @php
                            $pieColors = ['#ea580c', '#3b82f6', '#22c55e', '#eab308', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
                        @endphp
                        <canvas id="categoryChart" x-data="{
                                        chart: null,
                                        init() {
                                            const ctx = document.getElementById('categoryChart');
                                            const isDark = document.documentElement.classList.contains('dark');
                                            this.chart = new Chart(ctx, {
                                                type: 'pie',
                                                data: {
                                                    labels: @js($categorySales->pluck('category')->values()),
                                                    datasets: [{
                                                        data: @js($categorySales->pluck('revenue')->values()),
                                                        backgroundColor: @js(array_slice($pieColors, 0, $categorySales->count())),
                                                        borderWidth: 0,
                                                    }]
                                                },
                                                options: {
                                                    responsive: true,
                                                    plugins: {
                                                        legend: {
                                                            position: 'bottom',
                                                            labels: { color: isDark ? '#a1a1aa' : '#71717a', padding: 10, font: { size: 11 } }
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    }"></canvas>
                    </div>
                </div>
            @endif

        </div>
    </div>
</flux:main>