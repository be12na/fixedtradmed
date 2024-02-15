<?php

namespace App\Models;

use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
        'bank_type',
        'is_active',
        'active_at',
        'active_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bank_type' => 'integer',
        'is_active' => 'boolean',
        'active_at' => 'datetime'
    ];

    // static
    public static function getMainBanks(bool $activeOnly): Collection
    {
        $query = static::byType(OWNER_BANK_MAIN);

        if ($activeOnly === true) {
            $query = $query->byActive(true);
        }

        return $query
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->orderBy('account_no')
            ->get();
    }

    public static function getMemberBanks(User $user, bool $activeOnly): Collection
    {
        $query = static::byType(OWNER_BANK_MEMBER);

        if ($activeOnly === true) {
            $query = $query->byActive(true);
        }

        return $query
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->orderBy('account_no')
            ->get();
    }

    // scope
    public function scopeByType(Builder $builder, int $type): Builder
    {
        return $builder->where('bank_type', '=', $type);
    }

    public function scopeByUser(Builder $builder, $user): Builder
    {
        $userId = is_object($user) ? $user->id : $user;
        return $builder->where('user_id', '=', $userId);
    }
}
