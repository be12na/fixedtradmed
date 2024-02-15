<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseQuota extends Model
{
    use HasFactory,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'package_id',
        'quota',
        'point',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'package_id' => 'integer',
        'quota' => 'integer',
        'point' => 'integer',
    ];

    // scope
    public function scopeByPackage(Builder $builder, int $packageId): Builder
    {
        return $builder->where('package_id', '=', $packageId);
    }
    // scope:end
}
