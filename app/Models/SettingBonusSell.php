<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingBonusSell extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'percent',
        'is_direct',
        'target_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'percent' => FloatCast::class,
        'is_direct' => 'boolean',
        'target_id' => 'integer',
    ];

    public function scopeByDirect(Builder $builder, bool $isDirect = null): Builder
    {
        return $builder->where('is_direct', '=', is_null($isDirect) ? false : $isDirect);
    }

    public function scopeByTarget(Builder $builder, int $targetId = null): Builder
    {
        return $builder->where('target_id', '=', is_null($targetId) ? BONUS_TYPE_SALE : $targetId);
    }
}
