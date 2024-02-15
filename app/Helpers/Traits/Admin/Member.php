<?php

namespace App\Helpers\Traits\Admin;

use App\Models\User;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait Member
{
    private function baseAdminQueryMember(array|string $select = null): QueryBuilder
    {
        $result = DB::table('users')
            ->where('users.user_group', '=', USER_GROUP_MEMBER);

        if (!empty($select)) {
            if (is_array($select)) {
                $result = $result->select($select);
            } else {
                $result = $result->selectRaw($select);
            }
        }

        return $result;
    }

    public function countDistributor(): int
    {
        // $user = Auth::user();
        // if (!$user->is_main_user) return 0;

        // $query = $this->baseAdminQueryMember()
        //     ->join('branch_members', function ($join) {
        //         $join->on('branch_members.user_id', '=', 'users.id')
        //             ->where('branch_members.position_ext', '=', USER_EXT_DIST)
        //             ->where('branch_members.is_active', '=', true);
        //     })
        //     ->selectRaw('
        //     users.id,
        //     COUNT(branch_members.id) as tmp
        //     ')
        //     ->where('users.user_status', '=', USER_STATUS_ACTIVE)
        //     ->where('users.user_type', '=', USER_TYPE_MEMBER)
        //     ->where('users.position_int', '=', USER_INT_MGR)
        //     ->groupBy('users.id');

        // return DB::table(DB::raw('(' . $query->toSql() . ') as agent'))
        //     ->mergeBindings($query)
        //     ->count();

        $user = Auth::user();
        if (!$user->is_main_user) return 0;

        return $this->baseAdminQueryMember('users.id')
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_type', '=', USER_TYPE_MITRA)
            ->where('users.position_ext', '=', USER_EXT_MTR)
            ->where('users.mitra_type', '=', MITRA_TYPE_AGENT)
            ->whereNull('users.position_int')
            ->count();
    }

    public function countAgent()
    {
        // $user = Auth::user();
        // if (!$user->is_main_user) return 0;

        // $query = $this->baseAdminQueryMember()
        //     ->join('branch_members', function ($join) {
        //         $join->on('branch_members.user_id', '=', 'users.id')
        //             ->where('branch_members.position_ext', '=', USER_EXT_AG)
        //             ->where('branch_members.is_active', '=', true);
        //     })
        //     ->selectRaw('
        //     users.id,
        //     COUNT(branch_members.id) as tmp
        //     ')
        //     ->where('users.user_status', '=', USER_STATUS_ACTIVE)
        //     ->where('users.user_type', '=', USER_TYPE_MEMBER)
        //     ->where('users.position_int', '=', USER_INT_MGR)
        //     ->groupBy('users.id');

        // return DB::table(DB::raw('(' . $query->toSql() . ') as agent'))
        //     ->mergeBindings($query)
        //     ->count();

        $user = Auth::user();
        if (!$user->is_main_user) return 0;

        return $this->baseAdminQueryMember('users.id')
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_type', '=', USER_TYPE_MITRA)
            ->where('users.position_ext', '=', USER_EXT_MTR)
            ->where('users.mitra_type', '=', MITRA_TYPE_RESELLER)
            ->whereNull('users.position_int')
            ->count();
    }

    public function countReseller()
    {
        $user = Auth::user();
        if (!$user->is_main_user) return 0;

        return $this->baseAdminQueryMember('users.id')
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_type', '=', USER_TYPE_MITRA)
            ->where('users.position_ext', '=', USER_EXT_MTR)
            ->where('users.mitra_type', '=', MITRA_TYPE_DROPSHIPPER)
            ->whereNull('users.position_int')
            ->count();
    }

    public function countMitra()
    {
        $user = Auth::user();
        if (!$user->is_main_user) return 0;

        return $this->baseAdminQueryMember('users.id')
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_type', '=', USER_TYPE_MITRA)
            ->where('users.position_ext', '=', USER_EXT_MTR)
            ->whereNull('users.position_int')
            ->count();
    }

    /////////////////// NETWORK ///////////////////
    // private Collection $allActiveMember;
    // private function getAllActiveMember(): Collection
    // {
    //     if (isset($this->allActiveMember)) return $this->allActiveMember;

    //     return $this->allActiveMember = $this->baseAdminQueryMember([
    //         'users.id', 'users.username', 'users.name',
    //         'users.email', 'users.referral_id', 'users.is_login',
    //         'users.user_group', 'users.user_type', 'users.position_int',
    //         'users.upline_id', 'users.level_id', 'users.branch_id',
    //         'users.branch_manager', 'users.user_status', 'users.status_at', 'users.activated',
    //         'users.activated_at', 'users.phone', 'users.sub_domain',
    //         'users.created_at',
    //     ])->where('user_type', '=', USER_TYPE_MEMBER)
    //         ->whereNotNull('position_int')
    //         ->orderBy('users.id')
    //         ->get();
    // }

    // private Collection $allActiveMitra;
    // private function getAllActiveMitra(): Collection
    // {
    //     if (isset($this->allActiveMitra)) return $this->allActiveMitra;

    //     return $this->allActiveMitra = $this->baseAdminQueryMember([
    //         'users.id', 'users.username', 'users.name',
    //         'users.email', 'users.referral_id', 'users.is_login',
    //         'users.user_group', 'users.user_type', 'users.position_int',
    //         'users.position_ext',
    //         'users.upline_id', 'users.level_id', 'users.branch_id',
    //         'users.branch_manager', 'users.user_status', 'users.status_at', 'users.activated',
    //         'users.activated_at', 'users.phone', 'users.sub_domain',
    //         'users.created_at',
    //     ])->where('user_status', '=', USER_STATUS_ACTIVE)
    //         ->where('user_type', '=', USER_TYPE_MITRA)
    //         ->where('position_ext', '=', USER_EXT_MTR)
    //         ->whereNull('position_int')
    //         ->get();
    // }

    public function getMemberUplines(User $member): Collection
    {
        $result = collect();
        if (empty($member)) return $result;

        // $allMembers = $this->getAllActiveMember();

        // if ($member->user_type == USER_TYPE_MITRA) {
        //     $upline = $allMembers->where('id', '=', $member->referral_id)->first();
        // } else {
        //     $upline = $allMembers->where('id', '=', $member->id)->first();
        // }

        // while (!empty($upline = $allMembers->where('id', '=', $upline->upline_id)->first())) {
        //     $result->push($upline);
        // }

        // return $result;

        // return ($member->is_member_mitra_user)
        //     ? $member->referral->structure->ancestorsWithSelf()->get()->pluck('user')->values()
        //     : $member->structure->ancestors()->get()->pluck('user')->values();

        return $member->referral ? $member->referral->structure->ancestorsWithSelf()->get()->pluck('user')->values() : collect();
    }
}
