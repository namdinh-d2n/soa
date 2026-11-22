<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng lưu dữ liệu thống kê báo cáo theo sản phẩm (Report Service Database)
     */
    public function up(): void
    {
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique(); // ID sản phẩm từ Product Service
            $table->string('product_name', 255); // Tên sản phẩm
            $table->integer('stock_quantity')->default(0); // Số lượng hàng tồn kho
            $table->decimal('cost_price', 10, 2)->default(0.00); // Giá vốn / Chi phí đơn vị
            $table->decimal('selling_price', 10, 2)->default(0.00); // Giá bán niêm yết
            $table->integer('sold_quantity')->default(0); // Số lượng hàng bán được
            $table->decimal('revenue', 10, 2)->default(0.00); // Doanh thu từ sản phẩm
            $table->decimal('cost', 10, 2)->default(0.00); // Tổng chi phí vốn (sold_quantity * cost_price)
            $table->decimal('profit', 10, 2)->default(0.00); // Lợi nhuận (revenue - cost)
            $table->decimal('profit_margin', 5, 2)->default(0.00); // Tỷ suất lợi nhuận (%)
            $table->timestamp('synced_at')->nullable(); // Thời gian đồng bộ gần nhất
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};
