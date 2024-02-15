<?php

namespace App\Models;

use App\Casts\DateEndStockCast;
use App\Casts\DateStartStockCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchStock extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_product_id',
        'date_from',
        'date_to',
        'stock_type',
        'stock_info',
        'last_stock',
        'output_stock',
        'rest_stock',
        'input_manager',
        'diff_stock',
        'input_admin',
        'total_stock',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_from' => DateStartStockCast::class,
        'date_to' => DateEndStockCast::class,
        'stock_type' => 'integer',
        'last_stock' => 'integer',
        'output_stock' => 'integer',
        'rest_stock' => 'integer',
        'input_manager' => 'integer',
        'diff_stock' => 'integer',
        'input_admin' => 'integer',
        'total_stock' => 'integer',
    ];

    // relation
    public function product()
    {
        return $this->belongsTo(BranchProduct::class, 'branch_product_id', 'id')->with(['product', 'branch']);
    }

    // scope
    public function scopeByBranchProduct(Builder $builder, $branchProduct)
    {
        $branchProductId = is_object($branchProduct) ? $branchProduct->id : ($branchProduct ?? 0);
        return $builder->where('branch_product_id', '=', $branchProductId);
    }

    public function scopeByDates(Builder $builder, string $startDate, string $endDate): Builder
    {
        return $builder->whereBetween('date_from', [$startDate, $endDate]);
        // ->whereBetween('date_to', [$startDate, $endDate]);
    }

    // accessor
    public function getRealStockAttribute()
    {
        return app('neo')->getProductStock($this);
    }
}
