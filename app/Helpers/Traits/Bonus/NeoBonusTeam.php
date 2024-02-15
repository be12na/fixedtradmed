<?php

namespace App\Helpers\Traits\Bonus;

use App\Models\BranchSale;
use App\Models\BranchTransfer;
use App\Models\SettingBonusTeam;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait NeoBonusTeam
{
    public function listSettingBonusTeam(): EloquentCollection
    {
        return SettingBonusTeam::orderBy('position_id')->get();
    }

    public function settingBonusTeamById($id): SettingBonusTeam|null
    {
        return SettingBonusTeam::byId($id)->first();
    }

    public function updateSettingBonusTeam(array $values, SettingBonusTeam $setting = null): SettingBonusTeam
    {
        if (!is_null($setting)) $setting->delete();

        return SettingBonusTeam::create($values);
    }

    // calculation bonus team
    public function getCalculationBonusTeam(int $totalOmzet, SettingBonusTeam $setting = null)
    {
        $percent = (optional($setting)->percent ?? 0) / 100;

        return $totalOmzet * $percent;
    }

    public function neoBonusTeam(BranchTransfer $branchTransfer, BranchSale $branchSale, User $salesman, Collection $settings, Collection $uplines): array
    {
        $result = [];
        if (!$salesman->is_internal_member_user) return $result;

        $positionIds = $settings->pluck('position_id')->toArray();
        $uplinesTeam = $uplines->whereIn('position_int', $positionIds);
        $baseBonus = $branchSale->sum_total_price;
        $bonusDate = $branchSale->getRawOriginal('sale_date');

        foreach ($uplinesTeam as $team) {
            $setting = $settings->where('position_id', '=', $team->position_int)->first();
            if (empty($setting)) continue;

            $percentBonus = $setting->percent;
            $bonusAmount = floor($baseBonus * $percentBonus / 100);

            $itemBonus = [
                'bonus_type' => BONUS_TYPE_TEAM,
                'bonus_date' => $bonusDate,
                'user_id' => $team->id,
                'position_id' => $team->position_int,
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

        return $result;
    }

    public function baseQueryBonusTeam(): Builder
    {
        return DB::table('bonus_members')->where('bonus_members.bonus_type', '=', BONUS_TYPE_TEAM);
    }
}
