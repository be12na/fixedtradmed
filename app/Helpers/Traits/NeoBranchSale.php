<?php

namespace App\Helpers\Traits;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

trait NeoBranchSale
{
    use NeoChart;

    protected int $dayIndexSale;
    protected Carbon $carbonTodaySale;

    protected function initSale(Carbon $today): void
    {
        $this->carbonTodaySale = clone $today;
        $this->dayIndexSale = $today->dayOfWeek;
    }

    public function canSale(): bool
    {
        if (!Auth::check()) return false;
        $user = Auth::user();

        $role = $user->is_main_user ? 'main.sales.edit' : 'member.sale.create';

        return hasPermission($role);
    }

    public function dateRangeInputSale(): array
    {
        $today = Carbon::today();
        $subDay = DAYS_SALE_RANGE[$today->dayOfWeek][0];
        $result = [
            // (clone $today)->subWeek(), //->startOfWeek(1),
            (clone $today)->addDays($subDay),
            $today
        ];

        return $result;
    }

    private function baseQuerySummaryTransaction(array $branchIds, Carbon $startDate, Carbon $endDate, array $params = null): QueryBuilder
    {
        /*
        $params = [
            'users' => model user|int|string, // param utama, untuk memisahkan query manager / member dengan admin
            // khusus admin dan internal member
            'scope' => string, admin: member atau mitra, manager: team atau mitra, crew: self
            // khusus manager
            'exclude_self' => boolean
        ];
        */

        if (is_null($params)) $params = [];

        $startFormat = $startDate->format('Y-m-d');
        $endFormat = $endDate->format('Y-m-d');
        $inBranches = implode(',', $branchIds);
        $user = null; // default, sebagai user admin

        $userId = null;
        $asAdmin = true;
        $queries = [];

        if (array_key_exists('user', $params) && !empty($paramUser = $params['user'])) {
            $asAdmin = false;
            if ($paramUser instanceof User) {
                $user = $paramUser;
                $userId = $paramUser->id;
            } else {
                $userId = $paramUser;
                $user = User::byId($userId)->first();
            }

            if (empty($user)) {
                throw new Exception('You are unable to read this data.', 500);
            }
        }

        $asMember = (!$asAdmin && $user->is_member_user);
        $asManager = (!$asAdmin && $user->is_manager_user);
        $asMitra = ($asMember && $user->is_member_mitra_user);
        $asCrew = ($asMember && !$asAdmin && !$asManager && !$asMitra);
        $asSelf = false;

        $scopes = [];
        if (!$asMitra) {
            if (array_key_exists('scope', $params)) {
                $varScope = $params['scope'];
                if ($asAdmin && ($varScope == 'member')) $scopes[] = 'member';
                if ($asManager && ($varScope == 'team')) $scopes[] = 'team';
                if (($asAdmin || $asManager) && ($varScope == 'mitra')) $scopes[] = 'mitra';
                // if ($asCrew && ($varScope == 'team')) $scopes[] = 'self';
                $asSelf = ($varScope == 'self');

                if (($asCrew && ($varScope == 'team')) || $asSelf) $scopes[] = 'self';
            } else {
                if ($asAdmin) $scopes[] = 'member';
                if ($asManager) $scopes[] = 'team';
                if ($asAdmin || $asManager) $scopes[] = 'mitra';
            }
        } elseif ($asMitra) {
            $scopes[] = 'mitra';
            if (in_array('direct', $params)) $scopes[] = 'direct';
        }

        if (in_array('member', $scopes) || in_array('team', $scopes) || in_array('self', $scopes)) {
            $querySale = DB::table('branch_sales')
                ->join('branch_sales_products', function ($join) {
                    return $join->on('branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
                        ->whereRaw('branch_sales_products.is_active = 1')
                        ->whereRaw('branch_sales_products.deleted_at is NULL');
                })
                ->selectRaw("branch_sales.sale_date as trans_date, SUM(branch_sales_products.total_price) as total_price")
                ->whereRaw("
                    ((branch_sales.sale_date BETWEEN '{$startFormat}' AND '{$endFormat}'))
                ")
                ->whereRaw("(branch_sales.branch_id IN({$inBranches}))")
                ->whereRaw("(branch_sales.is_active = 1)")
                ->whereRaw("(branch_sales.deleted_at IS NULL)")
                ->groupBy("branch_sales.sale_date");

            if ($asSelf || $asCrew) {
                $querySale = $querySale->whereRaw("branch_sales.salesman_id = {$userId}");
            } elseif ($asManager) {
                $querySale = $querySale->whereRaw("branch_sales.manager_id = {$userId}");
                $excludeSelf = array_key_exists('exclude_self', $params) ? ($params['exclude_self'] === true) : false;
                if ($excludeSelf) {
                    $querySale = $querySale->whereRaw("branch_sales.salesman_id != {$userId}");
                }
            }

            $queries[] = $querySale->toSql();
        }

        if (in_array('mitra', $scopes)) {
            $queryPurchase = DB::table('mitra_purchases')
                ->selectRaw('mitra_purchases.purchase_date as trans_date,SUM(mitra_purchases.total_purchase) as total_price')
                ->whereRaw("(mitra_purchases.purchase_date BETWEEN '{$startFormat}' AND '{$endFormat}')")
                ->whereRaw('(mitra_purchases.is_active = 1)')
                ->whereRaw('(mitra_purchases.is_transfer = 1)')
                ->whereRaw('(mitra_purchases.deleted_at IS NULL)')
                ->groupBy('mitra_purchases.purchase_date');

            $isDirect = in_array('direct', $scopes);

            if ($asManager) {
                $minBonusDate = DB::table('bonus_members')
                    ->where('bonus_type', '=', BONUS_TYPE_MGR_DIRECT_MITRA)
                    ->where('user_id', '=', $userId)
                    ->min('bonus_date');

                if (empty($minBonusDate)) $minBonusDate = DATE_BONUS_MGR_DIRECT_MITRA;

                $queryPurchase = $queryPurchase->whereRaw("(mitra_purchases.referral_id = {$userId})")
                    ->whereRaw('(mitra_purchases.purchase_status IN(' . implode(',', [PROCESS_STATUS_APPROVED]) . '))')
                    ->whereRaw("(mitra_purchases.purchase_date >= '{$minBonusDate}')");
            } elseif ($asMitra) {
                $queryPurchase = $queryPurchase->whereRaw('(mitra_purchases.purchase_status IN(' . implode(',', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]) . '))');

                if ($isDirect) {
                    $minBonusDate = DB::table('bonus_members')
                        ->where('bonus_type', '=', BONUS_TYPE_MITRA_DIRECT_MITRA)
                        ->where('user_id', '=', $userId)
                        ->min('bonus_date');

                    $queryPurchase = $queryPurchase->whereRaw("(mitra_purchases.referral_id = '{$userId}')");
                } else {
                    $queryPurchase = $queryPurchase->whereRaw("(mitra_purchases.mitra_id = '{$userId}')");
                }
            } elseif ($asAdmin) {
                $queryPurchase = $queryPurchase
                    ->whereRaw('(mitra_purchases.purchase_status IN(' . implode(',', [PROCESS_STATUS_APPROVED]) . '))');
            }

            $queries[] = $queryPurchase->toSql();
        }

        $unionSql = implode(' UNION ALL ', $queries);

        return DB::table(DB::raw("({$unionSql}) as neo_trans"))
            ->selectRaw('trans_date, SUM(total_price) as total_price')
            ->groupBy('trans_date');
    }

    public function sumOfLastWeekSales(array $branchIds, array $params = null)
    {
        $time = strtotime(date('Y-m-d'));
        $time = strtotime('-1 week', $time);
        $startDate = Carbon::createFromTimestamp($time)->startOfWeek(0);
        $endDate = Carbon::createFromTimestamp($time)->endOfWeek(6);

        return $this->baseQuerySummaryTransaction($branchIds, $startDate, $endDate, $params)->get()->sum('total_price');
    }

    public function sumOfThisWeekSales(array $branchIds, array $params = null)
    {
        $time = strtotime(date('Y-m-d'));
        $startDate = Carbon::createFromTimestamp($time)->startOfWeek(0);
        $endDate = Carbon::createFromTimestamp($time)->endOfWeek(6);

        return $this->baseQuerySummaryTransaction($branchIds, $startDate, $endDate, $params)->get()->sum('total_price');
    }

    public function sumOfThisMonthSales(array $branchIds, array $params = null)
    {
        $time = strtotime(date('Y-m-d'));
        $startDate = Carbon::createFromTimestamp($time)->startOfMonth();
        $endDate = Carbon::createFromTimestamp($time)->endOfMonth();

        return $this->baseQuerySummaryTransaction($branchIds, $startDate, $endDate, $params)->get()->sum('total_price');
    }

    public function sumOfTotalMitraPurchase(array $branchIds, array $params = null)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', DATE_BONUS_MGR_DIRECT_MITRA);
        $endDate = Carbon::today();

        return $this->baseQuerySummaryTransaction($branchIds, $startDate, $endDate, $params)->get()->sum('total_price');
    }

    public function chartDataOfThisMonthSales(array $branchIds, array $params = null): stdClass
    {
        $time = strtotime(date('Y-m-d'));
        $year = date('Y', $time);
        $startDate = Carbon::createFromTimestamp($time)->startOfMonth();
        $endDate = Carbon::createFromTimestamp($time)->endOfMonth();

        $query = $this->baseQuerySummaryTransaction($branchIds, $startDate, $endDate, $params);
        $sql = $query->toSql();
        $datas = DB::table(DB::raw("({$sql}) as trans"))->mergeBindings($query)
            ->selectRaw("
                DAY(trans_date) as day_of_date,
                SUM(total_price) as total
            ")
            ->groupBy(DB::raw('DAY(trans_date)'))
            ->get();

        $temps = collect();
        for ($i = 1; $i <= $endDate->day; $i++) {
            $data = $datas->where('day_of_date', '=', $i)->first();

            $temps->push((object) [
                'label' => $i,
                'data' => $data ? $data->total : 0
            ]);
        }

        $monthName = $startDate->monthName;
        $colors = $this->setColorGraph($temps->count());

        $result = (object) [
            'title' => "{$monthName} {$year}",
            'start_date' => $startDate,
            'end_date' => $endDate,
            'labels' => $temps->pluck('label')->toArray(),
            'datas' => $temps->pluck('data')->toArray(),
            'total' => $temps->sum('data'),
            'background_color' => $colors->background_color,
            'border_color' => $colors->background_color,
        ];

        return $result;
    }
}
