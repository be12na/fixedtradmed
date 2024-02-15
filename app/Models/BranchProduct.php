<?php

namespace App\Models;

use App\Casts\FullDatetimeCast;
use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchProduct extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_id',
        'product_id',
        'is_active',
        'active_at',
        'active_by',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active_at' => FullDatetimeCast::class,
        'is_active' => 'boolean',
    ];

    // relation
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function beforePreviousStock()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname(strtotime('-2 week', $time), 'Y-m-d');

        return $this->hasOne(BranchStock::class, 'branch_product_id', 'id')
            ->ofMany(['id' => 'max'], function ($query) use ($dates) {
                return $query->whereBetween('date_from', [$dates->start, $dates->end]);
                // ->whereBetween('date_to', [$dates->start, $dates->end]);
            });
    }

    public function previousStock()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname(strtotime('-1 week', $time), 'Y-m-d');

        return $this->hasOne(BranchStock::class, 'branch_product_id', 'id')
            ->ofMany(['id' => 'max'], function ($query) use ($dates) {
                return $query->whereBetween('date_from', [$dates->start, $dates->end]);
                // ->whereBetween('date_to', [$dates->start, $dates->end]);
            });
    }

    public function lastStock()
    {
        $today = date('Y-m-d H:i:s');

        return $this->hasOne(BranchStock::class, 'branch_product_id', 'id')
            ->ofMany(['id' => 'max'], function ($query) use ($today) {
                return $query->where('created_at', '<=', $today);
            });
    }

    public function currentStock()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname($time, 'Y-m-d');

        return $this->hasOne(BranchStock::class, 'branch_product_id', 'id')
            ->ofMany(['id' => 'max'], function ($query) use ($dates) {
                return $query->whereBetween('date_from', [$dates->start, $dates->end]);
                // ->whereBetween('date_to', [$dates->start, $dates->end]);
            });
    }

    public function selectedStock()
    {
        return $this->hasOne(BranchStock::class, 'branch_product_id', 'id')->orderBy('id', 'desc');
    }

    public function historyStocks()
    {
        return $this->hasMany(BranchStock::class, 'branch_product_id', 'id');
    }

    // scope
    public function scopeByBranch(Builder $builder, $branch): Builder
    {
        $branchId = is_object($branch) ? $branch->id : ($branch ?? -1);
        return ($branchId <= 0) ? $builder : $builder->where('branch_id', '=', $branchId);
    }

    public function scopeByProduct(Builder $builder, $product): Builder
    {
        $productId = is_object($product) ? $product->id : ($product ?? 0);
        return $builder->where('product_id', '=', $productId);
    }

    // accessor
    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : null;
    }

    public function getAvailableStockAttribute()
    {
        return $this->currentStock ? $this->currentStock->total_stock : 0;
    }
}
