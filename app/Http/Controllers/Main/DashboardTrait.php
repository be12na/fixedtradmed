<?php

namespace App\Http\Controllers\Main;

use App\Helpers\Neo;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

trait DashboardTrait
{
    protected function mainDashboard(Request $request, Neo $neo)
    {
        if ($request->ajax()) {
            return $this->mainDashboardSummary($request, $neo);
        }

        $monthTitle = formatDatetime(Carbon::today(), 'F Y');
        $products = Product::query()->byActive()->orderBy('package_range')->get();

        return view('main.dashboard', [
            'monthTitle' => $monthTitle,
            'windowTitle' => 'Dashboard',
            'breadcrumbs' => ['Dashboard'],
            'products' => $products,
        ]);
    }

    private function mainDashboardSummary(Request $request, Neo $neo)
    {
        $branchIds = Branch::byActive()->get()->pluck('id')->toArray();
        $scope = $request->get('s', 'member'); // member, mitra
        $result = null;
        $formattedCurrency = true;

        if ($scope == 'member') {
            $data = $request->get('d', 'sale'); // distributor, agent, mitra, sale
            if ($data == 'sale') {
                $timeSale = $request->get('t', 'current'); // current, last, month
                $value = 0;
                $saleScope = ['scope' => 'member'];
                if ($timeSale == 'current') {
                    $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
                } elseif ($timeSale == 'last') {
                    $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
                } elseif ($timeSale == 'month') {
                    $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
                }

                $result = new HtmlString(formatCurrency($value, 0, true, false));
            }
        } elseif ($scope == 'mitra') {
            $data = $request->get('d', 'summary');

            if ($data == 'omzet') {
                $timeSale = $request->get('t', 'current'); // current, last, month
                $value = 0;
                $saleScope = ['scope' => 'mitra'];
                if ($timeSale == 'current') {
                    $value = $neo->sumOfThisWeekSales($branchIds, $saleScope);
                } elseif ($timeSale == 'last') {
                    $value = $neo->sumOfLastWeekSales($branchIds, $saleScope);
                } elseif ($timeSale == 'month') {
                    $value = $neo->sumOfThisMonthSales($branchIds, $saleScope);
                }

                $result = new HtmlString(formatCurrency($value, 0, true, false));
            } elseif ($data == 'summary') {
                $typeSum = $request->get('t', 'reseller');

                if ($typeSum == 'distributor') {
                    $result = 0; //formatNumber($neo->countDistributor(), 0);
                } elseif ($typeSum == 'agent') {
                    $result = 0; //formatNumber($neo->countAgent(), 0);
                } elseif ($typeSum == 'reseller') {
                    $result = formatNumber($neo->countReseller(), 0);
                } else {
                    $result = formatNumber($this->countMitra($typeSum, $request->get('p')), 0);
                }
            }
        }

        $status = is_null($result) ? 404 : 200;

        return response($result, $status);
    }

    private function countMitra(string $typeSum, int $rangeId = null): int
    {
        $query = User::query()->byMitraGroup()->byActivated();

        if ($typeSum == 'month') {
            $dateYm = date('Y-m');
            $query = $query->whereRaw("DATE_FORMAT(activated_at, '%Y-%m') = '{$dateYm}'");
        } elseif ($typeSum == 'today') {
            $dateYmd = date('Y-m-d');
            $query = $query->whereRaw("DATE_FORMAT(activated_at, '%Y-%m-%d') = '{$dateYmd}'");
        } elseif ($typeSum == 'mitra') {
            if (is_null($rangeId)) $rangeId = -1;

            if ($rangeId == 0) {
                $query = $query->byMitraDropshipper();
            } elseif ($rangeId > 0) {
                $query = DB::table(DB::raw('users as u'))
                    ->join(DB::raw('mitra_purchases mp'), 'mp.mitra_id', '=', 'u.id')
                    ->join(DB::raw('mitra_purchase_products as mpp'), 'mpp.mitra_purchase_id', '=', 'mp.id')
                    ->join(DB::raw('products as p'), function ($p) {
                        return $p->on('p.id', '=', 'mpp.product_id')
                            ->where('p.package_range', '=', DB::raw("(SELECT MAX(p2.package_range) as p_range FROM products p2 INNER JOIN mitra_purchase_products mpp2 ON mpp2.product_id = p2.id INNER JOIN mitra_purchases mp2 ON mp2.id = mpp2.mitra_purchase_id WHERE mp2.mitra_id = u.id)"));
                    })
                    ->selectRaw('u.*')
                    ->where('u.activated', '=', true)
                    ->where('u.user_group', '=', USER_GROUP_MEMBER)
                    ->where('u.user_type', '=', USER_TYPE_MITRA)
                    ->where('u.mitra_type', '=', MITRA_TYPE_RESELLER)
                    ->where('p.package_range', '=', $rangeId);
            }
        }

        return $query->count();
    }
}
