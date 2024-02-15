<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\MitraPoint;
use App\Reports\Point\ExcelListPoint;
use Barryvdh\DomPDF\Facade\Pdf;
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

        return ($checkName == 'main.point.shopping');
    }

    public function index(Request $request)
    {
        $dateRange = $this->dateFilter();
        $isShoppingPoint = $this->isShoppingPoint($request);
        $bonusTitle = $isShoppingPoint ? 'Poin Belanja' : 'Poin Aktifasi';
        $dataUrl = $isShoppingPoint ? route('main.point.shopping.datatable') : route('main.point.activate.datatable');
        $totalUrl = $isShoppingPoint ? route('main.point.shopping.total') : route('main.point.activate.total');
        $downloadRouteName = $isShoppingPoint ? 'main.point.shopping.download' : 'main.point.activate.download';

        return view('main.bonuses.points.index', [
            'windowTitle' => "Bonus {$bonusTitle}",
            'breadcrumbs' => ['Bonus', $bonusTitle],
            'isShoppingPoint' => $isShoppingPoint,
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
            'downloadRouteName' => $downloadRouteName,
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
        $filter = $this->getQueryFilter($request);

        $eloquent = MitraPoint::query()
            ->byDates($filter['startDate'], $filter['endDate'])
            ->with('user');

        if (!is_null($isShoppingPoint)) {
            $eloquent = $eloquent->byType($isShoppingPoint ? POINT_TYPE_SHOPPING_SELF : POINT_TYPE_ACTIVATE_MEMBER);

            if (!$isShoppingPoint) {
                $eloquent = $eloquent->with('fromUser')
                    ->with('userPackage');
            }
        } else {
            $eloquent = $eloquent->with('purchase')
                ->with('purchaseProduct');
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
            ->addColumn('member_name', function ($row) {
                $format = '<div>%s</div><div class="text-primary fst-italic small">(%s)</div>';

                return new HtmlString(sprintf($format, $row->user->name, $row->user->username));
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

    public function downloadFile(Request $request)
    {
        $isShoppingPoint = $this->isShoppingPoint($request);
        $data = $this->getQueryBonusPoint($request, $isShoppingPoint);

        $rows = $data['eloquent']
            ->orderBy('point_date')
            ->orderBy('id')
            ->get();

        $filter = $data['filter'];

        $filenames = [
            'BonusPoint',
            $isShoppingPoint ? 'Belanja' : 'Aktifasi',
            $isShoppingPoint ? 'Pribadi' : 'Member'
        ];

        $fileDates = [
            $filter['startDate']->format('Ymd'),
        ];

        if ($filter['startDate']->format('Ymd') != $filter['endDate']->format('Ymd')) {
            $fileDates[] = $filter['endDate']->format('Ymd');
        }

        $filenames[] = implode('-', $fileDates);
        $fileFormat = (strtolower($request->fileType) == 'pdf') ? 'pdf' : 'xlsx';
        $filename = implode('_', $filenames) . ".{$fileFormat}";
        $titleHeader = 'Daftar Bonus Poin ' . ($isShoppingPoint ? 'Belanja Pribadi' : 'Aktifasi Member');

        if ($fileFormat == 'pdf') {
            return Pdf::loadView('main.bonuses.points.pdf', [
                'filename' => $filename,
                'rows' => $rows,
                'startDate' => $filter['startDate'],
                'endDate' => $filter['endDate'],
                'titleHeader' => $titleHeader,
                'isShoppingPoint' => $isShoppingPoint,
            ])->stream($filename);
        }

        return (new ExcelListPoint(
            $rows,
            $isShoppingPoint,
            $filter['startDate'],
            $filter['endDate'],
            $titleHeader
        ))->download($filename);
    }
}
