<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

    private function index(string $bonusTitle, array $breadcrumbs, string $dataUrl, string $totalUrl)
    {
        return view('main.bonuses.mitra.index', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => $breadcrumbs,
            'dateRange' => $this->dateFilter(),
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
        ]);
    }

    private function datatable(Request $request, array|int $bonusType)
    {
        if (!is_array($bonusType)) $bonusType = [$bonusType];

        $strType = implode(', ', $bonusType);

        $filter = $this->getQueryFilter($request);
        $formatStart = $filter['formatStart'];
        $formatEnd = $filter['formatEnd'];

        $query = DB::table('user_bonuses')
            ->join('users', 'users.id', '=', 'user_bonuses.user_id')
            ->selectRaw("user_bonuses.bonus_date, user_bonuses.bonus_type, user_bonuses.user_id, users.name, users.username, users.phone, SUM(user_bonuses.bonus_amount) as total_bonus")
            ->whereRaw("user_bonuses.bonus_type in({$strType})")
            ->whereRaw("user_bonuses.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}'")
            ->groupByRaw('user_bonuses.bonus_date, user_bonuses.bonus_type, user_bonuses.user_id, users.name, users.username, users.phone')
            ->toSql();

        session([
            'filter.dates' => ['start' => $filter['startDate'], 'end' => $filter['endDate']],
        ]);

        return datatables()->query(DB::table(DB::raw("({$query}) as bonusan")))
            ->editColumn('bonus_date', function ($row) {
                return formatDatetime($row->bonus_date, 'j M Y');
            })
            ->editColumn('bonus_type', function ($row) {
                return Arr::get(BONUS_MITRA_NAMES, $row->bonus_type, '-');
            })
            ->editColumn('name', function ($row) {
                $format = '<div>%s</div><div class="small text-muted">%s - %s</div>';

                return new HtmlString(sprintf($format, $row->name, $row->username, $row->phone));
            })
            ->editColumn('total_bonus', function ($row) {
                return formatNumber($row->total_bonus);
            })
            ->escapeColumns()
            ->toJson();
    }

    private function totalBonus(Request $request, array|int $bonusType)
    {
        if (!is_array($bonusType)) $bonusType = [$bonusType];

        $filter = $this->getQueryFilter($request);
        $formatStart = $filter['formatStart'];
        $formatEnd = $filter['formatEnd'];

        $data = DB::table('user_bonuses')
            ->whereIn('bonus_type', $bonusType)
            ->whereBetween('bonus_date', [$formatStart, $formatEnd]);

        $result = [
            'total' => $data->sum('bonus_amount')
        ];

        return response()->json($result);
    }

    // bonus sponsor
    public function indexBonusSponsor(Request $request)
    {
        $bonusTitle = 'Bonus Sponsor';
        $breadcrumbs = ['Bonus', 'Sponsor'];
        $dataUrl = route('main.memberBonus.sponsor.datatable');
        $totalUrl = route('main.memberBonus.sponsor.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl);
    }

    public function datatableBonusSponsor(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_SPONSOR);
    }

    public function totalBonusSponsor(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_SPONSOR);
    }

    // bonus sponsor
    public function indexBonusSponsorRO(Request $request)
    {
        $bonusTitle = 'Bonus Sponsor RO';
        $breadcrumbs = ['Bonus', 'Sponsor RO'];
        $dataUrl = route('main.memberBonus.sponsor-ro.datatable');
        $totalUrl = route('main.memberBonus.sponsor-ro.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl);
    }

    public function datatableBonusSponsorRO(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_RO);
    }

    public function totalBonusSponsorRO(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_RO);
    }

    // bonus cashback
    public function indexBonusCashback(Request $request)
    {
        $bonusTitle = 'Bonus Cashback';
        $breadcrumbs = ['Bonus', 'Cashback'];
        $dataUrl = route('main.memberBonus.cashback.datatable');
        $totalUrl = route('main.memberBonus.cashback.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl);
    }

    public function datatableBonusCashback(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_CASHBACK_RO);
    }

    public function totalBonusCashback(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_CASHBACK_RO);
    }

    // bonus point ro
    public function indexBonusPointRO(Request $request)
    {
        $bonusTitle = 'Bonus Point RO';
        $breadcrumbs = ['Bonus', 'Point RO'];
        $dataUrl = route('main.memberBonus.point-ro.datatable');
        $totalUrl = route('main.memberBonus.point-ro.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl);
    }

    public function datatableBonusPointRO(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_POINT_RO);
    }

    public function totalBonusPointRO(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_POINT_RO);
    }

    // bonus Generasi
    public function indexBonusGenerasi(Request $request)
    {
        $bonusTitle = 'Bonus Generasi';
        $breadcrumbs = ['Bonus', 'Generasi'];
        $dataUrl = route('main.memberBonus.generasi.datatable');
        $totalUrl = route('main.memberBonus.generasi.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl);
    }

    public function datatableBonusGenerasi(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_GENERASI);
    }

    public function totalBonusGenerasi(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_GENERASI);
    }

    // bonus Generasi
    public function indexBonusPrestasi(Request $request)
    {
        $bonusTitle = 'Bonus Prestasi';
        $breadcrumbs = ['Bonus', 'Prestasi'];
        $dataUrl = route('main.memberBonus.prestasi.datatable');
        $totalUrl = route('main.memberBonus.prestasi.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl);
    }

    public function datatableBonusPrestasi(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_PRESTASI);
    }

    public function totalBonusPrestasi(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_PRESTASI);
    }
}
