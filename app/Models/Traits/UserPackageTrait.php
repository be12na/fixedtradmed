<?php

namespace App\Models\Traits;

use App\Models\UserPackage;

trait UserPackageTrait
{
    // relationship
    public function userPackage()
    {
        return $this->hasOne(UserPackage::class, 'user_id', 'id')
            ->byType([TRANS_PKG_ACTIVATE, TRANS_PKG_UPGRADE])
            ->orderBy('package_id', 'desc')
            ->orderBy('type', 'desc')
            ->orderBy('id', 'desc');
    }

    public function packageTransactions()
    {
        return $this->hasMany(UserPackage::class, 'user_id', 'id');
    }

    public function repeatOrders()
    {
        return $this->hasMany(UserPackage::class, 'user_id', 'id')
            ->byType([TRANS_PKG_REPEAT_ORDER])
            ->orderBy('id', 'desc');
    }
    // relationship:end

    // accessor
    public function getHasRepeatOrderAttribute()
    {
        return ($this->repeatOrders()->byConfirmed()->count() > 0);
    }

    public function getHasPendingRepeatOrderAttribute()
    {
        return ($this->repeatOrders()->byStatus([MITRA_PKG_PENDING, MITRA_PKG_TRANSFERRED, MITRA_PKG_REJECTED])->count() > 0);
    }

    public function getCanRepeatOrderAttribute()
    {
        return ($this->userPackage && $this->userPackage->repeatable);
    }

    public function getShowPackageMenuAttribute()
    {
        return $this->is_active;
    }

    public function getShowPackageTransferAttribute()
    {
        if (!$this->show_package_menu) return false;

        $count = $this->repeatOrders()
            ->where(function ($where) {
                return $where->where(function ($where1) {
                    return $where1->byPending();
                })->orWhere(function ($where2) {
                    return $where2->byTransferred();
                });
            })
            ->count();

        return ($count > 0);
    }
    // accessor:end
}
