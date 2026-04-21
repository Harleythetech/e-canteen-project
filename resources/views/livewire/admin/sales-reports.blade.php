<flux:main>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">Sales Reports</flux:heading>
    </div>

    {{-- Period Filter --}}
    <div class="mb-6 flex flex-wrap items-end gap-3">
        <div class="flex gap-2">
            @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'custom' => 'Custom'] as $value => $label)
                <button wire:click="$set('period', '{{ $value }}')" @class([
                    'rounded-full px-3 py-1.5 text-xs font-medium transition',
                    'bg-orange-500 text-white' => $period === $value,
                    'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300' => $period !== $value,
                ])>{{ $label }}</button>
            @endforeach
        </div>

        @if ($period === 'custom')
            <div class="flex items-end gap-2">
                <flux:input wire:model.live="dateFrom" type="date" label="From" />
                <flux:input wire:model.live="dateTo" type="date" label="To" />
            </div>
        @endif
    </div>

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-orange-600">₱{{ number_format($totalRevenue, 0) }}</p>
            <p class="text-xs text-zinc-500">Total Revenue</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalOrders }}</p>
            <p class="text-xs text-zinc-500">Total Orders</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-green-600">{{ $completedOrders }}</p>
            <p class="text-xs text-zinc-500">Completed</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-red-500">{{ $cancelledOrders }}</p>
            <p class="text-xs text-zinc-500">Cancelled</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">₱{{ number_format($averageOrderValue, 0) }}
            </p>
            <p class="text-xs text-zinc-500">Avg Order Value</p>
        </div>
    </div>

    {{-- Revenue Trend Bar Chart + Order Status Doughnut --}}
    <div class="mb-6 grid gap-6 lg:grid-cols-3" wire:key="charts-{{ $period }}-{{ $dateFrom }}-{{ $dateTo }}">
        {{-- Daily Revenue Bar Chart --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Daily Revenue</h3>
            <div class="relative h-72">
                @if ($dailySales->isNotEmpty())
                    <canvas x-data="{
                                chart: null,
                                init() {
                                    const isDark = document.documentElement.classList.contains('dark');
                                    const gridColor = isDark ? 'rgba(113,113,122,0.3)' : 'rgba(228,228,231,0.8)';
                                    const textColor = isDark ? '#a1a1aa' : '#71717a';
                                    this.chart = new Chart(this.$el, {
                                        type: 'bar',
                                        data: {
                                            labels: @js($dailySales->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->values()),
                                            datasets: [{
                                                label: 'Revenue (₱)',
                                                data: @js($dailySales->pluck('revenue')->values()),
                                                backgroundColor: 'rgba(234,88,12,0.8)',
                                                borderRadius: 6,
                                                yAxisID: 'y',
                                            }, {
                                                label: 'Orders',
                                                data: @js($dailySales->pluck('orders')->values()),
                                                type: 'line',
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
                @else
                    <div class="flex h-full items-center justify-center text-sm text-zinc-400">No revenue data for this
                        period.</div>
                @endif
            </div>
        </div>

        {{-- Order Status Doughnut --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <h3 class="mb-3 font-semibold text-zinc-900 dark:text-zinc-100">Orders by Status</h3>
            @if (!empty($statusBreakdown))
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
                <div class="relative mx-auto max-w-[220px]">
                    <canvas x-data="{
                                chart: null,
                                init() {
                                    const isDark = document.documentElement.classList.contains('dark');
                                    this.chart = new Chart(this.$el, {
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
                                                    labels: { color: isDark ? '#a1a1aa' : '#71717a', padding: 10 }
                                                }
                                            },
                                            cutout: '60%',
                                        }
                                    });
                                }
                            }"></canvas>
                </div>
                {{-- Status summary --}}
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    @foreach ($statusBreakdown as $status => $count)
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                            {{ match ($status) {
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                            'paid' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                            'preparing' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                            'ready' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                            'completed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                            default => 'bg-zinc-100 text-zinc-800',
                        } }}">{{ $count }} {{ ucfirst($status) }}</span>
                    @endforeach
                </div>
            @else
                <div class="flex h-48 items-center justify-center text-sm text-zinc-400">No orders in this period.</div>
            @endif
        </div>
    </div>

    {{-- Top Products Bar Chart + Tables --}}
    <div class="grid gap-6 lg:grid-cols-2" wire:key="tables-{{ $period }}-{{ $dateFrom }}-{{ $dateTo }}">
        {{-- Top Products Horizontal Bar --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Top Selling Products</h3>
            </div>
            <div class="p-4">
                @if ($topProducts->isNotEmpty())
                    <div class="relative h-64">
                        <canvas x-data="{
                                    chart: null,
                                    init() {
                                        const isDark = document.documentElement.classList.contains('dark');
                                        const gridColor = isDark ? 'rgba(113,113,122,0.3)' : 'rgba(228,228,231,0.8)';
                                        const textColor = isDark ? '#a1a1aa' : '#71717a';
                                        this.chart = new Chart(this.$el, {
                                            type: 'bar',
                                            data: {
                                                labels: @js($topProducts->pluck('product_name')->values()),
                                                datasets: [{
                                                    label: 'Units Sold',
                                                    data: @js($topProducts->pluck('total_sold')->values()),
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
                                                    x: { ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor }, title: { display: true, text: 'Units Sold', color: textColor } },
                                                    y: { ticks: { color: textColor }, grid: { display: false } },
                                                }
                                            }
                                        });
                                    }
                                }"></canvas>
                    </div>
                @else
                    <div class="flex h-48 items-center justify-center text-sm text-zinc-400">No product data for this
                        period.</div>
                @endif
            </div>
        </div>

        {{-- Product Revenue Pie --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Revenue by Product</h3>
            </div>
            <div class="p-4">
                @if ($topProducts->isNotEmpty())
                    @php
                        $prodColors = ['#ea580c', '#3b82f6', '#22c55e', '#eab308', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#f43f5e'];
                    @endphp
                    <div class="relative mx-auto max-w-[260px]">
                        <canvas x-data="{
                                    chart: null,
                                    init() {
                                        const isDark = document.documentElement.classList.contains('dark');
                                        this.chart = new Chart(this.$el, {
                                            type: 'pie',
                                            data: {
                                                labels: @js($topProducts->pluck('product_name')->values()),
                                                datasets: [{
                                                    data: @js($topProducts->pluck('total_revenue')->values()),
                                                    backgroundColor: @js(array_slice($prodColors, 0, min($topProducts->count(), 10))),
                                                    borderWidth: 0,
                                                }]
                                            },
                                            options: {
                                                responsive: true,
                                                plugins: {
                                                    legend: {
                                                        position: 'bottom',
                                                        labels: { color: isDark ? '#a1a1aa' : '#71717a', padding: 8, font: { size: 11 } }
                                                    },
                                                    tooltip: {
                                                        callbacks: {
                                                            label: (ctx) => ctx.label + ': ₱' + Number(ctx.raw).toLocaleString()
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }"></canvas>
                    </div>
                @else
                    <div class="flex h-48 items-center justify-center text-sm text-zinc-400">No product data for this
                        period.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detailed Tables --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Daily Sales Table --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Daily Breakdown</h3>
            </div>
            <div class="max-h-80 overflow-y-auto overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-zinc-800">
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="px-4 py-2 font-medium text-zinc-500">Date</th>
                            <th class="px-4 py-2 text-end font-medium text-zinc-500">Orders</th>
                            <th class="px-4 py-2 text-end font-medium text-zinc-500">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                        @forelse ($dailySales as $day)
                            <tr>
                                <td class="px-4 py-2 text-zinc-900 dark:text-zinc-100">
                                    {{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-2 text-end text-zinc-600 dark:text-zinc-400">{{ $day->orders }}</td>
                                <td class="px-4 py-2 text-end font-semibold text-zinc-900 dark:text-zinc-100">
                                    ₱{{ number_format($day->revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-zinc-500">No sales data for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Products Table --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Product Details</h3>
            </div>
            <div class="max-h-80 overflow-y-auto overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-zinc-800">
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <th class="px-4 py-2 font-medium text-zinc-500">#</th>
                            <th class="px-4 py-2 font-medium text-zinc-500">Product</th>
                            <th class="px-4 py-2 text-end font-medium text-zinc-500">Sold</th>
                            <th class="px-4 py-2 text-end font-medium text-zinc-500">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                        @forelse ($topProducts as $i => $product)
                            <tr>
                                <td class="px-4 py-2 text-zinc-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2 font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $product->product_name }}
                                </td>
                                <td class="px-4 py-2 text-end text-zinc-600 dark:text-zinc-400">{{ $product->total_sold }}
                                </td>
                                <td class="px-4 py-2 text-end font-semibold text-zinc-900 dark:text-zinc-100">
                                    ₱{{ number_format($product->total_revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-zinc-500">No product data for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</flux:main>