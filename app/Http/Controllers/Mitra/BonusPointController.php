<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\MitraPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

class BonusPointController extends Controller
{
    private function isShoppingPoint(Request $request)
    {
        $routeNames = explode('.', $request->route()->getName());

        $count = count($routeNames);
        $max = (count($routeNames) >= 3) ? 3 : $count;
        $checkName = implode('.', array_slice($routeNames, 0, $max));

        return ($checkName == 'mitra.point.my-shopping');
    }

    public function index(Request $request)
    {
        $dateRange = $this->dateFilter();
        $isShoppingPoint = $this->isShoppingPoint($request);
        $bonusTitle = $isShoppingPoint ? 'Belanja Pribadi' : 'Aktifasi Member';
        $dataUrl = $isShoppingPoint ? route('mitra.point.my-shopping.datatable') : route('mitra.point.activate-member.datatable');
        $totalUrl = $isShoppingPoint ? route('mitra.point.my-shopping.total') : route('mitra.point.activate-member.total');

        return view('mitra.points.index', [
            'windowTitle' => "Poin {$bonusTitle}",
            'breadcrumbs' => ['Poin', $bonusTitle],
            'isShoppingPoint' => $isShoppingPoint,
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    private function getQueryFilter(Request $request): array
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        return [
            'formatStart' => $formatStart,
            'formatEnd' => $formatEnd,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    private function getQueryBonusPoint(Request $request, bool $isShoppingPoint = null): array
    {
        $user = $request->user();
        $filter = $this->getQueryFilter($request);

        $eloquent = MitraPoint::query()
            ->byUser($user)
            ->byDates($filter['startDate'], $filter['endDate'])
            ->with('user')
            ->with('purchase')
            ->with('purchaseProduct');

        if (!is_null($isShoppingPoint)) {
            $eloquent = $eloquent->byType($isShoppingPoint ? POINT_TYPE_SHOPPING_SELF : POINT_TYPE_ACTIVATE_MEMBER);

            if (!$isShoppingPoint) {
                $eloquent = $eloquent->with('fromUser');
            }
        }

        return [
            'filter' => $filter,
            'eloquent' => $eloquent,
        ];
    }

    public function dataTable(Request $request)
    {
        $isShoppingPoint = $this->isShoppingPoint($request);
        $data = $this->getQueryBonusPoint($request, $isShoppingPoint);

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        $dt = datatables()->eloquent($data['eloquent'])
            ->editColumn('point_date', function ($row) {
                return formatFullDate($row->point_date);
            })
            ->addColumn('purchase_no', function ($row) use ($isShoppingPoint) {
                $purchase = $isShoppingPoint ? $row->purchase : $row->userPackage;

                return $purchase ? $purchase->code : '-';
            });

        if (!$isShoppingPoint) {
            $dt = $dt->addColumn('from_member_name', function ($row) {
                if ($fromUser = $row->fromUser) {
                    $format = '<div>%s</div><div class="text-primary fst-italic small">(%s)</div>';

                    return new HtmlString(sprintf($format, $fromUser->name, $fromUser->username));
                }

                return '-';
            })->addColumn('package_name', function ($row) {
                return $row->userPackage ? $row->userPackage->package_name : '-';
            });
        } else {
            $dt = $dt->addColumn('product_name', function ($row) {
                $product = $row->purchaseProduct->product;

                return $product ? $product->name : '-';
            });
        }

        return $dt->escapeColumns()
            ->toJson();
    }

    public function totalBonus(Request $request)
    {
        $isShoppingPoint = $this->isShoppingPoint($request);
        $data = $this->getQueryBonusPoint($request, $isShoppingPoint);

        $result = [
            'total' => $data['eloquent']->sum('point')
        ];

        return response()->json($result);
    }
}
