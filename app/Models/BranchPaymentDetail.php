<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class BranchPaymentDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'branch_payment_id',
        'product_id',
        'branch_product_id',
        'branch_stock_id',
        'product_unit',
        'product_zone',
        'product_price',
        'product_qty',
        'total_price',
        'discount_id',
        'product_discount',
        'total_discount',
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
        'product_discount' => 'integer',
        'total_discount' => 'integer',
    ];

    // relations
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // accessor
    public function getProductUnitNameAttribute()
    {
        return strtoupper(Arr::get(PRODUCT_UNITS, $this->product_unit, ''));
    }
}
