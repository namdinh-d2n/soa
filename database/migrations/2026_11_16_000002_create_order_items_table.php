<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // INT (PRIMARY KEY) ID duy nhất của chi tiết đơn hàng
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // ID đơn hàng liên kết bảng orders
            $table->unsignedBigInteger('product_id'); // ID sản phẩm (liên kết với Product Service)
            $table->string('product_name', 255); // Tên sản phẩm
            $table->integer('quantity'); // Số lượng sản phẩm
            $table->decimal('unit_price', 10, 2); // Giá sản phẩm tại thời điểm đặt hàng
            $table->decimal('total_price', 10, 2); // Tổng giá trị: quantity * unit_price
            $table->timestamps(); // created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
