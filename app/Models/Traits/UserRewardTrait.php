<?php

namespace App\Models\Traits;

use App\Models\MitraPoint;
use App\Models\MitraReward;
use App\Models\MitraRewardClaim;
use Illuminate\Database\Eloquent\Builder;

trait UserRewardTrait
{
    // relationship
    public function claimRewards()
    {
        return $this->hasMany(MitraRewardClaim::class, 'user_id', 'id')->onStatus()->with('reward');
    }

    public function pointsShoppingSelf()
    {
        return $this->hasMany(MitraPoint::class, 'user_id', 'id')->byType(POINT_TYPE_SHOPPING_SELF);
    }

    public function pointsActivateMember()
    {
        return $this->hasMany(MitraPoint::class, 'user_id', 'id')->byType(POINT_TYPE_ACTIVATE_MEMBER);
    }

    public function pointsRepeatOrderMember()
    {
        return $this->hasMany(MitraPoint::class, 'user_id', 'id')->byType(POINT_TYPE_REPEAT_ORDER);
    }

    public function points()
    {
        return $this->hasMany(MitraPoint::class, 'user_id', 'id');
    }
    // relationship:end

    // accessor
    public function getTotalPointAttribute()
    {
        return $this->points()->sum('point');
    }

    public function getTotalClaimedPointAttribute()
    {
        return $this->claimRewards->sum('reward.point');
    }

    public function getTotalRemainingPointAttribute()
    {
        $remaining = $this->total_point - $this->total_claimed_point;

        return ($remaining > 0) ? $remaining : 0;
    }
    // accessor:end

    // public
    public function enoughRewardPoint(MitraReward|int $reward): bool
    {
        $reward = ($reward instanceof MitraReward) ? $reward : MitraReward::byId($reward)->first();

        if (empty($reward) || ($reward->point <= 0)) return false;

        return ($this->total_remaining_point >= $reward->point);
    }

    public function dataClaimReward(MitraReward|int $reward): MitraRewardClaim|null
    {
        return $this->claimRewards
            ->where('reward_id', '=', ($reward instanceof MitraReward) ? $reward->id : $reward)
            ->first();
    }

    public function canClaimReward(MitraReward|int $reward): bool
    {
        return (empty($this->dataClaimReward($reward)) && $this->enoughRewardPoint($reward));
    }
    // public:end
}
