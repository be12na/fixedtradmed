<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class ProductReward extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'reward_type',
        'total_qty',
        'reward_value',
        'set_by',
        'previous_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reward_type' => 'integer',
        'total_qty' => 'integer',
    ];

    // scope
    public function scopeByTotalQty(Builder $builder, int $totalQty): Builder
    {
        return $builder->where('total_qty', '<=', $totalQty);
    }

    // accessor
    // accessor
    public function getRewardNameAttribute()
    {
        return Arr::get(MITRA_REWARD_CATEGORIES, $this->reward_type);
    }
}
