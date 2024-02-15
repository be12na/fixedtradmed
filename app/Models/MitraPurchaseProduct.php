<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use stdClass;

class MitraPurchaseProduct extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mitra_purchase_id',
        'product_id',
        'branch_product_id',
        'branch_stock_id',
        'product_unit',
        'product_zone',
        'product_price',
        'product_zone_id',
        'is_promo',
        'product_zone_price',
        'product_qty',
        'total_price',
        'persen_mitra',
        'profit_mitra',
        'persen_foundation',
        'foundation',
        'discount_id',
        'discount',
        'coupon_id',
        'coupon_is_percent',
        'coupon_percent',
        'coupon_discount',
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
        'persen_mitra' => FloatCast::class,
        'profit_mitra' => 'integer',
        'persen_foundation' => FloatCast::class,
        'coupon_percent' => FloatCast::class,
        'foundation' => 'integer',
        'total_profit' => 'integer',
        'is_promo' => 'boolean',
        'is_active' => 'boolean',
        'is_v2' => 'boolean',
    ];

    // relation
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(MitraPurchase::class, 'mitra_purchase_id', 'id');
    }

    // scope
    public function scopeByProduct(Builder $builder, $product): Builder
    {
        $productId = is_object($product) ? $product->id : $product;

        return $builder->where('product_id', '=', $productId);
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

    public function getActualPriceAttribute()
    {
        return ($this->product_zone_price > 0) ? $this->product_zone_price : $this->product_price;
    }
}
