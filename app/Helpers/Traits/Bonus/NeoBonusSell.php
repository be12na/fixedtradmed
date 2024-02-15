<?php

namespace App\Helpers\Traits\Bonus;

use App\Models\BranchSale;
use App\Models\BranchTransfer;
use App\Models\MitraPurchase;
use App\Models\SettingBonusSell;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait NeoBonusSell
{
    private $defaultBonusSalePercent = 10;

    public function settingBonusSell(bool $isDirect = null, int $target = null): SettingBonusSell|null
    {
        return SettingBonusSell::query()->byDirect($isDirect)->byTarget($target)->first();
    }

    public function updateSettingBonusSell(array $values, SettingBonusSell $setting = null): SettingBonusSell
    {
        if (!is_null($setting)) $setting->delete();

        return SettingBonusSell::create($values);
    }

    public function neoBonusSale(BranchTransfer $branchTransfer, BranchSale $branchSale, SettingBonusSell $setting = null): array
    {
        $result = [];
        $salesman = $branchSale->salesman;
        $setting = optional($setting);

        if ($salesman->is_active_user) {
            $bonusDate = $branchSale->getRawOriginal('sale_date');

            foreach ($branchSale->products as $itemSale) {
                if (!is_null($itemSale->deleted_at)) continue;

                $itemBonus = [
                    'bonus_type' => BONUS_TYPE_SALE,
                    'bonus_date' => $bonusDate,
                    'user_id' => $salesman->id,
                    'position_id' => $salesman->position_int,
                    'is_internal' => true,
                    'bonus_base' => $itemSale->total_price,
                    'bonus_percent' => $itemSale->persen_crew,
                    'bonus_amount' => $itemSale->profit_crew,
                    'setting_id' => $setting->id ?? 0,
                    'transfer_id' => $branchTransfer->id,
                    'transaction_id' => $branchSale->id,
                    'item_id' => $itemSale->id,
                    'qty_box' => $itemSale->qty_box,
                    'qty_pcs' => $itemSale->qty_pcs,
                    'details' => [
                        'setting_id' => $setting->id ?? 0,
                        'transfer_id' => $branchTransfer->id,
                        'transaction_id' => $branchSale->id,
                        'item_id' => $itemSale->id,
                        'qty_box' => $itemSale->qty_box,
                        'qty_pcs' => $itemSale->qty_pcs,
                    ],
                ];

                $result[] = $itemBonus;
            }
        }

        return $result;
    }

    public function neoBonusSaleMitra(MitraPurchase $purchase, SettingBonusSell $setting = null): array
    {
        $result = [];

        $salesman = $purchase->mitra;
        $setting = optional($setting);

        if ($salesman->is_active_user) {
            $bonusDate = $purchase->getRawOriginal('purchase_date');

            $itemBonus = [
                'bonus_type' => BONUS_TYPE_SALE,
                'bonus_date' => $bonusDate,
                'user_id' => $salesman->id,
                'position_id' => $salesman->position_ext,
                'is_internal' => false,
                'bonus_base' => $purchase->total_purchase,
                'bonus_percent' => $purchase->bonus_persen,
                'bonus_amount' => $purchase->total_bonus,
                'setting_id' => $setting->id ?? 0,
                'transfer_id' => $purchase->id,
                'transaction_id' => $purchase->id,
                'qty_box' => $purchase->sum_quantity_box,
                'qty_pcs' => $purchase->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $setting->id ?? 0,
                    'transfer_id' => $purchase->id,
                    'transaction_id' => $purchase->id,
                    'item_id' => null,
                    'qty_box' => $purchase->sum_quantity_box,
                    'qty_pcs' => $purchase->sum_quantity_pcs,
                ],
            ];

            $result[] = $itemBonus;
        }

        return $result;
    }

    public function neoBonusPurchaseMitraPremium(MitraPurchase $purchase, int $bonusType, SettingBonusSell $setting = null): array
    {
        $result = [];
        $mitra = $purchase->mitra;
        $isMitraPremium = ($mitra->mitra_type == MITRA_TYPE_AGENT);

        if (!$isMitraPremium) return [];
        if (!in_array($bonusType, array_keys(BONUS_TYPE_MITRA_SHOPPINGS))) return [];

        $setting = optional($setting);

        $targetUser = $mitra->referral;
        $positionId = null;
        $getBonus = false;

        if (!empty($targetUser)) {
            if ($bonusType == BONUS_TYPE_MGR_DISTRIBUTOR) {
                $targetUser = $purchase->manager;
                $getBonus = $targetUser->is_active_user;
                $positionId = $targetUser->position_int;
            } elseif ($bonusType == BONUS_TYPE_MITRA_DIRECT_MITRA) {
                $getBonus = $targetUser->is_mitra_premium;
                $positionId = $targetUser->position_ext;
            } else {
                // mgr direct mitra
                $getBonus = (!empty($targetUser) && $targetUser->is_manager_user);
                $positionId = $targetUser->position_int;
            }
        }

        $bonusDate = $purchase->getRawOriginal('purchase_date');
        $baseBonus = $purchase->total_transfer;
        $percent = $setting->percent ?? 0;
        $bonus = floor($baseBonus * $percent / 100);
        $getBonus = ($getBonus && ($bonus > 0));

        if ($getBonus) {
            $result[] = [
                'bonus_type' => $bonusType,
                'bonus_date' => $bonusDate,
                'user_id' => $targetUser->id,
                'position_id' => $positionId,
                'is_internal' => false,
                'bonus_base' => $baseBonus,
                'bonus_percent' => $percent,
                'bonus_amount' => $bonus,
                'setting_id' => $setting->id ?? 0,
                'transfer_id' => $purchase->id,
                'transaction_id' => $purchase->id,
                'qty_box' => $purchase->sum_quantity_box,
                'qty_pcs' => $purchase->sum_quantity_pcs,
                'details' => [
                    'setting_id' => $setting->id ?? 0,
                    'transfer_id' => $purchase->id,
                    'transaction_id' => $purchase->id,
                    'item_id' => null,
                    'qty_box' => $purchase->sum_quantity_box,
                    'qty_pcs' => $purchase->sum_quantity_pcs,
                ],
            ];
        }

        return $result;
    }

    public function baseQueryBonusSale(): Builder
    {
        return DB::table('bonus_members')->where('bonus_members.bonus_type', '=', BONUS_TYPE_SALE);
    }

    public function totalBonusSale(User|int|string $user, bool $thisMonthOnly = false): int
    {
        $userId = ($user instanceof User) ? $user->id : $user;

        $query = $this->baseQueryBonusSale()->where('bonus_members.user_id', '=', $userId);

        if ($thisMonthOnly === true) {
            $time = strtotime(date('Y-m-d'));
            $startDate = Carbon::createFromTimestamp($time)->startOfMonth();
            $endDate = Carbon::createFromTimestamp($time)->endOfMonth();

            $query = $query->whereBetween('bonus_members.bonus_date', [$startDate, $endDate]);
        }

        $total = $query->sum(DB::raw('floor(bonus_members.bonus_amount)'));

        return intval($total);
    }

    public function baseQueryBonusDirectMitra(int $type = null): Builder
    {
        if (is_null($type)) $type = BONUS_TYPE_MGR_DIRECT_MITRA;
        return DB::table('bonus_members')->where('bonus_members.bonus_type', '=', $type);
    }

    public function totalBonusDirectMitra(User|int|string $user, int $type = null): int
    {
        $userId = ($user instanceof User) ? $user->id : $user;

        $total = $this->baseQueryBonusDirectMitra($type)->where('bonus_members.user_id', '=', $userId)
            ->where('bonus_members.is_internal', '=', false)
            ->sum(DB::raw('floor(bonus_members.bonus_amount)'));

        return intval($total);
    }
}
