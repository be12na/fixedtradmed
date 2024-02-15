<?php

namespace App\Helpers\Traits\Bonus;

use App\Models\BranchSale;
use App\Models\BranchTransfer;
use App\Models\SettingOverride;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait NeoBonusOverride
{
    public function listSettingOverride(): EloquentCollection
    {
        return SettingOverride::orderBy('level_id')->get();
    }

    public function settingOverrideById($id): SettingOverride|null
    {
        return SettingOverride::byId($id)->first();
    }

    public function updateSettingOverride(array $values, SettingOverride $setting = null): SettingOverride
    {
        if (!is_null($setting)) $setting->delete();

        return SettingOverride::create($values);
    }

    // calculation bonus override
    public function getCalculationBonusOverride(int $totalOmzet, SettingOverride $setting = null)
    {
        $percent = (optional($setting)->percent ?? 0) / 100;

        return $totalOmzet * $percent;
    }

    public function neoBonusOverride(BranchTransfer $branchTransfer, BranchSale $branchSale, User $salesman, Collection $settings, Collection $uplines): array
    {
        $result = [];
        $startPositionId = USER_INT_MGR;
        $level = 1;
        $maxLevel = $settings->max('level_id');
        $currentPositionId = null;
        $baseBonus = $branchSale->sum_total_price;
        $bonusDate = $branchSale->getRawOriginal('sale_date');

        $mgrStarted = ($salesman->position_int == $startPositionId);

        foreach ($uplines as $upline) {
            if ($level > $maxLevel) break;
            if (($positionId = $upline->position_int) > $startPositionId) continue;

            if (!$mgrStarted) {
                $mgrStarted = ($positionId == $startPositionId);
                continue;
            }

            if ($positionId == $currentPositionId) {
                $level += 1;
                continue;
            }

            $currentPositionId = $positionId;
            if (!empty($setting = $settings->where('level_id', '=', $level)->first())) {
                $percentBonus = $setting->percent;
                $bonusAmount = floor($baseBonus * $percentBonus / 100);

                $itemBonus = [
                    'bonus_type' => BONUS_TYPE_OVERRIDE,
                    'bonus_date' => $bonusDate,
                    'user_id' => $upline->id,
                    'position_id' => $upline->position_int,
                    'level_id' => $level,
                    'is_internal' => true,
                    'bonus_base' => $baseBonus,
                    'bonus_percent' => $percentBonus,
                    'bonus_amount' => $bonusAmount,
                    'setting_id' => $setting->id,
                    'transfer_id' => $branchTransfer->id,
                    'transaction_id' => $branchSale->id,
                    'qty_box' => $branchSale->sum_quantity_box,
                    'qty_pcs' => $branchSale->sum_quantity_pcs,
                    'details' => [
                        'setting_id' => $setting->id,
                        'transfer_id' => $branchTransfer->id,
                        'transaction_id' => $branchSale->id,
                        'qty_box' => $branchSale->sum_quantity_box,
                        'qty_pcs' => $branchSale->sum_quantity_pcs,
                    ],
                ];

                $result[] = $itemBonus;
            }

            $level += 1;
        }

        return $result;
    }

    public function baseQueryBonusOverride(): Builder
    {
        return DB::table('bonus_members')->where('bonus_members.bonus_type', '=', BONUS_TYPE_OVERRIDE);
    }
}
