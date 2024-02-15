<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UserStatusTrait
{
    // scope
    public function scopeByStatus(Builder $builder, array|int $status): Builder
    {
        if (is_array($status)) return $builder->whereIn('user_status', $status);

        return $builder->where('user_status', '=', $status);
    }

    public function scopeByActivated(Builder $builder, bool $activated = null): Builder
    {
        return $builder->where('activated', '=', $activated ?? true);
    }
    // scope:end

    // accessor
    public function getIsActiveAttribute()
    {
        return $this->activated && ($this->user_status == USER_STATUS_ACTIVE);
    }

    public function getStatusTextAttribute()
    {
        return memberStatusText($this);
    }
    // accessor:end
}
