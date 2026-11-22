@extends('layouts.app')

@section('title', 'Thống kê Hàng tồn kho - Dịch vụ Báo cáo')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center">
            <i class="fa-solid fa-warehouse text-indigo-600 mr-3"></i>
            Báo cáo Quản lý & Thống kê Hàng tồn kho
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Theo dõi tổng số lượng hàng tồn, giá trị định giá vốn, giá trị bán lẻ dự kiến và cảnh báo hàng sắp hết.
        </p>
    </div>

    <!-- Inventory Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total In-stock -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                <span>Tổng Hàng Tồn Kho</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-slate-900">
                {{ number_format($inventoryData['total_inventory_items'] ?? 0) }}
                <span class="text-xs font-normal text-slate-500">chiếc</span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Đang lưu giữ trong kho hàng</p>
        </div>

        <!-- Valuation Cost -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                <span>Giá trị Tồn (Theo Giá Vốn)</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-slate-900">
                ${{ number_format($inventoryData['inventory_cost_valuation'] ?? 0, 2) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Tổng vốn đọng trong hàng tồn</p>
        </div>

        <!-- Valuation Retail -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                <span>Giá trị Tồn (Theo Giá Bán)</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-tags"></i>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-slate-900">
                ${{ number_format($inventoryData['inventory_retail_valuation'] ?? 0, 2) }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Doanh thu dự kiến khi bán hết</p>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-400 text-xs font-bold uppercase">
                <span>Cảnh báo Sắp Hết Hàng</span>
                <span class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-sm">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-rose-600">
                {{ $inventoryData['low_stock_alerts_count'] ?? 0 }}
                <span class="text-xs font-normal text-slate-500">mặt hàng</span>
            </div>
            <p class="text-xs text-rose-500 mt-1">Tồn kho ≤ 10 sản phẩm</p>
        </div>

    </div>

    <!-- Detailed Inventory List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-900 text-sm">Chi tiết Tồn kho từng Sản phẩm</h3>
            <span class="text-xs text-slate-500 font-medium">Tổng cộng {{ $inventoryData['products']->count() }} sản phẩm</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase">
                    <tr>
                        <th class="px-5 py-3.5">Mã & Tên Sản phẩm</th>
                        <th class="px-4 py-3.5 text-center">Số lượng tồn kho</th>
                        <th class="px-4 py-3.5 text-right">Giá vốn đơn vị</th>
                        <th class="px-4 py-3.5 text-right">Giá bán niêm yết</th>
                        <th class="px-4 py-3.5 text-right">Tổng giá trị vốn</th>
                        <th class="px-4 py-3.5 text-right">Tổng giá trị bán lẻ</th>
                        <th class="px-4 py-3.5 text-center">Trạng thái tồn kho</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($inventoryData['products'] as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $p->product_name }}</div>
                            <span class="text-[11px] text-slate-400 font-mono">ID: #{{ $p->product_id }}</span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $p->stock_quantity <= 10 ? 'bg-rose-100 text-rose-800' : 'bg-blue-50 text-blue-800' }}">
                                {{ $p->stock_quantity }} chiếc
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-600">
                            ${{ number_format($p->cost_price, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-900 font-semibold">
                            ${{ number_format($p->selling_price, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-amber-700 font-semibold">
                            ${{ number_format($p->stock_quantity * (float) $p->cost_price, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-emerald-700 font-semibold">
                            ${{ number_format($p->stock_quantity * (float) $p->selling_price, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            @if($p->stock_quantity <= 10)
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> Cần nhập thêm
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                    <i class="fa-solid fa-circle-check mr-1"></i> Ổn định
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">Không có dữ liệu tồn kho</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
