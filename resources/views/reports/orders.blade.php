@extends('layouts.app')

@section('title', 'Báo cáo theo Đơn hàng - Dịch vụ Báo cáo')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Form -->
    <div class="sm:flex sm:items-center sm:justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center">
                <i class="fa-solid fa-receipt text-indigo-600 mr-3"></i>
                Báo cáo Thống kê theo Đơn hàng
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Thống kê từng đơn hàng: Mã đơn, Khách hàng, Doanh thu, Chi phí giá vốn và Lợi nhuận của từng đơn.
            </p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ url('/orders-report') }}" class="mt-4 sm:mt-0 flex flex-wrap items-center gap-3">
            <div>
                <select name="status" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 block">
                    <option value="">Tất cả trạng thái</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành (Completed)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý (Pending)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy (Cancelled)</option>
                </select>
            </div>

            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email khách..."
                    class="bg-slate-50 border border-slate-200 text-slate-700 text-xs rounded-lg px-3 py-2 w-44 focus:ring-indigo-500 focus:border-indigo-500 block">
            </div>

            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition shadow-sm">
                Lọc
            </button>

            @if(request('status') || request('search'))
                <a href="{{ url('/orders-report') }}" class="text-xs text-slate-500 hover:text-rose-600 font-medium">
                    <i class="fa-solid fa-xmark"></i> Xóa
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Mã Đơn hàng</th>
                        <th class="px-5 py-3.5">Khách hàng</th>
                        <th class="px-4 py-3.5 text-center">Trạng thái</th>
                        <th class="px-4 py-3.5 text-center">Ngày đặt</th>
                        <th class="px-4 py-3.5 text-center">Số lượng</th>
                        <th class="px-4 py-3.5 text-right font-bold text-violet-700">Doanh thu</th>
                        <th class="px-4 py-3.5 text-right font-bold text-amber-700">Chi phí (Vốn)</th>
                        <th class="px-5 py-3.5 text-right font-black text-emerald-700">Lợi nhuận</th>
                        <th class="px-4 py-3.5 text-center">Chi tiết món</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($orders as $o)
                    <tr class="hover:bg-indigo-50/40 transition">
                        <!-- Order ID -->
                        <td class="px-5 py-4 font-mono font-bold text-slate-900 text-sm">
                            #{{ $o->order_id }}
                        </td>

                        <!-- Customer Info -->
                        <td class="px-5 py-4">
                            <div class="font-bold text-slate-900">{{ $o->customer_name }}</div>
                            <span class="text-[11px] text-slate-400">{{ $o->customer_email }}</span>
                        </td>

                        <!-- Status Badge -->
                        <td class="px-4 py-4 text-center">
                            @if($o->status === 'completed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fa-solid fa-check mr-1 text-[10px]"></i> Hoàn thành
                                </span>
                            @elseif($o->status === 'pending')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fa-solid fa-clock mr-1 text-[10px]"></i> Chờ xử lý
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="fa-solid fa-xmark mr-1 text-[10px]"></i> Đã hủy
                                </span>
                            @endif
                        </td>

                        <!-- Date -->
                        <td class="px-4 py-4 text-center text-slate-500 font-mono text-[11px]">
                            {{ $o->order_date ? $o->order_date->format('d/m/Y H:i') : 'N/A' }}
                        </td>

                        <!-- Quantity -->
                        <td class="px-4 py-4 text-center font-bold text-slate-800">
                            {{ $o->total_quantity }}
                        </td>

                        <!-- Revenue -->
                        <td class="px-4 py-4 text-right font-mono font-bold text-violet-700 text-sm">
                            ${{ number_format($o->revenue, 2) }}
                        </td>

                        <!-- Cost -->
                        <td class="px-4 py-4 text-right font-mono font-semibold text-amber-700 text-sm">
                            ${{ number_format($o->cost, 2) }}
                        </td>

                        <!-- Profit -->
                        <td class="px-5 py-4 text-right font-mono font-black text-emerald-700 text-sm">
                            +${{ number_format($o->profit, 2) }}
                        </td>

                        <!-- Action View Details -->
                        <td class="px-4 py-4 text-center">
                            <button onclick="toggleOrderItems('order-items-{{ $o->id }}')" class="bg-slate-100 hover:bg-indigo-100 text-slate-700 hover:text-indigo-700 px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                                <i class="fa-solid fa-list-ul mr-1"></i> Xem
                            </button>
                        </td>
                    </tr>

                    <!-- Collapsible Row: Chi tiết các mặt hàng trong đơn -->
                    <tr id="order-items-{{ $o->id }}" class="hidden bg-slate-50/80">
                        <td colspan="9" class="px-8 py-4">
                            <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-inner">
                                <div class="text-xs font-bold text-slate-800 mb-2.5 flex items-center">
                                    <i class="fa-solid fa-basket-shopping text-indigo-600 mr-2"></i>
                                    Danh sách sản phẩm trong đơn hàng #{{ $o->order_id }}:
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @if(is_array($o->items))
                                        @foreach($o->items as $it)
                                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs flex justify-between items-center">
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $it['product_name'] ?? ('Sản phẩm #' . ($it['product_id'] ?? '')) }}</div>
                                                <div class="text-[11px] text-slate-500 mt-0.5">
                                                    SL: <strong class="text-slate-800">{{ $it['quantity'] ?? 1 }}</strong> × ${{ number_format($it['unit_price'] ?? 0, 2) }}
                                                    (Vốn: ${{ number_format($it['cost_price'] ?? 0, 2) }})
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-violet-700 font-bold">${{ number_format($it['revenue'] ?? 0, 2) }}</div>
                                                <div class="text-emerald-600 font-bold text-[11px]">+${{ number_format($it['profit'] ?? 0, 2) }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-slate-400 text-xs">Không có chi tiết mặt hàng</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-3xl mb-2 block text-slate-300"></i>
                            Không tìm thấy dữ liệu đơn hàng nào phù hợp
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function toggleOrderItems(elementId) {
        const row = document.getElementById(elementId);
        if (row) {
            row.classList.toggle('hidden');
        }
    }
</script>
@endpush
