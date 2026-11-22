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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // INT (PRIMARY KEY) ID duy nhất của đơn hàng
            $table->string('customer_name', 255); // Tên khách hàng
            $table->string('customer_email', 255); // Email khách hàng
            $table->decimal('total_amount', 10, 2)->default(0.00); // Tổng số tiền của đơn hàng
            $table->string('status', 50)->default('pending'); // Trạng thái: pending, completed, cancelled
            $table->timestamps(); // created_at và updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
