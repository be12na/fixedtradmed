<?php

namespace App\Models\Traits;

use App\Models\OmzetMember;

trait UserOmzetTrait
{
    public function omzetFromMember()
    {
        return $this->hasMany(OmzetMember::class, 'user_id', 'id')->byFromMitra(false);
    }

    public function omzetFromMitra()
    {
        return $this->hasMany(OmzetMember::class, 'user_id', 'id')->byFromMitra(true);
    }

    public function getNetworkOmzetWithoutSelfAttribute()
    {
        return !$this->is_member_user ? 0 : $this->omzetFromMember()->where('salesman_id', '!=', $this->id)->sum('omzet');
    }

    public function getNetworkOmzetWithSelfAttribute()
    {
        return !$this->is_member_user ? 0 : $this->omzetFromMember()->sum('omzet');
    }

    public function getNetworkOmzetFromMitraAttribute()
    {
        return !$this->is_member_user ? 0 : $this->omzetFromMitra()->sum('omzet');
    }

    public function getMitraOmzetAttribute()
    {
        return !$this->is_member_mitra_user ? 0 : $this->omzetFromMitra()->sum('omzet');
    }
}
