<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Report & Analytics Service (SOA Architecture)
|--------------------------------------------------------------------------
| Dịch vụ báo cáo hoạt động độc lập trên cổng riêng (localhost:8003)
| và cơ sở dữ liệu riêng (report_service.sqlite / report_db).
| Thống kê: Hàng tồn, Hàng bán được, Doanh thu, Chi phí, Lợi nhuận
| Báo cáo theo sản phẩm và theo đơn hàng.
*/

// Health check / Service info
Route::get('/health', function () {
    return response()->json([
        'service' => 'Report & Analytics Service',
        'version' => '1.0.0',
        'status' => 'healthy',
        'port' => env('APP_PORT', 8003),
        'database' => config('database.default'),
        'soa_connections' => [
            'product_service' => env('PRODUCT_SERVICE_URL', 'http://localhost:8000/api/products'),
            'order_service' => env('ORDER_SERVICE_URL', 'http://localhost:8002/api/orders'),
            'auth_service' => env('AUTH_SERVICE_URL', 'http://localhost:8001/api/verify'),
        ],
    ]);
});

// Nhóm Route Báo cáo & Thống kê (Reports & Analytics)
Route::middleware(['auth.service'])->group(function () {

    // 1. Báo cáo tổng hợp: Hàng tồn, Hàng bán được, Doanh thu, Chi phí, Lợi nhuận
    Route::get('/reports/overview', [ReportController::class, 'overview']);

    // 2. Báo cáo theo sản phẩm (Report by Product)
    Route::get('/reports/products', [ReportController::class, 'products']);
    Route::get('/reports/products/{id}', [ReportController::class, 'productDetail']);

    // 3. Báo cáo theo đơn hàng (Report by Order)
    Route::get('/reports/orders', [ReportController::class, 'orders']);
    Route::get('/reports/orders/{id}', [ReportController::class, 'orderDetail']);

    // 4. Báo cáo chi tiết tồn kho
    Route::get('/reports/inventory', [ReportController::class, 'inventory']);

    // 5. Đồng bộ dữ liệu từ các dịch vụ SOA
    Route::post('/reports/sync', [ReportController::class, 'sync']);

    // Các route đơn hàng hỗ trợ liên thông
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('order_items', OrderItemController::class);
});

// Route sản phẩm hỗ trợ kiểm thử
Route::apiResource('products', ProductController::class);
