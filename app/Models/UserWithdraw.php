<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UserWithdraw extends Model
{
    use HasFactory;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wd_code',
        'user_id',
        'wd_date',
        'bank_code',
        'bank_name',
        'bank_acc_no',
        'bank_acc_name',
        'wd_bonus_type',
        'total_bonus',
        'fee',
        'total_transfer',
        'status',
        'status_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'wd_date' => 'date',
        'wd_bonus_type' => 'integer',
        'total_bonus' => 'float',
        'fee' => 'float',
        'total_transfer' => 'float',
        'status' => 'integer',
        'status_at' => 'datetime',
    ];

    // relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bonuses()
    {
        return $this->hasMany(UserBonus::class, 'wd_id', 'id');
    }
    // relationship:end

    // scope
    public function scopeByCode(Builder $builder, string $code): Builder
    {
        return $builder->where('wd_code', '=', $code);
    }

    public function scopeByBonusType(Builder $builder, array|int $type): Builder
    {
        if (is_array($type)) return $builder->whereIn('wd_bonus_type', $type);

        return $builder->where('wd_bonus_type', '=', $type);
    }

    public function scopeByTransStatus(Builder $builder, array|int $value, bool $not = null): Builder
    {
        if (is_null($not)) $not = false;

        if (is_array($value)) {
            return empty($value)
                ? $builder
                : ($not ? $builder->whereNotIn('status', $value) : $builder->whereIn('status', $value));
        }

        return $builder->where('status', $not ? '!=' : '=', $value);
    }

    public function scopeByUser(Builder $builder, User|int $user): Builder
    {
        return $builder->where('user_id', '=', ($user instanceof User) ? $user->id : $user);
    }

    public function scopeByDateBetween(Builder $builder, Carbon|string $startDate, Carbon|string $endDate): Builder
    {
        $formatStart = ($startDate instanceof Carbon) ? $startDate->format('Y-m-d') : $startDate;
        $formatEnd = ($endDate instanceof Carbon) ? $endDate->format('Y-m-d') : $endDate;

        return $builder->whereBetween('wd_date', [$formatStart, $formatEnd]);
    }
    // scope:end

    // accessor
    public function getBonusTypeNameAttribute()
    {
        return Arr::get(BONUS_MITRA_NAMES, $this->wd_bonus_type, '-');
    }

    public function getStatusNameAttribute()
    {
        return Arr::get(CLAIM_STATUS_LIST, $this->status, '-');
    }
    // accessor:end

    // static
    public static function makeCode(int $type, Carbon|string $date, array $exists = null): string
    {
        $cabonDate = ($date instanceof Carbon) ? $date : Carbon::createFromTimeString($date);
        $dateFormat = $cabonDate->format('ymd');

        $wdType = '';
        switch ($type) {
            case BONUS_MITRA_SPONSOR:
                $wdType = 'SP';
                break;
            case BONUS_MITRA_RO:
                $wdType = 'RO';
                break;
            case BONUS_MITRA_CASHBACK_RO:
                $wdType = 'CB';
                break;
            case BONUS_MITRA_CASHBACK_ACTIVATION:
                $wdType = 'CB';
                break;
            case BONUS_MITRA_GENERASI:
                $wdType = 'GN';
                break;
            case BONUS_MITRA_PRESTASI:
                $wdType = 'PR';
                break;
        }

        $random = str_pad(mt_rand(123456, 987654), 6, '0', STR_PAD_LEFT);
        $code = "WD{$wdType}-{$dateFormat}{$random}";

        if (is_null($exists)) {
            $wdExists = static::query()->byCode($code)->first();

            if ($wdExists) return static::makeCode($type, $date, $exists);
        } else {
            if (in_array($code, $exists)) return static::makeCode($type, $date, $exists);
        }

        return $code;
    }
}
