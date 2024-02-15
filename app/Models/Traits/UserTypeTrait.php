<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UserTypeTrait
{
    // scope
    public function scopeByGroupType(Builder $builder, $groupId, $typeId): Builder
    {
        return $builder->where('user_group', '=', $groupId)
            ->where('user_type', '=', $typeId);
    }

    public function scopeByMemberGroup(Builder $builder): Builder
    {
        return $builder->byGroupType(USER_GROUP_MEMBER, USER_TYPE_MEMBER);
    }

    public function scopeByMitraGroup(Builder $builder): Builder
    {
        return $builder->byGroupType(USER_GROUP_MEMBER, USER_TYPE_MITRA);
    }

    public function scopeByMitraType(Builder $builder, int $type): Builder
    {
        return $builder->byMitraGroup()->where('mitra_type', '=', $type);
    }

    public function scopeByMitraReseller(Builder $builder): Builder
    {
        return $builder->byMitraType(MITRA_TYPE_RESELLER);
    }

    public function scopeByMitraDropshipper(Builder $builder): Builder
    {
        return $builder->byMitraType(MITRA_TYPE_DROPSHIPPER);
    }

    public function scopeByMitraPremium(Builder $builder): Builder
    {
        return $builder->byMitraType(MITRA_TYPE_AGENT);
    }

    public function scopeByCustomerGroup(Builder $builder): Builder
    {
        return $builder->byGroupType(USER_GROUP_MEMBER, USER_TYPE_CUSTOMER);
    }

    // accessor
    public function getIsMainUserAttribute()
    {
        return ($this->user_group == USER_GROUP_MAIN);
    }

    public function getIsMainAdminUserAttribute()
    {
        return ($this->is_main_user && (in_array($this->user_type, [USER_TYPE_SUPER, USER_TYPE_MASTER, USER_TYPE_ADMIN])));
    }

    public function getIsSuperAdminUserAttribute()
    {
        return ($this->is_main_admin_user && ($this->user_type == USER_TYPE_SUPER));
    }

    public function getIsMasterAdminUserAttribute()
    {
        return ($this->is_main_admin_user && ($this->user_type == USER_TYPE_MASTER));
    }

    public function getIsStaffAdminUserAttribute()
    {
        return ($this->is_main_admin_user && ($this->user_type == USER_TYPE_ADMIN));
    }

    public function getIsMainFounderUserAttribute()
    {
        return ($this->is_main_user && ($this->user_type == USER_TYPE_FOUNDER));
    }

    public function getIsMemberUserAttribute()
    {
        return ($this->user_group == USER_GROUP_MEMBER);
    }

    public function getIsMemberMitraUserAttribute()
    {
        return $this->is_member_user && ($this->user_type == USER_TYPE_MITRA);
    }

    public function getIsMemberCustomerUserAttribute()
    {
        return $this->is_member_user && ($this->user_type == USER_TYPE_CUSTOMER);
    }

    public function getIsInternalMemberUserAttribute()
    {
        return $this->is_member_user && ($this->user_type == USER_TYPE_MEMBER);
    }

    public function getIsManagerUserAttribute()
    {
        return ($this->is_internal_member_user && ($this->position_int == USER_INT_MGR));
    }
}
