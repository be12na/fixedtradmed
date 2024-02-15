<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UserPackage extends Model
{
    use HasFactory;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'user_id',
        'package_id',
        'price',
        'digit',
        'total_price',
        'status',
        'bank_id',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
        'image',
        'transfer_at',
        'confirm_at',
        'reject_at',
        'cancel_at',
        'note',
        'type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => 'integer',
        'package_id' => 'integer',
        'price' => 'integer',
        'digit' => 'integer',
        'total_price' => 'integer',
        'status' => 'integer',
        'transfer_at' => 'datetime',
        'confirm_at' => 'datetime',
        'reject_at' => 'datetime',
        'cancel_at' => 'datetime',
    ];

    // scope
    public function scopeByType(Builder $builder, array|int $type): Builder
    {
        $type = is_array($type) ? $type : [$type];

        return $builder->whereIn('type', $type);
    }

    public function scopeByActivateType(Builder $builder): Builder
    {
        return $builder->where('type', '=', TRANS_PKG_ACTIVATE);
    }

    public function scopeByRepeatOrderType(Builder $builder): Builder
    {
        return $builder->where('type', '=', TRANS_PKG_REPEAT_ORDER);
    }

    public function scopeByUpgradeType(Builder $builder): Builder
    {
        return $builder->where('type', '=', TRANS_PKG_UPGRADE);
    }

    public function scopeByCode(Builder $builder, string $code): Builder
    {
        return $builder->where('code', '=', $code);
    }

    public function scopeByUser(Builder $builder, User|int $user): Builder
    {
        return $builder->where('user_id', '=', ($user instanceof User) ? $user->id : $user);
    }

    public function scopeByStatus(Builder $builder, array|int $status): Builder
    {
        return $builder->whereIn('status', is_array($status) ? $status : [$status]);
    }

    public function scopeByConfirmed(Builder $builder): Builder
    {
        return $builder->byStatus(MITRA_PKG_CONFIRMED);
    }

    public function scopeByTransferred(Builder $builder): Builder
    {
        return $builder->byStatus(MITRA_PKG_TRANSFERRED);
    }

    public function scopeByPending(Builder $builder): Builder
    {
        return $builder->byStatus(MITRA_PKG_PENDING);
    }
    // scope:end

    // static
    public static function makeCode(int|string $package): string
    {
        $date = date('ymd');
        $pkg = "0{$package}";
        $rnd = mt_rand(10001, 99999);
        $code = "{$pkg}{$date}{$rnd}";

        while (static::query()->byCode($code)->exists()) {
            $rnd = mt_rand(10001, 99999);
            $code = "{$pkg}{$date}{$rnd}";
        }

        return $code;
    }
    // static:end

    // accessor
    public function getPackageNameAttribute()
    {
        return Arr::get(MITRA_NAMES, $this->package_id);
    }

    public function getTypeNameAttribute()
    {
        return Arr::get(TRANS_PKG_TYPES, $this->type, '-');
    }

    public function getIsConfirmedAttribute()
    {
        return ($this->status == MITRA_PKG_CONFIRMED);
    }

    public function getIsTransferredAttribute()
    {
        return ($this->status == MITRA_PKG_TRANSFERRED);
    }

    public function getIsRejectedAttribute()
    {
        return ($this->status == MITRA_PKG_REJECTED);
    }

    public function getRepeatOrderAttribute()
    {
        return ($this->type == TRANS_PKG_REPEAT_ORDER);
    }

    public function getUpgradeAttribute()
    {
        return ($this->type == TRANS_PKG_UPGRADE);
    }

    public function getRepeatableAttribute()
    {
        return ($this->is_confirmed && in_array($this->package_id, [MITRA_TYPE_RESELLER, MITRA_TYPE_AGENT]));
    }
    // accessor:end
}
