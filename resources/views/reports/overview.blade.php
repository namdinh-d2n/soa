@extends('layouts.app')

@section('title', 'Tổng quan Thống kê - Dịch vụ Báo cáo')

@section('content')
<div class="space-y-8">

    <!-- Page Header & Filter Form -->
    <div class="sm:flex sm:items-center sm:justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-chart-line text-indigo-600 mr-3"></i>
                Báo cáo Thống kê Tổng hợp
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Thống kê các chỉ số kinh doanh: Hàng tồn, Hàng bán được, Doanh thu, Chi phí và Lợi nhuận.
            </p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ url('/') }}" class="mt-4 sm:mt-0 flex flex-wrap items-center gap-3">
            <div>
                <label for="start_date" class="sr-only">Từ ngày</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                    class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block px-3 py-2">
            </div>
            <span class="text-slate-400 text-xs">đến</span>
            <div>
                <label for="end_date" class="sr-only">Đến ngày</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                    class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block px-3 py-2">
            </div>
            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition shadow-sm">
                <i class="fa-solid fa-filter mr-1"></i> Lọc dữ liệu
            </button>
            @if(request('start_date') || request('end_date'))
                <a href="{{ url('/') }}" class="text-xs text-slate-500 hover:text-rose-600 font-medium ml-1">
                    <i class="fa-solid fa-xmark"></i> Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- 5 Key Metric Cards theo đúng yêu cầu đề bài -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

        <!-- 1. Số lượng hàng tồn -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hàng tồn kho</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ number_format($summary['total_inventory_quantity'] ?? 0) }}
                </div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="text-blue-600 font-semibold mr-1.5"><i class="fa-solid fa-warehouse"></i> Hiện còn trong kho</span>
                </div>
            </div>
        </div>

        <!-- 2. Hàng bán được -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Hàng bán được</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-cart-shopping"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ number_format($summary['total_sold_quantity'] ?? 0) }}
                </div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="text-emerald-600 font-semibold mr-1.5"><i class="fa-solid fa-check"></i> Đã xuất bán</span>
                </div>
            </div>
        </div>

        <!-- 3. Doanh thu -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh thu</span>
                <span class="w-8 h-8 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    ${{ number_format($summary['total_revenue'] ?? 0, 2) }}
                </div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="text-violet-600 font-semibold mr-1.5"><i class="fa-solid fa-receipt"></i> Tổng thu khách</span>
                </div>
            </div>
        </div>

        <!-- 4. Chi phí -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition duration-150">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Chi phí (Giá vốn)</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    ${{ number_format($summary['total_cost'] ?? 0, 2) }}
                </div>
                <div class="mt-1 flex items-center text-xs text-slate-500">
                    <span class="text-amber-600 font-semibold mr-1.5"><i class="fa-solid fa-tags"></i> Giá vốn hàng bán</span>
                </div>
            </div>
        </div>

        <!-- 5. Lợi nhuận -->
        <div class="bg-gradient-to-tr from-indigo-900 to-indigo-800 text-white p-5 rounded-2xl shadow-md shadow-indigo-100 relative overflow-hidden">
            <div class="flex items-center justify-between text-indigo-200">
                <span class="text-xs font-bold uppercase tracking-wider">Lợi nhuận ròng</span>
                <span class="w-8 h-8 rounded-lg bg-white/10 text-emerald-400 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-sack-dollar"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-white tracking-tight">
                    ${{ number_format($summary['total_profit'] ?? 0, 2) }}
                </div>
                <div class="mt-1 flex items-center text-xs text-indigo-200">
                    <span>Tỷ suất: <strong class="text-emerald-300 font-bold">{{ $summary['profit_margin_percent'] ?? 0 }}%</strong></span>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Bar Chart: Doanh thu, Chi phí, Lợi nhuận -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Biểu đồ Doanh thu - Chi phí - Lợi nhuận</h2>
                    <p class="text-xs text-slate-500">So sánh chỉ số tài chính theo từng sản phẩm chủ lực</p>
                </div>
                <span class="text-xs bg-slate-100 px-2.5 py-1 rounded-md text-slate-600 font-medium">Đơn vị: USD ($)</span>
            </div>
            <div class="h-72 w-full">
                <canvas id="financialChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Hàng tồn vs Hàng đã bán -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 mb-1">Tỷ lệ Hàng tồn vs Đã bán</h2>
                <p class="text-xs text-slate-500 mb-4">Phân bổ số lượng sản phẩm trong hệ thống</p>
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="inventoryDoughnut"></canvas>
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                <div class="p-2 rounded-lg bg-blue-50">
                    <span class="text-slate-500 block">Tồn kho</span>
                    <strong class="text-blue-700 text-sm">{{ number_format($summary['total_inventory_quantity'] ?? 0) }} chiếc</strong>
                </div>
                <div class="p-2 rounded-lg bg-emerald-50">
                    <span class="text-slate-500 block">Đã bán</span>
                    <strong class="text-emerald-700 text-sm">{{ number_format($summary['total_sold_quantity'] ?? 0) }} chiếc</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Top Selling Products -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Top Sản phẩm Bán chạy & Lợi nhuận cao nhất</h3>
                    <p class="text-xs text-slate-500">Thống kê hiệu quả kinh doanh của các sản phẩm hàng đầu</p>
                </div>
                <a href="{{ url('/products-report') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold inline-flex items-center">
                    Xem tất cả <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3">Sản phẩm</th>
                            <th class="px-4 py-3 text-center">Tồn kho</th>
                            <th class="px-4 py-3 text-center">Đã bán</th>
                            <th class="px-4 py-3 text-right">Doanh thu</th>
                            <th class="px-4 py-3 text-right">Chi phí</th>
                            <th class="px-5 py-3 text-right">Lợi nhuận</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($topProducts as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                                <span class="text-[11px] text-slate-400">ID: #{{ $item->product_id }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $item->stock_quantity <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $item->stock_quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-emerald-600">
                                {{ $item->sold_quantity }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-slate-900">
                                ${{ number_format($item->revenue, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-slate-500">
                                ${{ number_format($item->cost, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-indigo-600">
                                +${{ number_format($item->profit, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-slate-400">Chưa có dữ liệu báo cáo</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Overview Status -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm mb-1">Trạng thái Đơn hàng</h3>
                <p class="text-xs text-slate-500 mb-5">Thống kê phân loại theo tiến độ xử lý đơn</p>

                <div class="space-y-4">
                    <!-- Total Orders -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-list-check"></i>
                            </span>
                            <span class="text-xs font-semibold text-slate-700">Tổng số đơn hàng</span>
                        </div>
                        <span class="font-black text-sm text-slate-900">{{ $ordersMetrics['total'] ?? 0 }}</span>
                    </div>

                    <!-- Completed Orders -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/60 border border-emerald-100">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-circle-check"></i>
                            </span>
                            <span class="text-xs font-semibold text-emerald-800">Đơn đã hoàn thành</span>
                        </div>
                        <span class="font-black text-sm text-emerald-700">{{ $ordersMetrics['completed'] ?? 0 }}</span>
                    </div>

                    <!-- Pending Orders -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50/60 border border-amber-100">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-clock"></i>
                            </span>
                            <span class="text-xs font-semibold text-amber-800">Đơn chờ xử lý</span>
                        </div>
                        <span class="font-black text-sm text-amber-700">{{ $ordersMetrics['pending'] ?? 0 }}</span>
                    </div>

                    <!-- Cancelled Orders -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-rose-50/60 border border-rose-100">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </span>
                            <span class="text-xs font-semibold text-rose-800">Đơn đã hủy</span>
                        </div>
                        <span class="font-black text-sm text-rose-700">{{ $ordersMetrics['cancelled'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ url('/orders-report') }}" class="w-full text-center block bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold py-2.5 rounded-lg transition">
                    <i class="fa-solid fa-receipt mr-1"></i> Xem chi tiết danh sách đơn
                </a>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    // Chuẩn bị dữ liệu cho biểu đồ từ PHP
    const productNames = {!! json_encode($topProducts->pluck('product_name')) !!};
    const productRevenues = {!! json_encode($topProducts->pluck('revenue')) !!};
    const productCosts = {!! json_encode($topProducts->pluck('cost')) !!};
    const productProfits = {!! json_encode($topProducts->pluck('profit')) !!};

    const totalInventory = {{ (int) ($summary['total_inventory_quantity'] ?? 0) }};
    const totalSold = {{ (int) ($summary['total_sold_quantity'] ?? 0) }};

    // 1. Biểu đồ Cột: Doanh thu - Chi phí - Lợi nhuận
    const ctxBar = document.getElementById('financialChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: productNames,
            datasets: [
                {
                    label: 'Doanh thu',
                    data: productRevenues,
                    backgroundColor: 'rgba(99, 102, 241, 0.85)', // Indigo
                    borderRadius: 6,
                },
                {
                    label: 'Chi phí (Giá vốn)',
                    data: productCosts,
                    backgroundColor: 'rgba(245, 158, 11, 0.85)', // Amber
                    borderRadius: 6,
                },
                {
                    label: 'Lợi nhuận',
                    data: productProfits,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)', // Emerald
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) { return '$' + val; }
                    }
                }
            }
        }
    });

    // 2. Biểu đồ Doughnut: Hàng tồn vs Hàng đã bán
    const ctxDoughnut = document.getElementById('inventoryDoughnut').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Hàng tồn kho', 'Hàng bán được'],
            datasets: [{
                data: [totalInventory, totalSold],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.85)',  // Blue (Tồn)
                    'rgba(16, 185, 129, 0.85)'   // Emerald (Bán)
                ],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '600' } }
                }
            },
            cutout: '70%'
        }
    });
</script>
@endpush
