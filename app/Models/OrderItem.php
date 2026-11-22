<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function (OrderItem $item) {
            // Tự động tính total_price nếu chưa có hoặc có sự thay đổi quantity / unit_price
            if ($item->quantity !== null && $item->unit_price !== null) {
                $item->total_price = round($item->quantity * $item->unit_price, 2);
            }
        });
    }

    /**
     * Mối quan hệ: Một chi tiết đơn hàng thuộc về một đơn hàng (Order)
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
