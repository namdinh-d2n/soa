<?php

namespace App\Http\Controllers;

use App\Models\OrderReport;
use App\Models\ProductReport;
use App\Services\ReportAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportAnalyticsService $analyticsService;

    public function __construct(ReportAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * GET /api/reports/overview: Thống kê tổng quan
     * - Số lượng hàng tồn
     * - Số lượng hàng bán được
     * - Doanh thu
     * - Chi phí
     * - Lợi nhuận
     */
    public function overview(Request $request): JsonResponse
    {
        // Tùy chọn tự động sync trước khi lấy báo cáo realtime
        if ($request->boolean('realtime')) {
            $this->analyticsService->syncData();
        }

        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $data = $this->analyticsService->getOverviewReport($filters);

        return response()->json([
            'status' => true,
            'message' => 'Lấy dữ liệu báo cáo tổng hợp thành công',
            'data' => $data,
        ], 200);
    }

    /**
     * GET /api/reports/products: Báo cáo thống kê theo sản phẩm
     * Danh sách sản phẩm: Tồn kho, Số lượng đã bán, Doanh thu, Chi phí, Lợi nhuận
     */
    public function products(Request $request): JsonResponse
    {
        if ($request->boolean('realtime')) {
            $this->analyticsService->syncData();
        }

        $filters = [
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'revenue'),
            'order' => $request->query('order', 'desc'),
        ];

        $reports = $this->analyticsService->getProductReports($filters);

        return response()->json([
            'status' => true,
            'message' => 'Lấy báo cáo theo sản phẩm thành công',
            'count' => $reports->count(),
            'data' => $reports,
        ], 200);
    }

    /**
     * GET /api/reports/products/{id}: Báo cáo chi tiết cho một sản phẩm
     */
    public function productDetail(string|int $id): JsonResponse
    {
        $report = ProductReport::where('product_id', $id)->orWhere('id', $id)->first();

        if (! $report) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy dữ liệu báo cáo cho sản phẩm ID {$id}",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Báo cáo chi tiết sản phẩm {$report->product_name}",
            'data' => $report,
        ], 200);
    }

    /**
     * GET /api/reports/orders: Báo cáo thống kê theo đơn hàng
     * Danh sách đơn hàng: Mã đơn, Khách hàng, Doanh thu, Chi phí, Lợi nhuận, Chi tiết sản phẩm
     */
    public function orders(Request $request): JsonResponse
    {
        if ($request->boolean('realtime')) {
            $this->analyticsService->syncData();
        }

        $filters = [
            'status' => $request->query('status'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search' => $request->query('search'),
        ];

        $reports = $this->analyticsService->getOrderReports($filters);

        return response()->json([
            'status' => true,
            'message' => 'Lấy báo cáo theo đơn hàng thành công',
            'count' => $reports->count(),
            'data' => $reports,
        ], 200);
    }

    /**
     * GET /api/reports/orders/{id}: Báo cáo chi tiết một đơn hàng cụ thể
     */
    public function orderDetail(string|int $id): JsonResponse
    {
        $report = OrderReport::where('order_id', $id)->orWhere('id', $id)->first();

        if (! $report) {
            return response()->json([
                'status' => false,
                'message' => "Không tìm thấy dữ liệu báo cáo cho đơn hàng ID {$id}",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Chi tiết báo cáo đơn hàng #{$report->order_id}",
            'data' => $report,
        ], 200);
    }

    /**
     * GET /api/reports/inventory: Thống kê chi tiết hàng tồn kho
     */
    public function inventory(): JsonResponse
    {
        $products = ProductReport::orderBy('stock_quantity', 'asc')->get();

        $totalInventory = (int) $products->sum('stock_quantity');
        $totalCostValue = (float) $products->sum(fn ($p) => $p->stock_quantity * (float) $p->cost_price);
        $totalRetailValue = (float) $products->sum(fn ($p) => $p->stock_quantity * (float) $p->selling_price);

        // Cảnh báo các sản phẩm có số lượng tồn thấp (dưới 10 chiếc)
        $lowStockProducts = $products->filter(fn ($p) => $p->stock_quantity <= 10)->values();

        return response()->json([
            'status' => true,
            'message' => 'Thống kê tồn kho thành công',
            'data' => [
                'total_inventory_items' => $totalInventory,
                'inventory_cost_valuation' => round($totalCostValue, 2),
                'inventory_retail_valuation' => round($totalRetailValue, 2),
                'low_stock_alerts_count' => $lowStockProducts->count(),
                'low_stock_products' => $lowStockProducts,
                'products' => $products,
            ],
        ], 200);
    }

    /**
     * POST /api/reports/sync: Đồng bộ dữ liệu thủ công từ Product Service và Order Service
     */
    public function sync(): JsonResponse
    {
        $result = $this->analyticsService->syncData();

        return response()->json([
            'status' => true,
            'message' => 'Đồng bộ hóa dữ liệu báo cáo thành công từ các dịch vụ SOA',
            'data' => $result,
        ], 200);
    }

    /**
     * Giao diện HTML: Trang Báo cáo Tổng quan (Dashboard)
     */
    public function viewOverview(Request $request)
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $data = $this->analyticsService->getOverviewReport($filters);

        return view('reports.overview', [
            'summary' => $data['summary'],
            'ordersMetrics' => $data['orders_metrics'],
            'topProducts' => $data['top_selling_products'],
        ]);
    }

    /**
     * Giao diện HTML: Trang Báo cáo theo Sản phẩm
     */
    public function viewProducts(Request $request)
    {
        $filters = [
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'revenue'),
            'order' => $request->query('order', 'desc'),
        ];

        $products = $this->analyticsService->getProductReports($filters);

        return view('reports.products', [
            'products' => $products,
        ]);
    }

    /**
     * Giao diện HTML: Trang Báo cáo theo Đơn hàng
     */
    public function viewOrders(Request $request)
    {
        $filters = [
            'status' => $request->query('status'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search' => $request->query('search'),
        ];

        $orders = $this->analyticsService->getOrderReports($filters);

        return view('reports.orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Giao diện HTML: Trang Báo cáo Hàng tồn kho
     */
    public function viewInventory()
    {
        $products = ProductReport::orderBy('stock_quantity', 'asc')->get();

        $totalInventory = (int) $products->sum('stock_quantity');
        $totalCostValue = (float) $products->sum(fn ($p) => $p->stock_quantity * (float) $p->cost_price);
        $totalRetailValue = (float) $products->sum(fn ($p) => $p->stock_quantity * (float) $p->selling_price);
        $lowStockProducts = $products->filter(fn ($p) => $p->stock_quantity <= 10)->values();

        return view('reports.inventory', [
            'inventoryData' => [
                'total_inventory_items' => $totalInventory,
                'inventory_cost_valuation' => round($totalCostValue, 2),
                'inventory_retail_valuation' => round($totalRetailValue, 2),
                'low_stock_alerts_count' => $lowStockProducts->count(),
                'low_stock_products' => $lowStockProducts,
                'products' => $products,
            ],
        ]);
    }
}
