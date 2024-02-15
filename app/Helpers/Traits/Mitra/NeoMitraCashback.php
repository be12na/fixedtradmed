<?php

namespace App\Helpers\Traits\Mitra;

use App\Models\SettingMitraCashback;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

trait NeoMitraCashback
{
    public function listSettingMitraCashback(): EloquentCollection
    {
        return SettingMitraCashback::orderBy('mitra_type')->orderBy('min_purchase', 'asc')->get();
    }

    public function SettingMitraCashbackById($id): SettingMitraCashback|null
    {
        return SettingMitraCashback::byId($id)->first();
    }

    public function SettingMitraCashbackByMinPurchase(int $totalPurchase, $mitraType): SettingMitraCashback|null
    {
        $mType = -1;
        if (!is_null($mitraType)) {
            $mType = intval($mitraType);
        }

        return SettingMitraCashback::byType($mType)
            ->byMinPurchase($totalPurchase)
            ->orderBy('min_purchase', 'desc')
            ->first();
    }

    public function updateSettingMitraCashback(array $values, SettingMitraCashback $setting = null): SettingMitraCashback
    {
        if (!is_null($setting)) $setting->delete();

        return SettingMitraCashback::create($values);
    }

    // calculation discount
    public function getCalculationMitraCashback(int $totalPurchase, SettingMitraCashback $setting = null): int
    {
        $percent = (optional($setting)->percent ?? 0) / 100;

        return intval(floor($totalPurchase * $percent));
    }
}
