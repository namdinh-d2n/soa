<?php

namespace Database\Seeders;

use App\Models\OrderReport;
use App\Models\ProductReport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds for Report Service.
     */
    public function run(): void
    {
        // 1. Thống kê sản phẩm mẫu (Tồn kho, Đã bán, Doanh thu, Chi phí, Lợi nhuận)
        $products = [
            [
                'product_id' => 1,
                'product_name' => 'iPhone 15 Pro Max',
                'stock_quantity' => 30, // Hàng tồn kho
                'cost_price' => 800.00, // Chi phí giá vốn
                'selling_price' => 1200.00, // Giá bán
                'sold_quantity' => 15, // Hàng bán được
                'revenue' => 18000.00, // Doanh thu = 15 * 1200
                'cost' => 12000.00, // Chi phí = 15 * 800
                'profit' => 6000.00, // Lợi nhuận = 18000 - 12000
                'profit_margin' => 33.33, // Tỷ suất lợi nhuận = 33.33%
                'synced_at' => Carbon::now(),
            ],
            [
                'product_id' => 2,
                'product_name' => 'MacBook Pro M3',
                'stock_quantity' => 12,
                'cost_price' => 1400.00,
                'selling_price' => 2000.00,
                'sold_quantity' => 8,
                'revenue' => 16000.00,
                'cost' => 11200.00,
                'profit' => 4800.00,
                'profit_margin' => 30.00,
                'synced_at' => Carbon::now(),
            ],
            [
                'product_id' => 3,
                'product_name' => 'Tai nghe AirPods Pro 2',
                'stock_quantity' => 50,
                'cost_price' => 150.00,
                'selling_price' => 250.00,
                'sold_quantity' => 25,
                'revenue' => 6250.00,
                'cost' => 3750.00,
                'profit' => 2500.00,
                'profit_margin' => 40.00,
                'synced_at' => Carbon::now(),
            ],
            [
                'product_id' => 4,
                'product_name' => 'Bàn phím cơ Bluetooth',
                'stock_quantity' => 5, // Tồn kho thấp (Low stock)
                'cost_price' => 50.00,
                'selling_price' => 90.00,
                'sold_quantity' => 10,
                'revenue' => 900.00,
                'cost' => 500.00,
                'profit' => 400.00,
                'profit_margin' => 44.44,
                'synced_at' => Carbon::now(),
            ],
        ];

        foreach ($products as $p) {
            ProductReport::updateOrCreate(['product_id' => $p['product_id']], $p);
        }

        // 2. Thống kê đơn hàng mẫu (Mã đơn, Doanh thu, Chi phí, Lợi nhuận, Chi tiết)
        $orders = [
            [
                'order_id' => 1001,
                'customer_name' => 'Nguyễn Văn An',
                'customer_email' => 'an.nguyen@example.com',
                'order_date' => Carbon::now()->subDays(2),
                'status' => 'completed',
                'total_quantity' => 2,
                'revenue' => 1450.00,
                'cost' => 950.00,
                'profit' => 500.00,
                'items' => [
                    ['product_id' => 1, 'product_name' => 'iPhone 15 Pro Max', 'quantity' => 1, 'unit_price' => 1200.00, 'cost_price' => 800.00, 'revenue' => 1200.00, 'cost' => 800.00, 'profit' => 400.00],
                    ['product_id' => 3, 'product_name' => 'Tai nghe AirPods Pro 2', 'quantity' => 1, 'unit_price' => 250.00, 'cost_price' => 150.00, 'revenue' => 250.00, 'cost' => 150.00, 'profit' => 100.00],
                ],
            ],
            [
                'order_id' => 1002,
                'customer_name' => 'Trần Thị Bình',
                'customer_email' => 'binh.tran@example.com',
                'order_date' => Carbon::now()->subDays(1),
                'status' => 'completed',
                'total_quantity' => 1,
                'revenue' => 2000.00,
                'cost' => 1400.00,
                'profit' => 600.00,
                'items' => [
                    ['product_id' => 2, 'product_name' => 'MacBook Pro M3', 'quantity' => 1, 'unit_price' => 2000.00, 'cost_price' => 1400.00, 'revenue' => 2000.00, 'cost' => 1400.00, 'profit' => 600.00],
                ],
            ],
            [
                'order_id' => 1003,
                'customer_name' => 'Lê Hoàng Cường',
                'customer_email' => 'cuong.le@example.com',
                'order_date' => Carbon::now(),
                'status' => 'pending',
                'total_quantity' => 2,
                'revenue' => 1290.00,
                'cost' => 850.00,
                'profit' => 440.00,
                'items' => [
                    ['product_id' => 1, 'product_name' => 'iPhone 15 Pro Max', 'quantity' => 1, 'unit_price' => 1200.00, 'cost_price' => 800.00, 'revenue' => 1200.00, 'cost' => 800.00, 'profit' => 400.00],
                    ['product_id' => 4, 'product_name' => 'Bàn phím cơ Bluetooth', 'quantity' => 1, 'unit_price' => 90.00, 'cost_price' => 50.00, 'revenue' => 90.00, 'cost' => 50.00, 'profit' => 40.00],
                ],
            ],
            [
                'order_id' => 1004,
                'customer_name' => 'Phạm Minh Đức',
                'customer_email' => 'duc.pham@example.com',
                'order_date' => Carbon::now()->subHours(5),
                'status' => 'completed',
                'total_quantity' => 2,
                'revenue' => 2250.00,
                'cost' => 1550.00,
                'profit' => 700.00,
                'items' => [
                    ['product_id' => 2, 'product_name' => 'MacBook Pro M3', 'quantity' => 1, 'unit_price' => 2000.00, 'cost_price' => 1400.00, 'revenue' => 2000.00, 'cost' => 1400.00, 'profit' => 600.00],
                    ['product_id' => 3, 'product_name' => 'Tai nghe AirPods Pro 2', 'quantity' => 1, 'unit_price' => 250.00, 'cost_price' => 150.00, 'revenue' => 250.00, 'cost' => 150.00, 'profit' => 100.00],
                ],
            ],
        ];

        foreach ($orders as $o) {
            OrderReport::updateOrCreate(['order_id' => $o['order_id']], $o);
        }
    }
}
