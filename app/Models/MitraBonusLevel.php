<?php

namespace App\Models;

use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraBonusLevel extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelCodeTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'level',
        'code',
        'name',
        'bonus',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'integer',
        'level' => 'integer',
        'bonus' => 'float',
        'is_active' => 'boolean',
    ];

    // static
    public static function bonus_amount(int $type, int $level): float
    {
        $row = static::query()->byLevel($type, $level)->first();

        return $row ? $row->bonus : 0;
    }
    // static:end

    // scope
    public function scopeByType(Builder $builder, int $type): Builder
    {
        return $builder->where('type', '=', $type);
    }

    public function scopeByLevel(Builder $builder, int $type, int $level): Builder
    {
        return $builder->byType($type)->where('level', '=', $level);
    }
    // scope:end
}
