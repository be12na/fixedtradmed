<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class ProductDiscount extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mitra_type',
        'zone_id',
        'product_id',
        'discount_category',
        'min_qty',
        'discount',
        'set_by',
        'previous_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mitra_type' => 'integer',
        'discount_category' => 'integer',
        'min_qty' => 'integer',
        'discount' => 'integer',
    ];

    // scope
    public function scopeByMitraType(Builder $builder, int $type): Builder
    {
        return $builder->where('mitra_type', '=', $type);
    }

    public function scopeByZone(Builder $builder, string|int $zone): Builder
    {
        return $builder->where('zone_id', '=', $zone);
    }

    public function scopeByMinQty(Builder $builder, int $minQty): Builder
    {
        return $builder->where('min_qty', '<=', $minQty);
    }

    // accessor
    public function getCategoryNameAttribute()
    {
        return Arr::get(MITRA_DISCOUNT_CATEGORIES, $this->purchase_level);
    }
}
