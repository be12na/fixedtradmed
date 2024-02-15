<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingBonusTeam extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'position_id',
        'percent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position_id' => 'integer',
        'percent' => FloatCast::class,
    ];

    // scope
    public function scopeByPosition(Builder $builder, int $position): Builder
    {
        return $builder->where('position_id', '=', $position);
    }

    // accessor
    public function getPositionAttribute()
    {
        $position = app('appStructure')->getPositionById(true, $this->position_id);

        return optional($position);
    }
}
