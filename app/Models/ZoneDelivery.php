<?php

namespace App\Models;

use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoneDelivery extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'zone_id',
        'name',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // scope
    public function scopeByZone(Builder $builder, int $zoneId): Builder
    {
        return $builder->where('zone_id', '=', $zoneId);
    }
}
