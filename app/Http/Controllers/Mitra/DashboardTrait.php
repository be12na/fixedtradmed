<?php

namespace App\Http\Controllers\Mitra;

use App\Helpers\Neo;
use App\Models\MitraPoint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

trait DashboardTrait
{
    protected function mitraDashboard(Request $request, Neo $neo)
    {
        if ($request->ajax()) {
            return $this->mitraDashboardSummary($request, $neo);
        }

        $myMitra = $request->user()->myMitra;
        $countRegular = $myMitra->where('mitra_type', '=', MITRA_TYPE_RESELLER)->count();
        $countBasic = $myMitra->where('mitra_type', '=', MITRA_TYPE_DROPSHIPPER)->count();

        $monthTitle = formatDatetime(Carbon::today(), 'F Y');

        return view('mitra.dashboard', [
            'monthTitle' => $monthTitle,
            'countRegular' => $countRegular,
            'countBasic' => $countBasic,
            'windowTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
        ]);
    }

    private function mitraDashboardSummary(Request $request, Neo $neo)
    {
        $user = $request->user();
        $branchIds = $user->activeBranches->pluck('branch_id')->toArray();
        $result = null;
        $paramS = $request->get('s');

        $saleScope = [
            'user' => $user,
        ];

        $paramT = $request->get('t', 'current'); // current, last, month
        $value = 0;
        $formattedCurrency = true;

        if ($paramS == 'direct') {
            $saleScope['scope'] = 'direct';
            if ($paramT == 'current') {
                $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
            } elseif ($paramT == 'last') {
                $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
            } elseif ($paramT == 'month') {
                $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
            } elseif ($paramT == 'total-bonus') {
                $value = 0;
                $formattedCurrency = false;
            } elseif ($paramT == 'total-purchase') {
                $value = $neo->sumOfTotalMitraPurchase($branchIds, $saleScope);
            }
        } elseif ($paramS == 'bonus') {
            $query = MitraPoint::query()->byUser($user);
            if ($paramT == 'total') {
                $value = $query->sum('point');
                $formattedCurrency = false;
            } elseif ($paramT == 'self') {
                $value = $query->byType(POINT_TYPE_SHOPPING_SELF)->sum('point');
                $formattedCurrency = false;
            } elseif ($paramT == 'upline') {
                $value = $query->byType(POINT_TYPE_SHOPPING_MEMBER)->sum('point');
                $formattedCurrency = false;
            }
        } elseif ($paramS == 'member') {
            $formattedCurrency = false;

            if ($paramT == 'total') {
                $value = $user->total_member;
            } elseif ($paramT == 'shopper') {
                // $value = $user->total_member_shopping;
                $value = $user->total_member;
            } elseif ($paramT == 'month') {
                $value = $user->total_member_this_month;
            } elseif ($paramT == 'today') {
                $value = $user->total_member_today;
            }
        } else {
            if ($paramT == 'current') {
                $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
            } elseif ($paramT == 'last') {
                $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
            } elseif ($paramT == 'month') {
                $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
            }
        }

        $result = new HtmlString(formatCurrency($value, 0, $formattedCurrency, false));

        $status = is_null($result) ? 404 : 200;

        return response($result, $status);
    }
}
