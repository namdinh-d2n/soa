<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tạo dữ liệu sản phẩm mẫu (dùng cho standalone test hoặc Product Service)
        if (class_exists(Product::class)) {
            $products = [
                ['name' => 'iPhone 15 Pro Max', 'description' => 'Điện thoại Apple iPhone 15 Pro Max 256GB', 'price' => 1199.00, 'quantity' => 25],
                ['name' => 'MacBook Pro M3', 'description' => 'Laptop Apple MacBook Pro 14 inch M3', 'price' => 1999.00, 'quantity' => 15],
                ['name' => 'Tai nghe AirPods Pro 2', 'description' => 'Tai nghe chống ồn chủ động Apple', 'price' => 249.00, 'quantity' => 50],
                ['name' => 'Bàn phím cơ không dây', 'description' => 'Bàn phím cơ custom switch mượt', 'price' => 89.00, 'quantity' => 40],
            ];

            foreach ($products as $p) {
                Product::firstOrCreate(['name' => $p['name']], $p);
            }
        }

        // 2. Tạo đơn hàng mẫu số 1
        $order1 = Order::create([
            'customer_name' => 'Nguyễn Văn An',
            'customer_email' => 'an.nguyen@example.com',
            'total_amount' => 1448.00,
            'status' => Order::STATUS_PENDING,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => 1,
            'product_name' => 'iPhone 15 Pro Max',
            'quantity' => 1,
            'unit_price' => 1199.00,
            'total_price' => 1199.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => 3,
            'product_name' => 'Tai nghe AirPods Pro 2',
            'quantity' => 1,
            'unit_price' => 249.00,
            'total_price' => 249.00,
        ]);

        $order1->recalculateTotal();

        // 3. Tạo đơn hàng mẫu số 2
        $order2 = Order::create([
            'customer_name' => 'Trần Thị Bình',
            'customer_email' => 'binh.tran@example.com',
            'total_amount' => 1999.00,
            'status' => Order::STATUS_COMPLETED,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => 2,
            'product_name' => 'MacBook Pro M3',
            'quantity' => 1,
            'unit_price' => 1999.00,
            'total_price' => 1999.00,
        ]);

        $order2->recalculateTotal();
    }
}
