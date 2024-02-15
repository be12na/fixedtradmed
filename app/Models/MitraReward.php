<?php

namespace App\Models;

use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraReward extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'point',
        'reward',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'point' => 'integer',
        'is_active' => 'boolean',
    ];

    // scope
    public function scopeByPoint(Builder $builder, int $point): Builder
    {
        return $builder->where('point', '=', $point);
    }
    // scope:end

    // accessor
    public function getNextRewardAttribute()
    {
        return static::query()->where('point', '>', $this->point)->byActive(true)->orderBy('point')->first();
    }

    public function getMaxPointAttribute()
    {
        $next = $this->next_reward;

        return $next ? $next->point - 1 : null;
    }
    // accessor:end
}
