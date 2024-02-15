<?php

namespace App\Models\Traits;

use App\Models\Branch;
use App\Models\BranchMember;

trait UserBranchTrait
{
    // relation
    public function branches()
    {
        return $this->hasMany(BranchMember::class, 'user_id', 'id')->with(['branch']);
    }

    public function activeBranches()
    {
        return $this->hasMany(BranchMember::class, 'user_id', 'id')
            ->byActive()
            ->whereHas('branch', function ($y) {
                if (isAppV2()) {
                    $y = $y->whereHas('zone');
                }
                return $y->byActive();
            })
            ->with(['branch' => function ($y) {
                if (isAppV2()) {
                    $y = $y->whereHas('zone');
                }
                return $y->byActive()->with('distributors');
            }]);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    // accessor
    public function getIsMemberDistributorUserAttribute()
    {
        $branchPositionIds = $this->activeBranches->pluck('position_ext')->toArray();

        return ($this->is_manager_user && in_array(USER_EXT_DIST, $branchPositionIds));
    }

    public function getIsBranchManagerAttribute()
    {
        return $this->is_member_distributor_user;
    }

    public function getBranchesStockAttribute()
    {
        if (!$this->is_branch_manager) return collect();

        $stockBranches = $this->activeBranches
            ->where('position_ext', '=', USER_EXT_DIST)
            ->where('manager_type', '=', USER_BRANCH_MANAGER_QUARTERBACK);

        $result = collect();
        foreach ($stockBranches as $branch) {
            $result->push($branch->branch);
        }

        return $result;
    }

    public function getCanStockOpnameAttribute()
    {
        return $this->branches_stock->isNotEmpty();
    }

    public function getUserZoneAttribute()
    {
        // if (!empty($this->branch_id)) return $this->branch->wilayah;

        return ZONE_WEST;
    }

    public function getUserZoneNameAttribute()
    {
        // if (!empty($this->branch_id)) return $this->branch->zone_name;

        return null;
    }
}
