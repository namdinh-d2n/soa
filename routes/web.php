<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Report & Analytics Service (SOA Dashboard HTML Views)
|--------------------------------------------------------------------------
*/

// 1. Các trang giao diện HTML trực quan
Route::get('/', [ReportController::class, 'viewOverview'])->name('reports.web.overview');
Route::get('/products-report', [ReportController::class, 'viewProducts'])->name('reports.web.products');
Route::get('/orders-report', [ReportController::class, 'viewOrders'])->name('reports.web.orders');
Route::get('/inventory-report', [ReportController::class, 'viewInventory'])->name('reports.web.inventory');

// Thông tin API JSON
Route::get('/api-info', function () {
    return response()->json([
        'service' => 'Report & Analytics Service (SOA)',
        'description' => 'Dịch vụ báo cáo thống kê hoạt động độc lập trên cổng riêng và database riêng.',
        'port' => env('APP_PORT', 8003),
        'features' => [
            'Thống kê số lượng hàng tồn' => 'GET /api/reports/inventory hoặc /reports/overview',
            'Thống kê số lượng hàng bán được' => 'GET /api/reports/overview hoặc /reports/products',
            'Thống kê doanh thu' => 'GET /api/reports/overview hoặc /reports/orders',
            'Thống kê chi phí' => 'GET /api/reports/overview hoặc /reports/products',
            'Thống kê lợi nhuận' => 'GET /api/reports/overview (Lợi nhuận = Doanh thu - Chi phí)',
            'Báo cáo theo sản phẩm' => 'GET /api/reports/products và /api/reports/products/{id}',
            'Báo cáo theo đơn hàng' => 'GET /api/reports/orders và /api/reports/orders/{id}',
            'Đồng bộ dữ liệu SOA' => 'POST /api/reports/sync',
        ],
        'soa_connections' => [
            'product_service' => env('PRODUCT_SERVICE_URL', 'http://localhost:8000/api/products'),
            'order_service' => env('ORDER_SERVICE_URL', 'http://localhost:8002/api/orders'),
            'auth_service' => env('AUTH_SERVICE_URL', 'http://localhost:8001/api/verify'),
        ],
    ]);
});

// 2. Cho phép gọi API trực tiếp không cần tiền tố /api
Route::middleware(['auth.service'])->group(function () {
    Route::get('/reports/overview', [ReportController::class, 'overview']);
    Route::get('/reports/products', [ReportController::class, 'products']);
    Route::get('/reports/products/{id}', [ReportController::class, 'productDetail']);
    Route::get('/reports/orders', [ReportController::class, 'orders']);
    Route::get('/reports/orders/{id}', [ReportController::class, 'orderDetail']);
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);
    Route::post('/reports/sync', [ReportController::class, 'sync']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/order_items', [OrderItemController::class, 'index']);
});
