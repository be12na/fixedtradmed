<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\UserWithdraw;
use App\Notifications\WithdrawBonusReferralNotification;
use App\Reports\Excel\SummaryWithdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class UserWithdrawController extends Controller
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

    private function index(string $bonusTitle, array $breadcrumbs, string $dataUrl, string $totalUrl, string $strBonusType)
    {
        $transferUrl = route('main.withdraw.transfer.index');

        return view('main.withdraw.index', [
            'windowTitle' => $bonusTitle,
            'breadcrumbs' => $breadcrumbs,
            'dateRange' => $this->dateFilter(),
            'dataUrl' => $dataUrl,
            'totalUrl' => $totalUrl,
            'transferUrl' => "{$transferUrl}?type={$strBonusType}",
        ]);
    }

    private function datatable(Request $request, array|int $bonusType, array|int $status = [], bool $withRole = True)
    {
        if (!is_array($bonusType)) $bonusType = [$bonusType];
        if (empty($status)) $status = CLAIM_STATUS_PENDING;
        if (!is_array($status)) $status = [$status];

        $strType = implode(', ', $bonusType);

        $filter = $this->getQueryFilter($request);
        $hasRole = ($withRole && hasPermission('main.withdraw.transfer.index'));
        $processing = implode(',', $status);
        $formatStart = $filter['formatStart'];
        $formatEnd = $filter['formatEnd'];

        $query = DB::table('user_withdraws')
            ->join('users', 'users.id', '=', 'user_withdraws.user_id')
            ->selectRaw("user_withdraws.id, user_withdraws.wd_code, user_withdraws.user_id, user_withdraws.wd_date, user_withdraws.bank_code, user_withdraws.bank_name, user_withdraws.bank_acc_no, user_withdraws.bank_acc_name, user_withdraws.wd_bonus_type, user_withdraws.total_bonus, user_withdraws.fee, user_withdraws.total_transfer, user_withdraws.status, user_withdraws.status_at, users.username, users.name, users.phone")
            ->whereRaw("user_withdraws.wd_bonus_type in({$strType})")
            ->whereRaw("user_withdraws.status in({$processing})")
            ->whereRaw("user_withdraws.wd_date BETWEEN '{$formatStart}' AND '{$formatEnd}'")
            ->toSql();

        session([
            'filter.dates' => ['start' => $filter['startDate'], 'end' => $filter['endDate']],
        ]);

        $result = datatables()->query(DB::table(DB::raw("({$query}) as wd")))
            ->editColumn('wd_date', function ($row) {
                return formatDatetime($row->wd_date, 'j M Y');
            })
            ->editColumn('name', function ($row) {
                $format = '<div>%s</div><div class="small text-muted">%s - %s</div>';

                return new HtmlString(sprintf($format, $row->name, $row->username, $row->phone));
            })
            ->editColumn('bank_name', function ($row) {
                $format = '<div>%s</div><div class="text-muted">%s</div><div class="small text-muted">%s</div>';

                return new HtmlString(sprintf($format, $row->bank_code, $row->bank_acc_no, $row->bank_acc_name));
            })
            ->editColumn('wd_bonus_type', function ($row) {
                return Arr::get(BONUS_MITRA_NAMES, $row->wd_bonus_type, '-');
            })
            ->editColumn('total_bonus', function ($row) {
                return formatNumber($row->total_bonus);
            })
            ->editColumn('fee', function ($row) {
                return formatNumber($row->fee);
            })
            ->editColumn('status', function ($row) {
                $statusName = Arr::get(CLAIM_STATUS_LIST, $row->status, '-');
                $statusBgCls = ($row->status == CLAIM_STATUS_PENDING)
                    ? 'warning'
                    : (($row->status == CLAIM_STATUS_FINISH)
                        ? 'success'
                        : 'danger');

                $statusTextCls = ($row->status == CLAIM_STATUS_PENDING)
                    ? 'dark'
                    : 'light';

                $format = '<span class="py-1 px-2 bg-%s text-%s">%s</span>';

                return new HtmlString(sprintf($format, $statusBgCls, $statusTextCls, $statusName));
            })
            ->editColumn('total_transfer', function ($row) {
                return formatNumber($row->total_transfer);
            });

        if ($hasRole) {
            $result = $result->addColumn('check', function ($row) {
                $check = '<input type="checkbox" class="check-row" value="' . $row->id . '" style="margin-right:2px;">';
                $check = new HtmlString($check);

                return $check;
            });
        }

        return $result->escapeColumns()->toJson();
    }

    private function totalBonus(Request $request, array|int $bonusType, array|int $status = [])
    {
        if (!is_array($bonusType)) $bonusType = [$bonusType];
        if (empty($status)) $status = CLAIM_STATUS_PENDING;
        if (!is_array($status)) $status = [$status];

        $filter = $this->getQueryFilter($request);
        $formatStart = $filter['formatStart'];
        $formatEnd = $filter['formatEnd'];

        $data = UserWithdraw::query()
            ->byBonusType($bonusType)
            ->byTransStatus($status)
            ->byDateBetween($formatStart, $formatEnd);

        $result = [
            'total' => $data->sum('total_bonus')
        ];

        return response()->json($result);
    }

    // bonus sponsor
    public function indexBonusSponsor(Request $request)
    {
        $bonusTitle = 'Withdraw Bonus Sponsor';
        $breadcrumbs = ['Withdraw', 'Bonus', 'Sponsor'];
        $dataUrl = route('main.withdraw.sponsor.datatable');
        $totalUrl = route('main.withdraw.sponsor.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl, 'sponsor');
    }

    public function dataTableBonusSponsor(Request $request)
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
        $bonusTitle = 'Withdraw Bonus Sponsor RO';
        $breadcrumbs = ['Withdraw', 'Bonus', 'Sponsor RO'];
        $dataUrl = route('main.withdraw.sponsor-ro.datatable');
        $totalUrl = route('main.withdraw.sponsor-ro.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl, 'sponsor-ro');
    }

    public function dataTableBonusSponsorRO(Request $request)
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
        $bonusTitle = 'Withdraw Bonus Cashback';
        $breadcrumbs = ['Withdraw', 'Bonus', 'Cashback'];
        $dataUrl = route('main.withdraw.cashback.datatable');
        $totalUrl = route('main.withdraw.cashback.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl, 'cashback');
    }

    public function dataTableBonusCashback(Request $request)
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
        $bonusTitle = 'Withdraw Bonus Point RO';
        $breadcrumbs = ['Withdraw', 'Bonus', 'Point RO'];
        $dataUrl = route('main.withdraw.point-ro.datatable');
        $totalUrl = route('main.withdraw.point-ro.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl, 'point-ro');
    }

    public function dataTableBonusPointRO(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_POINT_RO);
    }

    public function totalBonusPointRO(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_POINT_RO);
    }

    // bonus cashback
    public function indexBonusGenerasi(Request $request)
    {
        $bonusTitle = 'Withdraw Bonus Generasi';
        $breadcrumbs = ['Withdraw', 'Bonus', 'Generasi'];
        $dataUrl = route('main.withdraw.generasi.datatable');
        $totalUrl = route('main.withdraw.generasi.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl, 'generasi');
    }

    public function dataTableBonusGenerasi(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_GENERASI);
    }

    public function totalBonusGenerasi(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_GENERASI);
    }

    // bonus prestasi
    public function indexBonusPrestasi(Request $request)
    {
        $bonusTitle = 'Withdraw Bonus Prestasi';
        $breadcrumbs = ['Withdraw', 'Bonus', 'Prestasi'];
        $dataUrl = route('main.withdraw.prestasi.datatable');
        $totalUrl = route('main.withdraw.prestasi.total');

        return $this->index($bonusTitle, $breadcrumbs, $dataUrl, $totalUrl, 'prestasi');
    }

    public function dataTableBonusPrestasi(Request $request)
    {
        return $this->datatable($request, BONUS_MITRA_PRESTASI);
    }

    public function totalBonusPrestasi(Request $request)
    {
        return $this->totalBonus($request, BONUS_MITRA_PRESTASI);
    }

    public function transfer(Request $request)
    {
        $bonusType = $request->get('type');

        if (!in_array($bonusType, ['sponsor', 'sponsor-ro', 'cashback', 'point-ro', 'generasi', 'prestasi'])) {
            return response('Jenis withdraw bonus tidak tersedia.', 400);
        }

        if (!$request->isMethod('POST')) {
            $ids = $request->get('ids');
            if (empty($ids)) {
                return response('Silahkan pilih data yang akan diproses.', 400);
            }

            $checks = explode(',', $ids);

            return view('main.withdraw.transfer-comfirm', [
                'bonusType' => $bonusType,
                'checks' => $checks,
            ]);
        }

        $bonusIds = $request->get('checks', []);
        if (count($bonusIds) == 0) {
            return response('Bonus tidak tersedia.', 400);
        }

        $values = [
            'status' => CLAIM_STATUS_FINISH,
            'status_at' => Carbon::now(),
        ];

        $responCode = 200;
        $responText = route("main.withdraw.{$bonusType}.index");

        $withdraws = UserWithdraw::query()
            ->byTransStatus(CLAIM_STATUS_PENDING)
            ->byId($bonusIds)
            ->with('user')
            ->get();

        DB::beginTransaction();
        try {
            foreach ($withdraws as $userWithdraw) {
                $userWithdraw->update($values);
                $user = $userWithdraw->user;

                $user->notify(new WithdrawBonusReferralNotification($user, 'database', [
                    'driver' => 'mail',
                    'id' => $userWithdraw->id
                ]));
                $user->notify(new WithdrawBonusReferralNotification($user, 'database', [
                    'driver' => 'onesender',
                    'id' => $userWithdraw->id
                ]));
            }

            DB::commit();

            session([
                'message' => 'Penarikan bonus yang ditransfer berhasil disimpan.',
                'messageClass' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $responText = view('partials.alert', [
                'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi',
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }

    // history
    public function indexHistories(Request $request)
    {
        $status = session('wd.status', CLAIM_STATUS_FINISH);

        return view('main.withdraw.history', [
            'windowTitle' => 'Riwayat Withdraw ',
            'breadcrumbs' => ['Withdraw', 'Riwayat'],
            'dateRange' => $this->dateFilter(),
            'dataUrl' => route('main.withdraw.histories.dataTable'),
            'totalUrl' => route('main.withdraw.histories.total'),
            'status' => $status
        ]);
    }

    private function sessionStatus(Request $request): array
    {
        $status = $request->get('select_status', CLAIM_STATUS_FINISH);
        session()->put('wd.status', $status);

        return ($status == -1) ? [CLAIM_STATUS_PENDING, CLAIM_STATUS_FINISH] : [$status];
    }

    private $historyList = [BONUS_MITRA_SPONSOR, BONUS_MITRA_CASHBACK_RO, BONUS_MITRA_POINT_RO,  BONUS_MITRA_PRESTASI];

    public function dataTableHistories(Request $request)
    {
        $statusList = $this->sessionStatus($request);

        return $this->datatable($request, $this->historyList, $statusList, false);
    }

    public function totalHistories(Request $request)
    {
        $statusList = $this->sessionStatus($request);

        return $this->totalBonus($request, $this->historyList, $statusList);
    }

    public function downloadHistories(Request $request)
    {
        $filter = $this->getQueryFilter($request);
        $startDate = $filter['startDate'];
        $endDate = $filter['endDate'];
        $formatStart = $filter['formatStart'];
        $formatEnd = $filter['formatEnd'];
        $statusList = $this->sessionStatus($request);
        $typeList = $this->historyList;

        $fileFormat = strtolower($request->fileType);
        if ($fileFormat != 'pdf') {
            $fileFormat = 'xlsx';
        }

        $tgl = $startDate->format('Ymd');

        if ($tgl != $endDate->format('Ymd')) {
            $tgl .= '-' . $endDate->format('Ymd');
        }

        $filename = "Withdraw-{$tgl}.{$fileFormat}";
        $titleHeader = 'Daftar Penarikan Bonus';

        $rows = UserWithdraw::query()
            ->byTransStatus($statusList)
            ->byBonusType($typeList)
            ->byDateBetween($formatStart, $formatEnd)
            ->with('user')
            ->get();

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        return (new SummaryWithdraw(
            $rows,
            $startDate,
            $endDate,
            $titleHeader
        ))->download($filename);
    }
    // history:end
}
