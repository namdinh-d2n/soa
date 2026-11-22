<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Giả lập (Mock) Product Service phản hồi qua HTTP Client (Chuẩn kiểm thử SOA trong Laravel)
        Http::fake([
            '*/api/products/101' => Http::response([
                'id' => 101,
                'name' => 'Bàn phím cơ Test',
                'description' => 'Mô tả test',
                'price' => 100.00,
                'quantity' => 10,
            ], 200),
            '*/api/products/102' => Http::response([
                'id' => 102,
                'name' => 'Chuột không dây Test',
                'description' => 'Mô tả chuột',
                'price' => 50.00,
                'quantity' => 2,
            ], 200),
            '*/api/products/9999' => Http::response([
                'message' => 'Không tìm thấy sản phẩm',
            ], 404),
            '*/api/products/*' => Http::response([
                'id' => 1,
                'name' => 'Sản phẩm mặc định',
                'price' => 100.00,
                'quantity' => 50,
            ], 200),
        ]);
    }

    /**
     * Test GET /api/orders
     */
    public function test_can_list_orders(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 200.00,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 101,
            'product_name' => 'Bàn phím cơ Test',
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
        ]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'count',
                'data' => [
                    '*' => ['id', 'customer_name', 'customer_email', 'total_amount', 'status', 'items'],
                ],
            ]);
    }

    /**
     * Test GET /api/orders/{id}
     */
    public function test_can_get_single_order_detail(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.customer_name', 'Nguyen Van Test');
    }

    /**
     * Test GET /api/orders/{id} with invalid id returns 404
     */
    public function test_get_nonexistent_order_returns_404(): void
    {
        $response = $this->getJson('/api/orders/99999');
        $response->assertStatus(404);
    }

    /**
     * Test POST /api/orders creating order with items and checking stock
     */
    public function test_can_create_order_with_items_and_stock_validation(): void
    {
        $payload = [
            'customer_name' => 'Le Van C',
            'customer_email' => 'c.le@example.com',
            'status' => 'pending',
            'items' => [
                [
                    'product_id' => 101,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.customer_name', 'Le Van C')
            ->assertJsonPath('data.total_amount', '200.00');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Le Van C',
            'total_amount' => 200.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => 101,
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_price' => 200.00,
        ]);
    }

    /**
     * Test POST /api/orders fails when requested quantity exceeds inventory
     */
    public function test_create_order_fails_when_stock_insufficient(): void
    {
        $payload = [
            'customer_name' => 'Le Van C',
            'customer_email' => 'c.le@example.com',
            'items' => [
                [
                    'product_id' => 102, // stock is 2 in mock
                    'quantity' => 10,   // requesting 10
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(400)
            ->assertJsonPath('status', false);
    }

    /**
     * Test PUT /api/orders/{id} updating status
     */
    public function test_can_update_order_status(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/orders/{$order->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    /**
     * Test DELETE /api/orders/{id}
     */
    public function test_can_delete_order(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /**
     * Test GET /api/order_items
     */
    public function test_can_list_order_items(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 101,
            'product_name' => 'Bàn phím cơ Test',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $response = $this->getJson('/api/order_items');

        $response->assertStatus(200)
            ->assertJsonPath('count', 1);
    }

    /**
     * Test POST /api/order_items and total_amount recalculation
     */
    public function test_can_add_item_to_order_and_recalculate_total(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 0.00,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/order_items', [
            'order_id' => $order->id,
            'product_id' => 101,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);

        $order->refresh();
        $this->assertEquals(200.00, (float) $order->total_amount);
    }

    /**
     * Test PUT /api/order_items/{id} updates item and order total
     */
    public function test_can_update_order_item_and_recalculate_total(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 101,
            'product_name' => 'Bàn phím cơ Test',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $response = $this->putJson("/api/order_items/{$item->id}", [
            'quantity' => 3,
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(300.00, (float) $order->total_amount);
    }

    /**
     * Test DELETE /api/order_items/{id} deletes item and recalculates total
     */
    public function test_can_delete_order_item_and_recalculate_total(): void
    {
        $order = Order::create([
            'customer_name' => 'Nguyen Van Test',
            'customer_email' => 'test@example.com',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 101,
            'product_name' => 'Bàn phím cơ Test',
            'quantity' => 1,
            'unit_price' => 100.00,
            'total_price' => 100.00,
        ]);

        $response = $this->deleteJson("/api/order_items/{$item->id}");

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(0.00, (float) $order->total_amount);
    }
}
