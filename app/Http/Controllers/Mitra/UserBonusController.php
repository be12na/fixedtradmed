<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\UserBonus;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class UserBonusController extends Controller
{
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

    private function getQueryBonus(Request $request, array|int $type, Closure $with = null, bool $includeFromUser = false)
    {
        if (!is_array($type)) $type = [$type];

        $user = $request->user();
        $filter = $this->getQueryFilter($request);

        $eloquent = UserBonus::query()->byUser($user)
            ->byType($type)
            ->byDates($filter['startDate'], $filter['endDate']);

        if (!is_null($with)) {
            $eloquent = $with($eloquent);
        }

        // if (!in_array($type, [BONUS_MITRA_GENERASI, BONUS_MITRA_PRESTASI])) {
        //     if ($type == BONUS_MITRA_SPONSOR) {
        //         $eloquent = $eloquent->with('product');
        //     } elseif ($type == BONUS_MITRA_CASHBACK) {
        //         $eloquent = $eloquent->with('purchaseProduct', function ($pp) {
        //             return $pp->with('product')->with('purchase');
        //         });
        //     } else {
        //         $eloquent = $eloquent->with('purchaseProduct', function ($pp) {
        //             return $pp->with('product')->with('purchase');
        //         });
        //     }
        // }

        if ($includeFromUser) {
            $eloquent = $eloquent->with('fromUser');
        }

        return [
            'filter' => $filter,
            'eloquent' => $eloquent,
        ];
    }

    // bonus sponsor
    public function indexBonusSponsor(Request $request)
    {
        $dateRange = $this->dateFilter();
        $bonusTitle = 'Bonus Sponsor';
        $dataUrl = route('mitra.bonus.sponsor.datatable');
        $totalUrl = route('mitra.bonus.sponsor.total');

        return view('mitra.bonuses.sponsor', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Sponsor'],
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    public function dataTableBonusSponsor(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_SPONSOR, function ($eloquent) {
            return $eloquent->with('product');
        }, true);

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        return datatables()->eloquent($data['eloquent'])
            ->editColumn('bonus_date', function ($row) {
                return formatFullDate($row->bonus_date);
            })
            ->addColumn('from_member_name', function ($row) {
                if ($fromUser = $row->fromUser) {
                    $format = '<div>%s</div><div class="text-primary fst-italic small">(%s)</div>';

                    return new HtmlString(sprintf($format, $fromUser->name, $fromUser->username));
                }

                return '-';
            })
            ->addColumn('product_name', function ($row) {
                return $row->product ? $row->product->code : '-';
            })
            ->escapeColumns()
            ->toJson();
    }

    public function totalBonusSponsor(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_SPONSOR);

        $result = [
            'total' => $data['eloquent']->sum('bonus_amount')
        ];

        return response()->json($result);
    }

    // bonus sponsor RO
    public function indexBonusSponsorRO(Request $request)
    {
        $dateRange = $this->dateFilter();
        $bonusTitle = 'Bonus Sponsor RO';
        $dataUrl = route('mitra.bonus.sponsor-ro.datatable');
        $totalUrl = route('mitra.bonus.sponsor-ro.total');

        return view('mitra.bonuses.sponsor-ro', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Sponsor RO'],
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    public function dataTableBonusSponsorRO(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_RO, function ($eloquent) {
            return $eloquent->with('purchaseProduct', function ($pp) {
                return $pp->with('product')->with('purchase');
            });
        }, true);

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        return datatables()->eloquent($data['eloquent'])
            ->editColumn('bonus_date', function ($row) {
                return formatFullDate($row->bonus_date);
            })
            ->addColumn('from_member_name', function ($row) {
                if ($fromUser = $row->fromUser) {
                    $format = '<div>%s</div><div class="text-primary fst-italic small">(%s)</div>';

                    return new HtmlString(sprintf($format, $fromUser->name, $fromUser->username));
                }

                return '-';
            })
            ->addColumn('product_name', function ($row) {
                if ($row->purchaseProduct && $row->purchaseProduct->product) {
                    return $row->purchaseProduct->product->code . ' x ' . $row->purchaseProduct->product_qty;
                }

                return '-';
            })
            ->escapeColumns()
            ->toJson();
    }

    public function totalBonusSponsorRO(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_RO);

        $result = [
            'total' => $data['eloquent']->sum('bonus_amount')
        ];

        return response()->json($result);
    }

    // bonus cashback
    public function indexBonusCashback(Request $request)
    {
        $dateRange = $this->dateFilter();
        $bonusTitle = 'Bonus Cashback';
        $dataUrl = route('mitra.bonus.cashback.datatable');
        $totalUrl = route('mitra.bonus.cashback.total');

        return view('mitra.bonuses.cashback', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Cashback'],
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    public function dataTableBonusCashback(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_CASHBACK_RO, function ($query) {
            return $query->with('purchaseProduct', function ($pp) {
                return $pp->with('product');
            });
        });

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        return datatables()->eloquent($data['eloquent'])
            ->editColumn('bonus_date', function ($row) {
                return formatFullDate($row->bonus_date);
            })
            ->editColumn('bonus_type', function ($row) {
                return Arr::get(BONUS_MITRA_NAMES, $row->bonus_type, '-');
            })
            ->addColumn('purchase_code', function ($row) {
                return ($row->purchaseProduct && $row->purchaseProduct->purchase) ? $row->purchaseProduct->purchase->code : '-';
            })
            ->addColumn('product_name', function ($row) {
                if ($row->purchaseProduct && $row->purchaseProduct->product) {
                    return $row->product->name . ' x ' . $row->purchaseProduct->product_qty;
                }

                return '-';
            })
            ->escapeColumns()
            ->toJson();
    }

    public function totalBonusCashback(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_CASHBACK_RO);

        $result = [
            'total' => $data['eloquent']->sum('bonus_amount')
        ];

        return response()->json($result);
    }

    // bonus point ro
    public function indexBonusPointRO(Request $request)
    {
        $dateRange = $this->dateFilter();
        $bonusTitle = 'Bonus Point RO';
        $dataUrl = route('mitra.bonus.point-ro.datatable');
        $totalUrl = route('mitra.bonus.point-ro.total');
        $user = $request->user();

        return view('mitra.bonuses.point-ro', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Point RO'],
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
            'totalPoint' => $user->total_point_ro,
        ]);
    }

    public function dataTableBonusPointRO(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_POINT_RO);

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        return datatables()->eloquent($data['eloquent'])
            ->editColumn('bonus_date', function ($row) {
                return formatFullDate($row->bonus_date);
            })
            ->escapeColumns()
            ->toJson();
    }

    public function totalBonusPointRO(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_POINT_RO);

        $result = [
            'total' => $data['eloquent']->sum('bonus_amount')
        ];

        return response()->json($result);
    }

    // bonus generasi
    public function indexBonusGenerasi(Request $request)
    {
        $dateRange = $this->dateFilter();
        $bonusTitle = 'Bonus Generasi';
        $dataUrl = route('mitra.bonus.generasi.datatable');
        $totalUrl = route('mitra.bonus.generasi.total');

        return view('mitra.bonuses.generasi', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Generasi'],
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    public function dataTableBonusGenerasi(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_GENERASI, null, true);

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        return datatables()->eloquent($data['eloquent'])
            ->editColumn('bonus_date', function ($row) {
                return formatFullDate($row->bonus_date);
            })
            ->addColumn('from_member_name', function ($row) {
                if ($fromUser = $row->fromUser) {
                    $format = '<div>%s</div><div class="text-primary fst-italic small">(%s)</div>';

                    return new HtmlString(sprintf($format, $fromUser->name, $fromUser->username));
                }

                return '-';
            })
            ->escapeColumns()
            ->toJson();
    }

    public function totalBonusGenerasi(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_GENERASI);

        $result = [
            'total' => $data['eloquent']->sum('bonus_amount')
        ];

        return response()->json($result);
    }

    // bonus generasi
    public function indexBonusPrestasi(Request $request)
    {
        $dateRange = $this->dateFilter();
        $bonusTitle = 'Bonus Prestasi';
        $dataUrl = route('mitra.bonus.prestasi.datatable');
        $totalUrl = route('mitra.bonus.prestasi.total');

        return view('mitra.bonuses.prestasi', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Prestasi'],
            'dateRange' => $dateRange,
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    public function dataTableBonusPrestasi(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_PRESTASI, null, true);

        session([
            'filter.dates' => ['start' => $data['filter']['startDate'], 'end' => $data['filter']['endDate']],
        ]);

        return datatables()->eloquent($data['eloquent'])
            ->editColumn('bonus_date', function ($row) {
                return formatFullDate($row->bonus_date);
            })
            ->addColumn('from_member_name', function ($row) {
                if ($fromUser = $row->fromUser) {
                    $format = '<div>%s</div><div class="text-primary fst-italic small">(%s)</div>';

                    return new HtmlString(sprintf($format, $fromUser->name, $fromUser->username));
                }

                return '-';
            })
            ->escapeColumns()
            ->toJson();
    }

    public function totalBonusPrestasi(Request $request)
    {
        $data = $this->getQueryBonus($request, BONUS_MITRA_PRESTASI);

        $result = [
            'total' => $data['eloquent']->sum('bonus_amount')
        ];

        return response()->json($result);
    }
}
