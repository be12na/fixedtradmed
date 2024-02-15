<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingMitraDiscount extends Model
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
        'min_purchase',
        'percent',
        'set_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'mitra_type' => 'integer',
        'min_purchase' => 'integer',
        'percent' => FloatCast::class,
    ];

    // scope
    public function scopeByType(Builder $builder, int $type): Builder
    {
        return $builder->where('mitra_type', '=', $type);
    }

    public function scopeByMinPurchase(Builder $builder, int $min): Builder
    {
        return $builder->where('min_purchase', '<=', $min);
    }
}
