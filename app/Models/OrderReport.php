<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    use HasFactory;

    protected $table = 'order_reports';

    protected $fillable = [
        'order_id',
        'customer_name',
        'customer_email',
        'order_date',
        'status',
        'total_quantity',
        'revenue',
        'cost',
        'profit',
        'items',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'order_date' => 'datetime',
        'total_quantity' => 'integer',
        'revenue' => 'decimal:2',
        'cost' => 'decimal:2',
        'profit' => 'decimal:2',
        'items' => 'array',
    ];

    /**
     * Tự động tính lại lợi nhuận của đơn hàng (Doanh thu - Chi phí)
     */
    public function recalculate(): self
    {
        $this->profit = round((float) $this->revenue - (float) $this->cost, 2);
        $this->save();

        return $this;
    }
}
