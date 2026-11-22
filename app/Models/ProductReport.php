<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReport extends Model
{
    use HasFactory;

    protected $table = 'product_reports';

    protected $fillable = [
        'product_id',
        'product_name',
        'stock_quantity',
        'cost_price',
        'selling_price',
        'sold_quantity',
        'revenue',
        'cost',
        'profit',
        'profit_margin',
        'synced_at',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'stock_quantity' => 'integer',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'sold_quantity' => 'integer',
        'revenue' => 'decimal:2',
        'cost' => 'decimal:2',
        'profit' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    /**
     * Tự động tính toán lại chi phí, lợi nhuận và tỷ suất lợi nhuận của sản phẩm
     */
    public function recalculate(): self
    {
        $this->cost = round($this->sold_quantity * (float) $this->cost_price, 2);
        $this->profit = round((float) $this->revenue - (float) $this->cost, 2);

        if ((float) $this->revenue > 0) {
            $this->profit_margin = round(($this->profit / (float) $this->revenue) * 100, 2);
        } else {
            $this->profit_margin = 0.00;
        }

        $this->save();

        return $this;
    }
}
