@extends('layouts.app')

@section('title', 'Báo cáo theo Sản phẩm - Dịch vụ Báo cáo')

@section('content')
<div class="space-y-6">

    <!-- Header & Search/Filter Controls -->
    <div class="sm:flex sm:items-center sm:justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-boxes-stacked text-indigo-600 mr-3"></i>
                Báo cáo Thống kê theo Sản phẩm
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Thống kê chi tiết từng sản phẩm: Số lượng tồn kho, Hàng bán được, Doanh thu, Chi phí và Lợi nhuận.
            </p>
        </div>

        <!-- Search and Sort Form -->
        <form method="GET" action="{{ url('/products-report') }}" class="mt-4 sm:mt-0 flex flex-wrap items-center gap-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên sản phẩm..."
                    class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg pl-9 pr-3 py-2 w-48 sm:w-60 focus:ring-indigo-500 focus:border-indigo-500 block">
            </div>

            <div>
                <select name="sort_by" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 block">
                    <option value="revenue" {{ request('sort_by') === 'revenue' ? 'selected' : '' }}>Sắp xếp: Doanh thu cao nhất</option>
                    <option value="profit" {{ request('sort_by') === 'profit' ? 'selected' : '' }}>Sắp xếp: Lợi nhuận cao nhất</option>
                    <option value="sold_quantity" {{ request('sort_by') === 'sold_quantity' ? 'selected' : '' }}>Sắp xếp: Bán chạy nhất</option>
                    <option value="stock_quantity" {{ request('sort_by') === 'stock_quantity' ? 'selected' : '' }}>Sắp xếp: Tồn kho nhiều nhất</option>
                    <option value="profit_margin" {{ request('sort_by') === 'profit_margin' ? 'selected' : '' }}>Sắp xếp: Tỷ suất lợi nhuận cao</option>
                </select>
            </div>

            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition shadow-sm">
                Lọc
            </button>

            @if(request('search') || request('sort_by'))
                <a href="{{ url('/products-report') }}" class="text-xs text-slate-500 hover:text-rose-600 font-medium">
                    <i class="fa-solid fa-xmark"></i> Xóa
                </a>
            @endif
        </form>
    </div>

    <!-- Products Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Mã & Tên Sản phẩm</th>
                        <th class="px-4 py-3.5 text-center">Hàng tồn kho</th>
                        <th class="px-4 py-3.5 text-center">Hàng bán được</th>
                        <th class="px-4 py-3.5 text-right">Giá vốn (Chi phí/sp)</th>
                        <th class="px-4 py-3.5 text-right">Giá bán niêm yết</th>
                        <th class="px-4 py-3.5 text-right font-bold text-violet-700">Tổng Doanh thu</th>
                        <th class="px-4 py-3.5 text-right font-bold text-amber-700">Tổng Chi phí</th>
                        <th class="px-5 py-3.5 text-right font-black text-emerald-700">Tổng Lợi nhuận</th>
                        <th class="px-4 py-3.5 text-center">Tỷ suất (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($products as $p)
                    <tr class="hover:bg-indigo-50/40 transition">
                        <!-- Product Name & ID -->
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $p->product_name }}</div>
                            <div class="flex items-center space-x-2 mt-0.5">
                                <span class="text-[11px] text-slate-400 font-mono">ID: #{{ $p->product_id }}</span>
                                @if($p->stock_quantity <= 10)
                                    <span class="text-[10px] bg-rose-50 text-rose-600 font-bold px-1.5 py-0.5 rounded border border-rose-200">
                                        Sắp hết hàng
                                    </span>
                                @else
                                    <span class="text-[10px] bg-emerald-50 text-emerald-600 font-semibold px-1.5 py-0.5 rounded border border-emerald-200">
                                        Sẵn sàng bán
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Stock Quantity -->
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $p->stock_quantity <= 10 ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                <i class="fa-solid fa-box mr-1 text-[10px]"></i> {{ $p->stock_quantity }}
                            </span>
                        </td>

                        <!-- Sold Quantity -->
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-cart-check mr-1 text-[10px]"></i> {{ $p->sold_quantity }}
                            </span>
                        </td>

                        <!-- Cost Price -->
                        <td class="px-4 py-4 text-right font-mono text-slate-600">
                            ${{ number_format($p->cost_price, 2) }}
                        </td>

                        <!-- Selling Price -->
                        <td class="px-4 py-4 text-right font-mono text-slate-900 font-semibold">
                            ${{ number_format($p->selling_price, 2) }}
                        </td>

                        <!-- Revenue -->
                        <td class="px-4 py-4 text-right font-mono font-bold text-violet-700 text-sm">
                            ${{ number_format($p->revenue, 2) }}
                        </td>

                        <!-- Cost -->
                        <td class="px-4 py-4 text-right font-mono text-amber-700 font-semibold text-sm">
                            ${{ number_format($p->cost, 2) }}
                        </td>

                        <!-- Profit -->
                        <td class="px-5 py-4 text-right font-mono font-black text-emerald-700 text-sm">
                            +${{ number_format($p->profit, 2) }}
                        </td>

                        <!-- Margin -->
                        <td class="px-4 py-4 text-center">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $p->profit_margin >= 30 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $p->profit_margin }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-3xl mb-2 block text-slate-300"></i>
                            Không tìm thấy dữ liệu báo cáo sản phẩm nào phù hợp
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
