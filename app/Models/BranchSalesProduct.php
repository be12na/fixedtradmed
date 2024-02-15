<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Models\Traits\ModelActiveTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class BranchSalesProduct extends Model
{
    use HasFactory, SoftDeletes;
    use ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_sale_id',
        'product_id',
        'branch_product_id',
        'branch_stock_id',
        'product_unit',
        'product_zone',
        'product_price',
        'product_qty',
        'total_price',
        'persen_crew',
        'profit_crew',
        'persen_foundation',
        'foundation',
        'total_profit',
        'is_active',
        'note',
        'is_v2',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_unit' => 'integer',
        'product_zone' => 'integer',
        'product_price' => 'integer',
        'product_qty' => 'integer',
        'total_price' => 'integer',
        'persen_crew' => FloatCast::class,
        'profit_crew' => 'integer',
        'persen_foundation' => FloatCast::class,
        'foundation' => 'integer',
        'total_profit' => 'integer',
        'is_active' => 'boolean',
        'is_v2' => 'boolean',
    ];

    // relation
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // scope
    public function scopeByProduct(Builder $builder, $product): Builder
    {
        $productId = is_object($product) ? $product->id : ($product ?? 0);
        return $builder->where('product_id', '=', $productId);
    }

    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : null;
    }

    // accessor
    public function getProductUnitNameAttribute()
    {
        return strtoupper(Arr::get(PRODUCT_UNITS, $this->product_unit, ''));
    }

    public function getQtyBoxAttribute()
    {
        return ($this->product_unit == PRODUCT_UNIT_BOX) ? $this->product_qty : 0;
    }

    public function getQtyPcsAttribute()
    {
        return ($this->product_unit == PRODUCT_UNIT_PCS) ? $this->product_qty : 0;
    }
}
