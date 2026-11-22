<?php

namespace App\Services;

use App\Models\OrderReport;
use App\Models\ProductReport;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportAnalyticsService
{
    protected ProductServiceClient $productService;

    protected OrderServiceClient $orderService;

    public function __construct(ProductServiceClient $productService, OrderServiceClient $orderService)
    {
        $this->productService = $productService;
        $this->orderService = $orderService;
    }

    /**
     * Đồng bộ hóa dữ liệu từ Product Service và Order Service vào cơ sở dữ liệu riêng của Report Service
     */
    public function syncData(): array
    {
        // 1. Lấy dữ liệu từ Order Service
        $orders = $this->orderService->getOrders();

        // 2. Lấy danh sách sản phẩm hoặc tạo từ DB cục bộ / Product Service
        $productStats = [];

        foreach ($orders as $order) {
            $orderId = (int) ($order['id'] ?? 0);
            $orderStatus = (string) ($order['status'] ?? 'pending');
            $customerName = (string) ($order['customer_name'] ?? 'Khách lẻ');
            $customerEmail = (string) ($order['customer_email'] ?? 'customer@example.com');
            $orderDate = isset($order['created_at']) ? Carbon::parse($order['created_at']) : Carbon::now();

            $orderItems = $order['items'] ?? [];
            $totalOrderQuantity = 0;
            $orderRevenue = (float) ($order['total_amount'] ?? 0);
            $orderCost = 0.0;
            $detailedItems = [];

            foreach ($orderItems as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $productName = (string) ($item['product_name'] ?? 'Sản phẩm #'.$productId);
                $quantity = (int) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $itemRevenue = (float) ($item['total_price'] ?? ($quantity * $unitPrice));

                // Giá vốn giả định: 65% giá bán hoặc lấy từ DB nếu đã có
                $existingProd = ProductReport::where('product_id', $productId)->first();
                $costPrice = $existingProd ? (float) $existingProd->cost_price : round($unitPrice * 0.65, 2);
                $itemCost = round($quantity * $costPrice, 2);
                $itemProfit = round($itemRevenue - $itemCost, 2);

                $totalOrderQuantity += $quantity;
                $orderCost += $itemCost;

                $detailedItems[] = [
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'cost_price' => $costPrice,
                    'revenue' => $itemRevenue,
                    'cost' => $itemCost,
                    'profit' => $itemProfit,
                ];

                // Nếu đơn hàng hoàn thành (completed), tích lũy vào thống kê sản phẩm
                if ($orderStatus === 'completed') {
                    if (! isset($productStats[$productId])) {
                        $productStats[$productId] = [
                            'product_id' => $productId,
                            'product_name' => $productName,
                            'cost_price' => $costPrice,
                            'selling_price' => $unitPrice,
                            'sold_quantity' => 0,
                            'revenue' => 0.0,
                            'cost' => 0.0,
                        ];
                    }

                    $productStats[$productId]['sold_quantity'] += $quantity;
                    $productStats[$productId]['revenue'] += $itemRevenue;
                    $productStats[$productId]['cost'] += $itemCost;
                }
            }

            $orderProfit = round($orderRevenue - $orderCost, 2);

            // Lưu hoặc cập nhật báo cáo đơn hàng vào Database riêng của Report Service
            OrderReport::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'order_date' => $orderDate,
                    'status' => $orderStatus,
                    'total_quantity' => $totalOrderQuantity,
                    'revenue' => $orderRevenue,
                    'cost' => $orderCost,
                    'profit' => $orderProfit,
                    'items' => $detailedItems,
                ]
            );
        }

        // Cập nhật thống kê sản phẩm vào database riêng
        foreach ($productStats as $pId => $stats) {
            // Lấy tồn kho hiện tại từ Product Service
            $stockQuantity = 20;
            try {
                $pData = $this->productService->getProduct($pId);
                $stockQuantity = (int) ($pData['quantity'] ?? 20);
                $stats['product_name'] = $pData['name'] ?? $stats['product_name'];
            } catch (Exception) {
                // Giữ nguyên fallback stock
            }

            $profit = round($stats['revenue'] - $stats['cost'], 2);
            $margin = $stats['revenue'] > 0 ? round(($profit / $stats['revenue']) * 100, 2) : 0.00;

            ProductReport::updateOrCreate(
                ['product_id' => $pId],
                [
                    'product_name' => $stats['product_name'],
                    'stock_quantity' => $stockQuantity,
                    'cost_price' => $stats['cost_price'],
                    'selling_price' => $stats['selling_price'],
                    'sold_quantity' => $stats['sold_quantity'],
                    'revenue' => $stats['revenue'],
                    'cost' => $stats['cost'],
                    'profit' => $profit,
                    'profit_margin' => $margin,
                    'synced_at' => Carbon::now(),
                ]
            );
        }

        return [
            'synced_orders' => count($orders),
            'synced_products' => count($productStats),
            'timestamp' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Báo cáo tổng hợp: Hàng tồn, Hàng bán được, Doanh thu, Chi phí, Lợi nhuận
     */
    public function getOverviewReport(array $filters = []): array
    {
        // 1. Thống kê sản phẩm và tồn kho
        $productQuery = ProductReport::query();
        $totalInventoryQuantity = (int) $productQuery->sum('stock_quantity');

        // 2. Thống kê đơn hàng (Doanh thu, Chi phí, Lợi nhuận)
        $orderQuery = OrderReport::query();

        // Lọc theo trạng thái đơn hàng (mặc định các phép tính tài chính dựa trên đơn completed)
        if (! empty($filters['start_date'])) {
            $orderQuery->whereDate('order_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $orderQuery->whereDate('order_date', '<=', $filters['end_date']);
        }

        $allOrders = (clone $orderQuery)->get();
        $completedOrders = (clone $orderQuery)->where('status', 'completed')->get();

        $totalSoldQuantity = (int) $completedOrders->sum('total_quantity');
        $totalRevenue = (float) $completedOrders->sum('revenue');
        $totalCost = (float) $completedOrders->sum('cost');
        $totalProfit = round($totalRevenue - $totalCost, 2);
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0.00;

        // Thống kê phân loại đơn hàng
        $ordersCountByStatus = [
            'total' => $allOrders->count(),
            'completed' => $allOrders->where('status', 'completed')->count(),
            'pending' => $allOrders->where('status', 'pending')->count(),
            'cancelled' => $allOrders->where('status', 'cancelled')->count(),
        ];

        // Top 5 sản phẩm bán chạy nhất
        $topProducts = ProductReport::orderByDesc('sold_quantity')->limit(5)->get();

        return [
            'status' => true,
            'summary' => [
                'total_inventory_quantity' => $totalInventoryQuantity, // Số lượng hàng tồn
                'total_sold_quantity' => $totalSoldQuantity,          // Số lượng hàng bán được
                'total_revenue' => $totalRevenue,                      // Doanh thu
                'total_cost' => $totalCost,                            // Chi phí
                'total_profit' => $totalProfit,                        // Lợi nhuận
                'profit_margin_percent' => $profitMargin,              // Tỷ suất lợi nhuận (%)
            ],
            'orders_metrics' => $ordersCountByStatus,
            'top_selling_products' => $topProducts,
            'report_generated_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Báo cáo theo sản phẩm (Report by Product)
     */
    public function getProductReports(array $filters = []): Collection
    {
        $query = ProductReport::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('product_name', 'like', "%{$search}%");
        }

        $sortBy = $filters['sort_by'] ?? 'revenue';
        $order = strtolower($filters['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, ['revenue', 'cost', 'profit', 'sold_quantity', 'stock_quantity', 'profit_margin'])) {
            $query->orderBy($sortBy, $order);
        } else {
            $query->orderByDesc('revenue');
        }

        return $query->get();
    }

    /**
     * Báo cáo theo đơn hàng (Report by Order)
     */
    public function getOrderReports(array $filters = []): Collection
    {
        $query = OrderReport::query()->latest('order_date');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('order_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('order_date', '<=', $filters['end_date']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }
}
