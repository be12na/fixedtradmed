<?php

namespace App\Models\Traits;

use App\Models\Structure;
use App\Models\User;
use Illuminate\Support\Collection;

trait NetworksTrait
{
    // public function getActiveTeamListAttribute()
    // {
    //     if (!$this->is_member_user) return collect();
    //     return $this->teamListFromUser($this, true, true, true);
    // }

    // public function getActiveTeamNonManagerListAttribute()
    // {
    //     if (!$this->is_member_user) return collect();
    //     return $this->teamListFromUser($this, true, true, false);
    // }

    // public function getInactiveTeamListAttribute()
    // {
    //     if (!$this->is_member_user) return collect();
    //     return $this->teamListFromUser($this, false, true, true);
    // }

    // private function teamListFromUser(User $user, bool $active, bool $self, bool $includeManager): Collection
    // {
    //     $result = collect();
    //     if (empty($user) || !$user->exists) return $result;

    //     if (!$self) $result->push($user);

    //     $downlines = $user->downlines;
    //     if (!empty($downlines)) {
    //         $downlines = $downlines->where('user_status', $active ? '=' : '!=', USER_STATUS_ACTIVE);
    //         foreach ($downlines as $downline) {
    //             if (($includeManager !== true) && ($downline->position_int == USER_INT_MGR)) continue;

    //             $nextDowlines = $this->teamListFromUser($downline, $active, false, $includeManager);
    //             if ($nextDowlines->isNotEmpty()) {
    //                 foreach ($nextDowlines as $next) {
    //                     $result->push($next);
    //                 }
    //             }
    //         }
    //     }

    //     return $result;
    // }

    public function structure()
    {
        return $this->hasOne(Structure::class, 'user_id', 'id')->with('user');
    }

    public function getUplinesAttribute()
    {
        return $this->structure ? $this->structure->ancestors()->get()->pluck('user')->values() : collect();
    }

    public function getUplineAttribute()
    {
        return $this->uplines->first();
    }

    public function getDownlinesAttribute()
    {
        return $this->structure ? $this->structure->children()->get()->pluck('user')->values() : collect();
    }

    public function getActiveTeamListAttribute()
    {
        if (!$this->is_member_user) return collect();
        return $this->teamListFromUser(true, true, true);
    }

    public function getActiveTeamNonManagerListAttribute()
    {
        if (!$this->is_member_user) return collect();
        return $this->teamListFromUser(true, true, false);
    }

    public function getActiveTeamWithoutSelfNonManagerListAttribute()
    {
        if (!$this->is_member_user) return collect();
        return $this->teamListFromUser(true, false, false);
    }

    public function getInactiveTeamListAttribute()
    {
        if (!$this->is_member_user) return collect();
        return $this->teamListFromUser(false, true, true);
    }

    private function teamListFromUser(bool $active, bool $self, bool $includeManager): Collection
    {
        if (empty($this->structure)) return collect();
        $userNetworks = ($self ? $this->structure->descendantsWithSelf() : $this->structure->descendants())->get()->pluck('user');

        if (!$includeManager) {
            $managers = $userNetworks->where('position_int', '=', USER_INT_MGR)->where('id', '!=', $this->id)->values();
            $excludeIds = [];
            foreach ($managers as $manager) {
                $excludeIds = array_merge(
                    $excludeIds,
                    ($manager->structure ? $manager->structure->descendantsWithSelf() : collect())
                        ->pluck('user_id')
                        ->toArray()
                );
            }

            $userNetworks = $userNetworks->reject(function ($user) use ($excludeIds) {
                return in_array($user->id, $excludeIds);
            });
        }

        return $userNetworks->where('user_status', $active ? '=' : '!=', USER_STATUS_ACTIVE)->values();
    }
}
