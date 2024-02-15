<?php

namespace App\Helpers;

use App\Helpers\Traits\Admin\Member as AdminMember;
use App\Helpers\Traits\Bonus\NeoBonusSell;
use App\Helpers\Traits\Bonus\NeoBonusTeam;
use App\Helpers\Traits\Bonus\NeoBonusOverride;
use App\Helpers\Traits\Bonus\NeoBonusRoyalty;
use App\Helpers\Traits\Mitra\NeoMitraCashback;
use App\Helpers\Traits\Mitra\NeoMitraDiscount;
use App\Helpers\Traits\NeoBank;
use App\Helpers\Traits\NeoBranchSale;
use App\Helpers\Traits\NeoStockOpname;
use App\Models\Branch;
use App\Models\BranchMember;
use App\Models\BranchTransfer;
use App\Models\MitraPurchase;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Neo
{
    use NeoStockOpname, NeoBranchSale;
    use NeoBank;
    use AdminMember;
    use NeoBonusRoyalty, NeoBonusOverride, NeoBonusTeam, NeoBonusSell;
    use NeoMitraDiscount, NeoMitraCashback;

    private Carbon $carbonToday;
    private int $dayIndex;

    private bool $isLiveEnvironment;

    public function __construct()
    {
        $this->isLiveEnvironment = in_array(config('app.env', 'local'), ['stage', 'production']);

        $this->carbonToday = $now = Carbon::now();
        $this->dayIndex = $now->dayOfWeek;

        $this->initStockOpname($this->carbonToday);
        $this->initSale($this->carbonToday);
    }

    private AppStructure $appStructure;

    public function AppStructure(): AppStructure
    {
        if (isset($this->appStructure)) return $this->appStructure;

        return $this->appStructure = app('appStructure');
    }

    public function isLive()
    {
        return $this->isLiveEnvironment;
    }

    public function myBranches(User $user = null, bool $asDistributorOnly = null): Collection
    {
        if (is_null($user)) $user = Auth::user();

        $activeBranches = $user->activeBranches
            ->pluck('branch')
            ->values();

        if ($asDistributorOnly === true) {
            $result = collect();
            foreach ($activeBranches as $branch) {
                if ($branch->distributors->isNotEmpty()) {
                    $mgrIds = $branch->distributors->pluck('user_id')->toArray();
                    if (in_array($user->id, $mgrIds)) $result->push($branch);
                }
            }

            $activeBranches = $result;
        }

        return $activeBranches->sortBy('name');
    }

    public function branchManagerFromMember(User $manager, Branch|int|string $branch): BranchMember|null
    {
        if (empty($manager) || !$manager->exists || $manager->activeBranches->isEmpty()) return null;

        $branchId = ($branch instanceof Branch) ? $branch->id : $branch;

        return $manager->activeBranches->where('branch_id', '=', $branchId)->first();
    }

    public function managerFromMember(User $user): User|null
    {
        if (empty($user) || !$user->exists) return null;
        if ($user->position_int == USER_INT_MGR) return $user;

        // $upline = $user->upline;

        // while (!empty($upline)) {
        //     if ($upline->position_int == USER_INT_MGR) break;

        //     $upline = $upline->upline;
        // }

        // return $upline;

        return $user->uplines->where('position_int', '=', USER_INT_MGR)->first();
    }

    public function subDomainLimits()
    {
        $limits = [
            'main',
            'founder',
            'member',
            'distributor',
            'agen',
            'agent',
            'mitra',
            'partner',
            'ref',
            'referral',
            'toko',
            'shop',
            'market',
        ];

        $internal = app('appStructure')->getAllData(true)->pluck('code')->toArray();
        $external = app('appStructure')->getAllData(false)->pluck('code')->toArray();

        $limits = array_unique(array_merge($limits, $internal, $external));

        $result = [];
        foreach ($limits as $limit) {
            if (empty($limit)) continue;

            $result[] = $limit;
            $result[] = strtolower($limit);
            $result[] = strtoupper($limit);
            $result[] = ucfirst($limit);
            $result[] = ucwords($limit);
        }

        return array_unique($result);
    }

    // pembagian bonus
    public function neoBonuses(BranchTransfer|MitraPurchase $transfer): array
    {
        $result = [];

        if (empty($transfer)) return $result;

        $isBranchTransfer = ($transfer instanceof BranchTransfer);
        $status = $isBranchTransfer ? $transfer->transfer_status : $transfer->purchase_status;

        if (!is_null($transfer->deleted_at) || ($status != PROCESS_STATUS_APPROVED)) return $result;

        $royalties = $this->listSettingRoyalty($isBranchTransfer);

        // if ($isBranchTransfer) {
        //     // transfer cabang
        //     $overrides = $this->listSettingOverride();
        //     $teams = $this->listSettingBonusTeam();

        //     $result = [
        //         'bonusSale' => [],
        //         'bonusRoyalty' => [],
        //         'bonusOverride' => [],
        //         'bonusTeam' => [],
        //     ];

        //     $saleSetting = $this->settingBonusSell(false);

        //     foreach ($transfer->transferDetails as $itemTransfer) {
        //         $branchSale = $itemTransfer->branchSale;
        //         if (!is_null($branchSale->deleted_at) || ($branchSale->is_active != 1)) continue;

        //         $salesman = $branchSale->salesman;
        //         $uplines = $this->getMemberUplines($salesman);

        //         $bonusSale = $this->neoBonusSale($transfer, $branchSale, $saleSetting);
        //         if (!empty($bonusSale)) {
        //             $result['bonusSale'] = array_merge($result['bonusSale'], $bonusSale);
        //         }

        //         $bonusRoyalty = $this->neoBonusRoyalty(
        //             $transfer,
        //             $branchSale,
        //             $royalties,
        //             $uplines
        //         );
        //         if (!empty($bonusRoyalty)) {
        //             $result['bonusRoyalty'] = array_merge($result['bonusRoyalty'], $bonusRoyalty);
        //         }

        //         $bonusOverride = $this->neoBonusOverride($transfer, $branchSale, $salesman, $overrides, $uplines);
        //         if (!empty($bonusOverride)) {
        //             $result['bonusOverride'] = array_merge($result['bonusOverride'], $bonusOverride);
        //         }

        //         $bonusTeam = $this->neoBonusTeam($transfer, $branchSale, $salesman, $teams, $uplines);
        //         if (!empty($bonusTeam)) {
        //             $result['bonusTeam'] = array_merge($result['bonusTeam'], $bonusTeam);
        //         }
        //     }
        // } else {
        // transfer mitra
        // if ($transfer->is_v2) {
        //     $settingMgrDC = $this->settingBonusSell(true, BONUS_TYPE_MGR_DISTRIBUTOR);
        //     $settingMgrMitra = $this->settingBonusSell(true, BONUS_TYPE_MGR_DIRECT_MITRA);
        //     $settingMitraMitra = $this->settingBonusSell(true, BONUS_TYPE_MITRA_DIRECT_MITRA);

        //     $result = [
        //         'bonusMgrDC' => $this->neoBonusPurchaseMitraPremium($transfer, BONUS_TYPE_MGR_DISTRIBUTOR, $settingMgrDC),
        //         'bonusMgrDirectMitra' => $this->neoBonusPurchaseMitraPremium($transfer, BONUS_TYPE_MGR_DIRECT_MITRA, $settingMgrMitra),
        //         'bonusMitraDirectMitra' => $this->neoBonusPurchaseMitraPremium($transfer, BONUS_TYPE_MITRA_DIRECT_MITRA, $settingMitraMitra),
        //     ];
        // } else {
        // old version: sebelum grand launching
        $saleSetting = $this->settingBonusSell(false);

        $result = [
            'bonusSale' => [],
            'bonusRoyalty' => [],
        ];
        $uplines = $this->getMemberUplines($transfer->mitra);

        $bonusSale = $this->neoBonusSaleMitra($transfer, $saleSetting);
        if (!empty($bonusSale)) {
            $result['bonusSale'] = array_merge($result['bonusSale'], $bonusSale);
        }

        $bonusRoyalty = $this->neoBonusRoyaltyMitra(
            $transfer,
            $royalties,
            $uplines
        );
        if (!empty($bonusRoyalty)) {
            $result['bonusRoyalty'] = array_merge($result['bonusRoyalty'], $bonusRoyalty);
        }
        // }
        // }

        return $result;
    }

    // pencatatan omzet
    public function omzetFromApprovedTransfer(BranchTransfer|MitraPurchase $transfer): array
    {
        $result = [];

        if (empty($transfer)) return $result;

        $isBranchTransfer = ($transfer instanceof BranchTransfer);
        $status = $isBranchTransfer ? $transfer->transfer_status : $transfer->purchase_status;

        if (!is_null($transfer->deleted_at) || ($status != PROCESS_STATUS_APPROVED)) return $result;

        if ($isBranchTransfer) {
            $branchSales = $transfer->transferDetails->pluck('branchSale')->values();
            foreach ($branchSales as $branchSale) {
                $salesman = $branchSale->salesman;
                $structure = $salesman->structure;
                $ancestors = $structure ? $structure->ancestorsWithSelf()->get()->pluck('user_id')->toArray() : [];
                $jsonAncestors = json_encode($ancestors);

                if (!empty($ancestors)) {
                    $qtyBox = $branchSale->sum_quantity_box;
                    $qtyPcs = $branchSale->sum_quantity_pcs;
                    $omzet = $branchSale->sum_total_price;

                    foreach ($ancestors as $userId) {
                        $result[] = [
                            'user_id' => $userId,
                            'omzet_date' => $branchSale->getRawOriginal('sale_date'),
                            'branch_id' => $branchSale->branch_id,
                            'transfer_id' => $transfer->id,
                            'transaction_id' => $branchSale->id,
                            'salesman_id' => $branchSale->salesman_id,
                            'salesman_ancestors' => $jsonAncestors,
                            'is_from_mitra' => false,
                            'qty_box' => $qtyBox,
                            'qty_pcs' => $qtyPcs,
                            'omzet' => $omzet,
                        ];
                    }
                }
            }
        } else {
            $mitra = $transfer->mitra;
            $referral = $mitra->referral;
            $structure = $referral ? $referral->structure : null;
            $ancestors = $structure ? $structure->ancestorsWithSelf()->get()->pluck('user_id')->toArray() : [];
            $ancestors = array_unique(array_merge([$mitra->id], $ancestors));

            if (!empty($ancestors)) {
                $qtyBox = $transfer->sum_quantity_box;
                $qtyPcs = $transfer->sum_quantity_pcs;
                $omzet = $transfer->sum_total_price;

                foreach ($ancestors as $userId) {
                    $result[] = [
                        'user_id' => $userId,
                        'omzet_date' => $transfer->getRawOriginal('purchase_date'),
                        'branch_id' => $mitra->branch_id ?? 0,
                        'transfer_id' => $transfer->id,
                        'transaction_id' => $transfer->id,
                        'salesman_id' => $mitra->id,
                        'salesman_ancestors' => json_encode($ancestors),
                        'is_from_mitra' => true,
                        'qty_box' => $qtyBox,
                        'qty_pcs' => $qtyPcs,
                        'omzet' => $omzet,
                    ];
                }
            }
        }

        return $result;
    }

    public function zones(bool $activeOnly): EloquentCollection
    {
        $query = Zone::orderBy('name');
        if ($activeOnly) $query = $query->byActive();

        return $query->get();
    }
}
