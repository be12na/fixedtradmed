<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UserReferralTrait
{
    // relationship
    public function referral()
    {
        return $this->belongsTo(static::class, 'referral_id', 'id'); //->byMemberGroup();
    }

    public function myMitra()
    {
        return $this->hasMany(static::class, 'referral_id', 'id'); //->byMitraGroup();
    }

    public function myMembers()
    {
        return $this->hasMany(static::class, 'referral_id', 'id'); //->byMitraGroup();
    }
    // relationship:end

    // scope
    public function scopeByReferral(Builder $builder, self|int $user): Builder
    {
        return $builder->where('referral_id', '=', ($user instanceof self) ? $user->id : $user);
    }
    // scope:end

    // accessor
    public function getTotalMemberAttribute()
    {
        return $this->myMitra()->count();
    }

    public function getTotalMemberShoppingAttribute()
    {
        return $this->myMitra()->whereHas('shoppings', function ($shopping) {
            return $shopping->byApproved();
        })->count();
    }

    public function getTotalMemberThisMonthAttribute()
    {
        $dateYm = date('Y-m');

        return $this->myMitra()->whereRaw("DATE_FORMAT(activated_at, '%Y-%m') = '{$dateYm}'")->count();
    }

    public function getTotalMemberTodayAttribute()
    {
        $dateYmd = date('Y-m-d');

        return $this->myMitra()->whereRaw("DATE_FORMAT(activated_at, '%Y-%m-%d') = '{$dateYmd}'")->count();
    }
    // accessor:end
}
