<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng lưu dữ liệu thống kê báo cáo theo đơn hàng (Report Service Database)
     */
    public function up(): void
    {
        Schema::create('order_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique(); // ID đơn hàng từ Order Service
            $table->string('customer_name', 255); // Tên khách hàng
            $table->string('customer_email', 255); // Email khách hàng
            $table->timestamp('order_date')->nullable(); // Ngày tạo đơn
            $table->string('status', 50)->default('pending'); // Trạng thái đơn: pending, completed, cancelled
            $table->integer('total_quantity')->default(0); // Tổng số lượng sản phẩm bán trong đơn
            $table->decimal('revenue', 10, 2)->default(0.00); // Doanh thu của đơn hàng (total_amount)
            $table->decimal('cost', 10, 2)->default(0.00); // Tổng chi phí vốn của đơn hàng
            $table->decimal('profit', 10, 2)->default(0.00); // Lợi nhuận của đơn hàng (revenue - cost)
            $table->json('items')->nullable(); // Chi tiết các mặt hàng trong đơn kèm giá vốn, chi phí, lợi nhuận
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_reports');
    }
};
