<?php

namespace App\Helpers\Traits\Mitra;

use App\Models\SettingMitraDiscount;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait NeoMitraDiscount
{
    public function listSettingMitraDiscount(): EloquentCollection
    {
        return SettingMitraDiscount::orderBy('mitra_type')->orderBy('min_purchase', 'asc')->get();
    }

    public function settingMitraDiscountById($id): SettingMitraDiscount|null
    {
        return SettingMitraDiscount::byId($id)->first();
    }

    public function settingMitraDiscountByMinPurchase(int $totalPurchase, $mitraType): SettingMitraDiscount|null
    {
        $mType = -1;
        if (!is_null($mitraType)) {
            $mType = intval($mitraType);
        }

        return SettingMitraDiscount::byType($mType)
            ->byMinPurchase($totalPurchase)
            ->orderBy('min_purchase', 'desc')
            ->first();
    }

    public function updateSettingMitraDiscount(array $values, SettingMitraDiscount $setting = null): SettingMitraDiscount
    {
        if (!is_null($setting)) $setting->delete();

        return SettingMitraDiscount::create($values);
    }

    // calculation discount
    public function getCalculationMitraDiscount(int $totalPurchase, SettingMitraDiscount $setting = null): int
    {
        $percent = (optional($setting)->percent ?? 0) / 100;

        return intval(floor($totalPurchase * $percent));
    }
}
