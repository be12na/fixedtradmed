<?php

namespace App\Helpers\Traits\Bonus;

use App\Models\BranchSale;
use App\Models\BranchTransfer;
use App\Models\MitraPurchase;
use App\Models\SettingRoyalty;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait NeoBonusRoyalty
{
    public function listSettingRoyalty(bool $internal): EloquentCollection
    {
        return SettingRoyalty::byCategory($internal)->orderBy('position_id')->get();
    }

    public function getSettingRoyaltyById(bool $internal, $id): SettingRoyalty|null
    {
        return SettingRoyalty::byCategory($internal)->byId($id)->first();
    }

    public function updateSettingRoyalty(array $values, SettingRoyalty $setting = null): SettingRoyalty
    {
        if (!is_null($setting)) $setting->delete();

        return SettingRoyalty::create($values);
    }

    // calculation bonus royalty
    public function getCalculationBonusRoyalty(int $totalOmzet, SettingRoyalty $setting = null)
    {
        $percent = (optional($setting)->percent ?? 0) / 100;

        return $totalOmzet * $percent;
    }

    public function neoBonusRoyalty(BranchTransfer $branchTransfer, BranchSale $branchSale, Collection $internalRoyalties, Collection $uplines): array
    {
        $result = [];
        $baseBonus = $branchSale->sum_total_price;
        $bonusDate = $branchSale->getRawOriginal('sale_date');

        // internal
        $internalRoyaltyPositionIds = $internalRoyalties->pluck('position_id')->toArray();
        $uplinesRoyalty = $uplines->whereIn('position_int', $internalRoyaltyPositionIds)
            ->whereNotNull('position_int');

        foreach ($uplinesRoyalty as $memberInternal) {
            if (!$memberInternal->activated || ($memberInternal->user_status != USER_STATUS_ACTIVE)) continue;

            $internalRoyalty = $internalRoyalties->where('position_id', '=', $memberInternal->position_int)->first();
            if (empty($internalRoyalty)) continue;

            $percentBonus = $internalRoyalty->percent;
            $bonusAmount = floor($baseBonus * $percentBonus / 100);

            $itemBonus = [
                'bonus_type' => BONUS_TYPE_ROYALTY,
                'bonus_date' => $bonusDate,
                'user_id' => $memberInternal->id,
                'position_id' => $memberInternal->position_int,
                'is_internal' => true,
                'bonus_base' => $baseBonus,
                'bonus_percent' => $percentBonus,
                'bonus_amount' => $bonusAmount,
                'setting_id' => $internalRoyalty->id,
                'transfer_id' => $branchTransfer->id,
                'transaction_id' => $branchSale->id,
                'qty_box' => $branchSale->sum_quantity_box,
                'qty_pcs' => $branchSale->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $internalRoyalty->id,
                    'transfer_id' => $branchTransfer->id,
                    'transaction_id' => $branchSale->id,
                    'qty_box' => $branchSale->sum_quantity_box,
                    'qty_pcs' => $branchSale->sum_quantity_pcs,
                ],
            ];

            $result[] = $itemBonus;
        }
        // khusus team support
        $supportRoyalty = $internalRoyalties->where('position_id', '=', USER_INT_NONE)->first();
        if (!empty($supportRoyalty)) {
            $percentBonus = $supportRoyalty->percent;
            $bonusAmount = floor($baseBonus * $percentBonus / 100);

            $itemBonus = [
                'bonus_type' => BONUS_TYPE_ROYALTY,
                'bonus_date' => $bonusDate,
                'user_id' => 0,
                'position_id' => USER_INT_NONE,
                'is_internal' => true,
                'bonus_base' => $baseBonus,
                'bonus_percent' => $percentBonus,
                'bonus_amount' => $bonusAmount,
                'setting_id' => $internalRoyalty->id,
                'transfer_id' => $branchTransfer->id,
                'transaction_id' => $branchSale->id,
                'qty_box' => $branchSale->sum_quantity_box,
                'qty_pcs' => $branchSale->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $internalRoyalty->id,
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

    public function neoBonusRoyaltyMitra(MitraPurchase $purchase, Collection $externalRoyalties, Collection $originalUplines): array
    {
        $result = [];
        $uplines = clone $originalUplines;
        $baseBonus = $purchase->total_purchase;
        $bonusDate = $purchase->getRawOriginal('purchase_date');
        $salesman = $purchase->mitra;
        $referral = $salesman->referral;

        $externalRoyalty = $externalRoyalties->where('position_id', '=', USER_INT_MGR)->first();

        // referral
        if (!empty($externalRoyalty)) {
            $percentBonus = $externalRoyalty->percent;
            $bonusAmount = floor($baseBonus * $percentBonus / 100);

            $result[] = [
                'bonus_type' => BONUS_TYPE_ROYALTY,
                'bonus_date' => $bonusDate,
                'user_id' => $referral->id,
                'position_id' => USER_INT_MGR,
                'is_internal' => false,
                'bonus_base' => $baseBonus,
                'bonus_percent' => $percentBonus,
                'bonus_amount' => $bonusAmount,
                'setting_id' => $externalRoyalty->id,
                'transfer_id' => $purchase->id,
                'transaction_id' => $purchase->id,
                'qty_box' => $purchase->sum_quantity_box,
                'qty_pcs' => $purchase->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $externalRoyalty->id,
                    'transfer_id' => $purchase->id,
                    'transaction_id' => $purchase->id,
                    'qty_box' => $purchase->sum_quantity_box,
                    'qty_pcs' => $purchase->sum_quantity_pcs,
                ],
            ];
        }

        $externalRoyalties = $externalRoyalties->reject(function ($value) {
            return ($value->position_id == USER_INT_MGR);
        });

        $externalRoyaltyPositionIds = $externalRoyalties->pluck('position_id')->toArray();

        $uplinesRoyalty = $uplines->whereIn('position_int', $externalRoyaltyPositionIds)
            ->whereNotNull('position_int');

        foreach ($uplinesRoyalty as $upline) {
            if (!$upline->activated || ($upline->user_status != USER_STATUS_ACTIVE)) continue;

            $externalRoyalty = $externalRoyalties->where('position_id', '=', $upline->position_int)->first();
            if (empty($externalRoyalty)) continue;

            $percentBonus = $externalRoyalty->percent;
            $bonusAmount = floor($baseBonus * $percentBonus / 100);

            $itemBonus = [
                'bonus_type' => BONUS_TYPE_ROYALTY,
                'bonus_date' => $bonusDate,
                'user_id' => $upline->id,
                'position_id' => $upline->position_int,
                'is_internal' => false,
                'bonus_base' => $baseBonus,
                'bonus_percent' => $percentBonus,
                'bonus_amount' => $bonusAmount,
                'setting_id' => $externalRoyalty->id,
                'transfer_id' => $purchase->id,
                'transaction_id' => $purchase->id,
                'qty_box' => $purchase->sum_quantity_box,
                'qty_pcs' => $purchase->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $externalRoyalty->id,
                    'transfer_id' => $purchase->id,
                    'transaction_id' => $purchase->id,
                    'qty_box' => $purchase->sum_quantity_box,
                    'qty_pcs' => $purchase->sum_quantity_pcs,
                ],
            ];

            $result[] = $itemBonus;
        }
        // khusus team support
        $supportRoyalty = $externalRoyalties->where('position_id', '=', USER_EXT_NONE)->first();
        if (!empty($supportRoyalty)) {
            $percentBonus = $supportRoyalty->percent;
            $bonusAmount = floor($baseBonus * $percentBonus / 100);

            $itemBonus = [
                'bonus_type' => BONUS_TYPE_ROYALTY,
                'bonus_date' => $bonusDate,
                'user_id' => 0,
                'position_id' => USER_EXT_NONE,
                'is_internal' => false,
                'bonus_base' => $baseBonus,
                'bonus_percent' => $percentBonus,
                'bonus_amount' => $bonusAmount,
                'setting_id' => $supportRoyalty->id,
                'transfer_id' => $purchase->id,
                'transaction_id' => $purchase->id,
                'qty_box' => $purchase->sum_quantity_box,
                'qty_pcs' => $purchase->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $supportRoyalty->id,
                    'transfer_id' => $purchase->id,
                    'transaction_id' => $purchase->id,
                    'qty_box' => $purchase->sum_quantity_box,
                    'qty_pcs' => $purchase->sum_quantity_pcs,
                ],
            ];

            $result[] = $itemBonus;
        }

        return $result;
    }

    public function baseQueryBonusRoyalty(): Builder
    {
        return DB::table('bonus_members')->where('bonus_members.bonus_type', '=', BONUS_TYPE_ROYALTY);
    }

    public function totalManagerRoyalty(User|int|string $user): int
    {
        $userId = ($user instanceof User) ? $user->id : $user;

        $total = $this->baseQueryBonusRoyalty()->where('bonus_members.user_id', '=', $userId)
            ->where('bonus_members.is_internal', '=', false)
            ->sum(DB::raw('floor(bonus_members.bonus_amount)'));

        return intval($total);
    }
}
