<?php

namespace Tests\Feature;

use App\Models\OrderReport;
use App\Models\ProductReport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo dữ liệu báo cáo sản phẩm mẫu
        ProductReport::create([
            'product_id' => 1,
            'product_name' => 'iPhone 15 Pro Max',
            'stock_quantity' => 20, // Hàng tồn kho
            'cost_price' => 800.00, // Chi phí vốn
            'selling_price' => 1200.00, // Giá bán
            'sold_quantity' => 5, // Hàng bán được
            'revenue' => 6000.00, // Doanh thu = 5 * 1200
            'cost' => 4000.00, // Chi phí = 5 * 800
            'profit' => 2000.00, // Lợi nhuận = 6000 - 4000
            'profit_margin' => 33.33,
            'synced_at' => Carbon::now(),
        ]);

        ProductReport::create([
            'product_id' => 2,
            'product_name' => 'Tai nghe AirPods Pro 2',
            'stock_quantity' => 15,
            'cost_price' => 150.00,
            'selling_price' => 250.00,
            'sold_quantity' => 10,
            'revenue' => 2500.00,
            'cost' => 1500.00,
            'profit' => 1000.00,
            'profit_margin' => 40.00,
            'synced_at' => Carbon::now(),
        ]);

        // Tạo dữ liệu báo cáo đơn hàng mẫu
        OrderReport::create([
            'order_id' => 101,
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'order_date' => Carbon::now()->subDay(),
            'status' => 'completed',
            'total_quantity' => 2,
            'revenue' => 1450.00,
            'cost' => 950.00,
            'profit' => 500.00,
            'items' => [
                [
                    'product_id' => 1,
                    'product_name' => 'iPhone 15 Pro Max',
                    'quantity' => 1,
                    'unit_price' => 1200.00,
                    'cost_price' => 800.00,
                    'revenue' => 1200.00,
                    'cost' => 800.00,
                    'profit' => 400.00,
                ],
                [
                    'product_id' => 2,
                    'product_name' => 'Tai nghe AirPods Pro 2',
                    'quantity' => 1,
                    'unit_price' => 250.00,
                    'cost_price' => 150.00,
                    'revenue' => 250.00,
                    'cost' => 150.00,
                    'profit' => 100.00,
                ],
            ],
        ]);

        OrderReport::create([
            'order_id' => 102,
            'customer_name' => 'Tran Thi Test',
            'customer_email' => 'tran@example.com',
            'order_date' => Carbon::now(),
            'status' => 'pending',
            'total_quantity' => 1,
            'revenue' => 1200.00,
            'cost' => 800.00,
            'profit' => 400.00,
            'items' => [
                [
                    'product_id' => 1,
                    'product_name' => 'iPhone 15 Pro Max',
                    'quantity' => 1,
                    'unit_price' => 1200.00,
                    'cost_price' => 800.00,
                    'revenue' => 1200.00,
                    'cost' => 800.00,
                    'profit' => 400.00,
                ],
            ],
        ]);
    }

    /**
     * Test GET /api/reports/overview: Thống kê hàng tồn, hàng bán được, doanh thu, chi phí, lợi nhuận
     */
    public function test_can_get_overview_report(): void
    {
        $response = $this->getJson('/api/reports/overview');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'summary' => [
                        'total_inventory_quantity', // Số lượng hàng tồn
                        'total_sold_quantity',      // Hàng bán được
                        'total_revenue',            // Doanh thu
                        'total_cost',               // Chi phí
                        'total_profit',             // Lợi nhuận
                        'profit_margin_percent',    // Tỷ suất lợi nhuận
                    ],
                    'orders_metrics' => [
                        'total',
                        'completed',
                        'pending',
                        'cancelled',
                    ],
                    'top_selling_products',
                ],
            ]);

        // Kiểm tra tính toán chính xác
        // Tổng tồn kho: 20 + 15 = 35
        $this->assertEquals(35, $response->json('data.summary.total_inventory_quantity'));
        // Đơn completed (ID 101): total_quantity = 2, revenue = 1450, cost = 950, profit = 500
        $this->assertEquals(2, $response->json('data.summary.total_sold_quantity'));
        $this->assertEquals(1450.00, (float) $response->json('data.summary.total_revenue'));
        $this->assertEquals(950.00, (float) $response->json('data.summary.total_cost'));
        $this->assertEquals(500.00, (float) $response->json('data.summary.total_profit'));
    }

    /**
     * Test GET /api/reports/products: Báo cáo theo sản phẩm
     */
    public function test_can_get_product_reports(): void
    {
        $response = $this->getJson('/api/reports/products');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('count', 2)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'product_id',
                        'product_name',
                        'stock_quantity', // Hàng tồn
                        'sold_quantity',  // Hàng bán được
                        'revenue',        // Doanh thu
                        'cost',           // Chi phí
                        'profit',         // Lợi nhuận
                        'profit_margin',  // Tỷ suất
                    ],
                ],
            ]);
    }

    /**
     * Test GET /api/reports/products/{id}: Báo cáo chi tiết 1 sản phẩm
     */
    public function test_can_get_single_product_report_detail(): void
    {
        $response = $this->getJson('/api/reports/products/1');

        $response->assertStatus(200)
            ->assertJsonPath('data.product_name', 'iPhone 15 Pro Max')
            ->assertJsonPath('data.stock_quantity', 20)
            ->assertJsonPath('data.sold_quantity', 5)
            ->assertJsonPath('data.profit', '2000.00');
    }

    /**
     * Test GET /api/reports/orders: Báo cáo theo đơn hàng
     */
    public function test_can_get_order_reports(): void
    {
        $response = $this->getJson('/api/reports/orders');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('count', 2)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'order_id',
                        'customer_name',
                        'status',
                        'revenue', // Doanh thu
                        'cost',    // Chi phí
                        'profit',  // Lợi nhuận
                        'items',
                    ],
                ],
            ]);
    }

    /**
     * Test GET /api/reports/orders/{id}: Báo cáo chi tiết 1 đơn hàng
     */
    public function test_can_get_single_order_report_detail(): void
    {
        $response = $this->getJson('/api/reports/orders/101');

        $response->assertStatus(200)
            ->assertJsonPath('data.customer_name', 'Nguyen Van Test')
            ->assertJsonPath('data.revenue', '1450.00')
            ->assertJsonPath('data.profit', '500.00');
    }

    /**
     * Test GET /api/reports/inventory: Thống kê chi tiết tồn kho
     */
    public function test_can_get_inventory_report(): void
    {
        $response = $this->getJson('/api/reports/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_inventory_items',
                    'inventory_cost_valuation',
                    'inventory_retail_valuation',
                    'low_stock_alerts_count',
                    'products',
                ],
            ]);

        $this->assertEquals(35, $response->json('data.total_inventory_items'));
    }

    /**
     * Test POST /api/reports/sync: Đồng bộ hóa từ các dịch vụ SOA
     */
    public function test_can_sync_reports_from_soa_services(): void
    {
        Http::fake([
            '*/api/orders' => Http::response([
                'data' => [
                    [
                        'id' => 201,
                        'customer_name' => 'Khách Hang Mới',
                        'customer_email' => 'new@example.com',
                        'status' => 'completed',
                        'total_amount' => 1200.00,
                        'created_at' => Carbon::now()->toIso8601String(),
                        'items' => [
                            [
                                'product_id' => 1,
                                'product_name' => 'iPhone 15 Pro Max',
                                'quantity' => 1,
                                'unit_price' => 1200.00,
                                'total_price' => 1200.00,
                            ],
                        ],
                    ],
                ],
            ], 200),
            '*/api/products/*' => Http::response([
                'id' => 1,
                'name' => 'iPhone 15 Pro Max',
                'quantity' => 19,
                'price' => 1200.00,
            ], 200),
        ]);

        $response = $this->postJson('/api/reports/sync');

        $response->assertStatus(200)
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('order_reports', [
            'order_id' => 201,
            'customer_name' => 'Khách Hang Mới',
            'status' => 'completed',
        ]);
    }

    /**
     * Test các trang HTML hiển thị giao diện báo cáo
     */
    public function test_can_render_html_report_pages(): void
    {
        $responseOverview = $this->get('/');
        $responseOverview->assertStatus(200)
            ->assertSee('Báo cáo Thống kê Tổng hợp')
            ->assertSee('Hàng tồn kho')
            ->assertSee('Hàng bán được')
            ->assertSee('Doanh thu')
            ->assertSee('Chi phí')
            ->assertSee('Lợi nhuận');

        $responseProducts = $this->get('/products-report');
        $responseProducts->assertStatus(200)
            ->assertSee('Báo cáo Thống kê theo Sản phẩm')
            ->assertSee('iPhone 15 Pro Max');

        $responseOrders = $this->get('/orders-report');
        $responseOrders->assertStatus(200)
            ->assertSee('Báo cáo Thống kê theo Đơn hàng')
            ->assertSee('Nguyen Van Test');

        $responseInventory = $this->get('/inventory-report');
        $responseInventory->assertStatus(200)
            ->assertSee('Thống kê Hàng tồn kho');
    }
}
