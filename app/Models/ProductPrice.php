<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPrice extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'zone_id',
        'product_id',
        'normal_price',
        'normal_retail_price',
        'mitra_price',
        'mitra_promo_price',
        'mitra_basic_bonus',
        'mitra_premium_bonus',
        'distributor_bonus',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'normal_price' => 'integer',
        'mitra_price' => 'integer',
        'normal_retail_price' => 'integer',
        'mitra_promo_price' => 'integer',
        'mitra_basic_bonus' => 'integer',
        'mitra_premium_bonus' => 'integer',
        'distributor_bonus' => 'integer',
    ];

    // relation
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id')->byActive();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    // scope
    public function scopeByZone(Builder $builder, Zone|int $zone = null): Builder
    {
        return $builder->where('zone_id', '=', ($zone instanceof Zone) ? $zone->id : $zone);
    }
}
