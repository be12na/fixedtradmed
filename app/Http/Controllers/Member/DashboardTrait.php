<?php

namespace App\Http\Controllers\Member;

use App\Helpers\Neo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

trait DashboardTrait
{
    protected function memberDashboard(Request $request, Neo $neo)
    {
        $user = $request->user();

        if ($user->position_int == USER_INT_MGR) return $this->memberManagerDashboard($request, $neo);
        if ($user->position_int < USER_INT_MGR) return $this->memberUpManagerDashboard($request);
        if ($user->position_int == USER_INT_AM) return $this->memberAssManagerDashboard($request);

        return $this->memberCrewDashboard($user);
    }

    private function memberManagerDashboard(Request $request, Neo $neo)
    {
        if ($request->ajax()) {
            return $this->managerDashboardSummary($request, $neo);
        }

        $monthTitle = formatDatetime(Carbon::today(), 'F Y');

        return view('member.dashboard.manager', [
            'monthTitle' => $monthTitle,
            'windowTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
        ]);
    }

    private function managerDashboardSummary(Request $request, Neo $neo)
    {
        $user = $request->user();
        $branchIds = $user->activeBranches->pluck('branch_id')->toArray();
        $scope = $request->get('s', 'team'); // team, mitra
        $result = null;
        $timeSale = $request->get('t', 'current'); // current, last, month

        $saleScope = [
            'user' => $user,
        ];

        if ($scope == 'team') {
            $value = 0;
            $saleScope['scope'] = 'team';
            if ($timeSale == 'current') {
                $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
            } elseif ($timeSale == 'last') {
                $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
            } elseif ($timeSale == 'month') {
                $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
            }

            $result = new HtmlString(formatCurrency($value, 0, true, false));
        } elseif ($scope == 'self') {
            $value = 0;
            $saleScope['scope'] = 'self';
            if ($timeSale == 'current') {
                $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
            } elseif ($timeSale == 'last') {
                $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
            } elseif ($timeSale == 'month') {
                $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
            } elseif ($timeSale == 'total-month-bonus-sale') {
                $value = $neo->totalBonusSale($user, true);
            }

            $result = new HtmlString(formatCurrency($value, 0, true, false));
        } elseif ($scope == 'mitra') {
            $value = 0;
            $saleScope['scope'] = 'mitra';
            if ($timeSale == 'current') {
                $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
            } elseif ($timeSale == 'last') {
                $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
            } elseif ($timeSale == 'month') {
                $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
            } elseif ($timeSale == 'royalty') {
                $value = $neo->totalManagerRoyalty($user);
            } elseif ($timeSale == 'total-purchase') {
                $value = $neo->sumOfTotalMitraPurchase($branchIds, $saleScope);
            }

            $result = new HtmlString(formatCurrency($value, 0, true, false));
        } elseif ($scope == 'bonus') {
            if ($timeSale == 'total-bonus-referral') {
                $value = $neo->totalBonusDirectMitra($user, BONUS_TYPE_MGR_DIRECT_MITRA);
            } elseif ($timeSale == 'total-bonus-dc') {
                $value = $neo->totalBonusDirectMitra($user, BONUS_TYPE_MGR_DISTRIBUTOR);
            } elseif ($timeSale == 'total-bonus-sale') {
                $value = $neo->totalBonusSale($user);
            }

            $result = new HtmlString(formatCurrency($value, 0, true, false));
        }

        $status = is_null($result) ? 404 : 200;

        return response($result, $status);
    }

    // khusus asmen
    private function memberAssManagerDashboard(Request $request)
    {
        return view('member.dashboard.assistent-manager', [
            'windowTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
        ]);
    }

    // khusus crew (none manager dan none asmen)
    private function memberCrewDashboard(Request $request)
    {
        return view('member.dashboard.crew', [
            'windowTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
        ]);
    }

    // khusus member di atas manager
    private function memberUpManagerDashboard(Request $request)
    {
        return view('member.dashboard.manager-up', [
            'windowTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
        ]);
    }
}
