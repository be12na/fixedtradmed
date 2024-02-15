<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingRoyalty extends Model
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
        'is_internal',
        'is_network',
        'percent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position_id' => 'integer',
        'is_internal' => 'boolean',
        'is_network' => 'boolean',
        'percent' => FloatCast::class,
    ];

    // scope
    public function scopeByCategory(Builder $builder, bool $internal): Builder
    {
        return $builder->where('is_internal', '=', $internal);
    }

    public function scopeByInternalPosition(Builder $builder, int $position): Builder
    {
        return $builder->byCategory(true)->where('position_id', '=', $position);
    }

    public function scopeByExternalPosition(Builder $builder, int $position): Builder
    {
        return $builder->byCategory(false)->where('position_id', '=', $position);
    }

    // accessor
    public function getTargetOmzetAttribute()
    {
        return $this->is_network ? 'Jaringan' : 'Nasional';
    }

    public function getPositionAttribute()
    {
        $position = app('appStructure')->getPositionById(true, $this->position_id);

        return optional($position);
    }
}
