<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'endpoint',
        'vendor',
        'vendor_type',
        'user_key',
        'pass_key',
        'sms_token',
        'is_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'vendor_type' => 'integer',
        'is_token' => 'boolean',
    ];

    // scope
    public function scopeByVendor(Builder $builder, string $vendor): Builder
    {
        return $builder->where('vendor', '=', $vendor);
    }

    public function scopeByVendorType(Builder $builder, int $vendorType): Builder
    {
        return $builder->where('vendor_type', '=', $vendorType);
    }
    // scope:end

    // static
    public static function updateSetting(array $values, SmsSetting $old = null): static
    {
        if (!is_null($old)) {
            $old->update($values);

            return $old;
        }

        return static::create($values);
    }
    // static:end

    // accessor
    public function getEntryPointsAttribute()
    {
        return [
            'endpoint' => $this->endpoint,
            'user_key' => $this->user_key,
            'pass_key' => $this->pass_key,
            'sms_token' => $this->sms_token,
            'is_token' => $this->is_token,
        ];
    }
    // accessor:end
}
