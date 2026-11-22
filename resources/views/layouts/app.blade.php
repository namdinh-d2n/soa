<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dịch vụ Báo cáo & Thống kê - SOA')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-100">
                        <i class="fa-solid fa-chart-pie text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="font-extrabold text-lg text-slate-900 tracking-tight">Report Service</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold border border-indigo-200">SOA Bài 4/5</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Hệ thống Báo cáo & Thống kê Độc lập</p>
                    </div>
                </div>

                <!-- SOA Architecture Badges & Actions -->
                <div class="flex items-center space-x-3">
                    <!-- Port Badge -->
                    <div class="hidden sm:flex items-center space-x-1.5 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1.5 rounded-lg font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Port: <strong>8003</strong></span>
                    </div>

                    <!-- DB Badge -->
                    <div class="hidden md:flex items-center space-x-1.5 text-xs bg-slate-100 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-lg font-medium">
                        <i class="fa-solid fa-database text-slate-500"></i>
                        <span>DB: <strong>report_service.sqlite</strong></span>
                    </div>

                    <!-- Sync Button -->
                    <button onclick="syncSoaData()" id="sync-btn" class="inline-flex items-center space-x-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition-all duration-150 shadow-sm shadow-indigo-200">
                        <i class="fa-solid fa-rotate text-xs" id="sync-icon"></i>
                        <span>Đồng bộ SOA</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-1 sm:space-x-8 overflow-x-auto py-2">
                <a href="{{ url('/') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg transition {{ request()->is('/') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-gauge mr-2 {{ request()->is('/') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                    Tổng quan Thống kê
                </a>
                <a href="{{ url('/products-report') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg transition {{ request()->is('products-report*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-boxes-stacked mr-2 {{ request()->is('products-report*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                    Báo cáo theo Sản phẩm
                </a>
                <a href="{{ url('/orders-report') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg transition {{ request()->is('orders-report*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-receipt mr-2 {{ request()->is('orders-report*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                    Báo cáo theo Đơn hàng
                </a>
                <a href="{{ url('/inventory-report') }}" class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg transition {{ request()->is('inventory-report*') ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="fa-solid fa-warehouse mr-2 {{ request()->is('inventory-report*') ? 'text-indigo-600' : 'text-slate-400' }}"></i>
                    Thống kê Hàng tồn kho
                </a>
            </div>
        </div>
    </nav>

    <!-- Toast Notification Container -->
    <div id="toast" class="fixed top-20 right-6 z-50 transform transition-all duration-300 translate-y-[-20px] opacity-0 pointer-events-none">
        <div class="bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center space-x-3 text-sm">
            <i class="fa-solid fa-circle-check text-emerald-400 text-lg" id="toast-icon"></i>
            <span id="toast-msg">Đã hoàn thành!</span>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:flex sm:justify-between sm:items-center text-xs text-slate-500">
            <div>
                © {{ date('Y') }} Dịch vụ Báo cáo & Thống kê - Kiến trúc Hướng dịch vụ (SOA).
            </div>
            <div class="mt-2 sm:mt-0 flex justify-center space-x-4">
                <span>Cổng Service: <strong class="text-slate-700">8003</strong></span>
                <span>•</span>
                <span>Product Service: <strong class="text-slate-700">8000</strong></span>
                <span>•</span>
                <span>Order Service: <strong class="text-slate-700">8002</strong></span>
            </div>
        </div>
    </footer>

    <!-- Global Sync Script -->
    <script>
        function showToast(msg, isSuccess = true) {
            const toast = document.getElementById('toast');
            const msgEl = document.getElementById('toast-msg');
            const iconEl = document.getElementById('toast-icon');

            msgEl.textContent = msg;
            iconEl.className = isSuccess ? 'fa-solid fa-circle-check text-emerald-400 text-lg' : 'fa-solid fa-circle-xmark text-rose-400 text-lg';

            toast.classList.remove('translate-y-[-20px]', 'opacity-0', 'pointer-events-none');
            setTimeout(() => {
                toast.classList.add('translate-y-[-20px]', 'opacity-0', 'pointer-events-none');
            }, 3500);
        }

        async function syncSoaData() {
            const btn = document.getElementById('sync-btn');
            const icon = document.getElementById('sync-icon');

            icon.classList.add('fa-spin');
            btn.disabled = true;

            try {
                const response = await fetch('{{ url("/api/reports/sync") }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                if (data.status) {
                    showToast('Đã đồng bộ hóa số liệu mới nhất từ các dịch vụ SOA!');
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    showToast('Đồng bộ thất bại: ' + (data.message || 'Lỗi server'), false);
                }
            } catch (err) {
                showToast('Không thể kết nối đến máy chủ', false);
            } finally {
                icon.classList.remove('fa-spin');
                btn.disabled = false;
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
