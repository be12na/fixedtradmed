<?php

namespace App\Http\Controllers\Main;

use App\Helpers\AppStructure;
use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\BonusMember;
use App\Models\Branch;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\WriterInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Spatie\SimpleExcel\SimpleExcelWriter;

class PaymentController extends Controller
{
    private Neo $neo;
    private AppStructure $appStructure;

    public function __construct()
    {
        $this->neo = app('neo');
        $this->appStructure = app('appStructure');
    }

    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $dateRange = session('filter.dates', []);
        $today = Carbon::today();

        if (empty($dateRange)) {
            $dateRange = [
                'start' => (clone $today)->startOfWeek(),
                'end' => $today
            ];
        }

        if ($dateRange['start']->format('Y-m-d') > date('Y-m-d')) $dateRange['start'] = $today;
        if ($dateRange['end']->format('Y-m-d') > date('Y-m-d')) $dateRange['end'] = $today;

        $currentBranchId = session('filter.branchId', -1);
        $currentBankCode = session('filter.bankCode');
        $currentStatusId = session('filter.paymentStatusId', PAYMENT_STATUS_PENDING);

        return view('main.payments.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentBankCode' => $currentBankCode,
            'currentStatusId' => $currentStatusId,
            'windowTitle' => 'Daftar Pembayaran Setoran',
            'breadcrumbs' => ['Pembayaran', 'Setoran', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));

        if ($startDate->format('Ymd') > $endDate->format('Ymd')) {
            $tmpEnd = $startDate;
            $startDate = clone $endDate;
            $endDate = $tmpEnd;
        }

        $branchId = intval($request->get('branch_id', -1));
        $bankCode = $request->get('bank_code');
        $statusId = $request->get('status_id', -1);
        $sumRejected = ($statusId == PAYMENT_STATUS_REJECTED) ? 1 : 0;
        $statusList = [PAYMENT_STATUS_APPROVED, PAYMENT_STATUS_REJECTED, PAYMENT_STATUS_TRANSFERRED];

        $baseQuery = DB::table('branch_payments')
            ->join('users', 'users.id', '=', 'branch_payments.manager_id')
            ->join('branches', 'branches.id', '=', 'branch_payments.branch_id')
            ->selectRaw("
                branch_payments.id,
                branch_payments.code as kode,
                branch_payments.payment_date,
                concat(branch_payments.payment_date, '-', branch_payments.id) as tanggal, 
                branch_payments.bank_name,
                branch_payments.transfer_status,
                branch_payments.transfer_status as status,
                branch_payments.total_price,
                branch_payments.total_discount,
                branch_payments.unique_digit,
                branch_payments.total_transfer,
                users.name as manager_name,
                branches.name as branch_name,
                {$sumRejected} as sum_rejected
            ")
            ->whereBetween('branch_payments.payment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('branch_payments.transfer_status', $statusList);

        if ($branchId > 0) {
            $baseQuery = $baseQuery->where('branch_payments.branch_id', '=', $branchId);
        }

        if (!empty($bankCode)) {
            $baseQuery = $baseQuery->where('branch_payments.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, $statusList)) {
            $baseQuery = $baseQuery->where('branch_payments.transfer_status', '=', $statusId);
        }

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.branchId' => $branchId,
            'filter.bankCode' => $bankCode,
            'filter.paymentStatusId' => $statusId,
        ]);

        $result = datatables()->query($baseQuery)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->payment_date);
            })
            ->editColumn('transfer_status', function ($row) {
                $cls = 'bg-light';
                if ($row->transfer_status == PAYMENT_STATUS_REJECTED) {
                    $cls = 'bg-danger text-light';
                } elseif ($row->transfer_status == PAYMENT_STATUS_APPROVED) {
                    $cls = 'bg-success text-light';
                } elseif ($row->transfer_status == PAYMENT_STATUS_TRANSFERRED) {
                    $cls = 'bg-warning';
                }

                $cls .= ' py-1 px-2';
                $statusName = Arr::get(PAYMENT_STATUS_LIST, $row->transfer_status);

                $html = "<span class=\"d-inline-block {$cls}\">{$statusName}</span>";

                return new HtmlString($html);
            })
            ->addColumn('view', function ($row) {
                $routeView = route('main.payments.detail', ['branchPayment' => $row->id]);
                $buttonView = "<button type=\"button\" class=\"btn btn-sm btn-outline-success\" onclick=\"window.location.href='{$routeView}';\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString($buttonView);
            })->escapeColumns(['view']);

        return $result->toJson();
    }

    public function detail(Request $request)
    {
        return view('main.payments.detail', [
            'branchPayment' => $request->branchPayment,
            'windowTitle' => 'Detail Pembayaran Setoran',
            'breadcrumbs' => ['Pembayaran', 'Setoran', 'Detail']
        ]);
    }

    public function actionTransfer(Request $request)
    {
        $branchPayment = $request->branchPaymentApproving;
        $mode = intval($request->get('action_mode', PAYMENT_STATUS_TRANSFERRED));

        $responCode = 200;
        $responText = route('main.payments.index');

        if (!in_array($mode, [PAYMENT_STATUS_APPROVED, PAYMENT_STATUS_REJECTED])) {
            $responText = view('partials.alert', [
                'message' => 'Proses tidak dikenali.',
                'messageClass' => 'danger'
            ])->render();

            return response($responText, $responCode);
        }

        $values = [
            'transfer_status' => $mode,
        ];

        $msg = 'dikonfirmasi';

        $valid = true;

        if ($mode == PAYMENT_STATUS_REJECTED) {
            $msg = 'ditolak';
            $values['status_note'] = $request->get('status_note');
            $validator = Validator::make($values, [
                'status_note' => 'required|string|max:250'
            ], [], [
                'status_note' => 'Keterangan'
            ]);

            if ($validator->fails()) {
                $valid = false;
                $responCode = 400;
                $responText = $this->validationMessages($validator);
            }
        }

        if ($valid === true) {
            DB::beginTransaction();
            try {
                $branchPayment->timestamps = false;
                $values['status_at'] = date('Y-m-d H:i:s');
                $values['status_by'] = $request->user()->id;
                $branchPayment->update($values);

                DB::commit();

                session([
                    'message' => "Data transfer pembayaran berhasil {$msg}.",
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi.' . (!isLive() ? ' ==> ' . $e->getMessage() : ''),
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }

    // download
    private function downloadQuery(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));

        $branchId = intval($request->get('branch_id', -1));
        $branches = Branch::byActive();
        if ($branchId != -1) $branches = $branches->byId($branchId);
        $branches = $branches->get();
        $bankCode = $request->get('bank_code');
        $statusId = $request->get('status_id', -1);

        $query = DB::table('branch_payments')
            ->join('users', 'users.id', '=', 'branch_payments.manager_id')
            ->join('branches', 'branches.id', '=', 'branch_payments.branch_id')
            ->selectRaw("
                branch_payments.id,
                branch_payments.code,
                branch_payments.payment_date,
                branch_payments.bank_name,
                branch_payments.transfer_status,
                branch_payments.total_omzets,
                branch_payments.total_crews,
                branch_payments.total_foundations,
                branch_payments.total_savings,
                0 as total_bonus,
                branch_payments.discount_amount,
                branch_payments.omzet_used,
                branch_payments.unique_digit,
                branch_payments.total_transfer,
                users.name as manager_name,
                users.position_ext as manager_position,
                branch_payments.branch_id,
                branches.name as branch_name
            ")
            ->whereBetween('branch_payments.payment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($branchId != -1) {
            $query = $query->where('branch_payments.branch_id', '=', $branchId);
        }

        if (!empty($bankCode)) {
            $query = $query->where('branch_payments.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, [PAYMENT_STATUS_PENDING, PAYMENT_STATUS_APPROVED, PAYMENT_STATUS_REJECTED])) {
            $query = $query->where('branch_payments.transfer_status', '=', $statusId);
        }

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'branches' => $branches,
            'bankCode' => $bankCode,
            'statusId' => $statusId,
            'rows' => $query
                ->orderBy('branches.name')
                ->orderBy('branch_payments.payment_date')
                ->orderBy('users.position_ext')
                ->orderBy('users.name')
                ->orderBy('branch_payments.id')
                ->get()
        ];
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->downloadQuery($request);
        $rows = $data->rows;

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $startFormatted = $data->startDate->format('Ymd');
        $endFormatted = $data->endDate->format('Ymd');
        $tglName = $startFormatted;
        $tglReport = formatFullDate($data->startDate);

        if ($startFormatted != $endFormatted) {
            if ($startFormatted > $endFormatted) {
                $tglName = "{$endFormatted}-{$startFormatted}";
                $tglReport = formatFullDate($data->endDate) . ' s/d ' . $tglReport;
            } else {
                $tglName = "{$startFormatted}-{$endFormatted}";
                $tglReport = $tglReport . ' s/d ' . formatFullDate($data->endDate);
            }
        }

        $downloadName = "Transfer-{$tglName}";

        $titleBranch = '';
        if (($branchId = $data->branchId) > 0) {
            $branch = $data->branches->first();
            $titleBranch = $branch->name;
            $downloadName .= '-' . str_replace(' ', '_', $titleBranch);
        }

        $bankCode = $data->bankCode;
        if (!empty($data->bankCode)) {
            $downloadName .= '-' . str_replace(' ', '_', $bankCode);
        }

        $statusName = '';
        if (($statusId = $data->statusId) > -1) {
            $statusName = Arr::get(PAYMENT_STATUS_LIST, $data->statusId);
            if ($statusName) {
                $downloadName .= '-' . str_replace(' ', '_', $statusName);
            }
        }

        $downloadName = $downloadName . ".xlsx";

        SimpleExcelWriter::streamDownload($downloadName, 'xlsx', function (WriterInterface $writer) use ($downloadName, $tglReport, $titleBranch, $branchId, $bankCode, $statusName, $statusId, $rows) {

            $writer->openToBrowser($downloadName);

            $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->build();
            $titleRedStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->setFontColor(Color::WHITE)->setBackgroundColor(Color::RED)->build();
            $titleWarnStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->setBackgroundColor(Color::YELLOW)->build();
            $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->setCellAlignment('center')->setBackgroundColor(Color::LIGHT_BLUE)->build();
            $headerSmallStyle = (new StyleBuilder)->setFontBold()->setFontSize(8)->setCellAlignment('center')->setBackgroundColor(Color::LIGHT_BLUE)->build();
            $rowStyle = (new StyleBuilder)->setFontSize(10)->build();
            $rowBoldStyle = (new StyleBuilder)->setFontSize(10)->setFontBold()->build();
            $rowRedStyle = (new StyleBuilder)->setFontSize(10)->setFontColor(Color::WHITE)->setBackgroundColor(Color::RED)->build();
            $rowWarnStyle = (new StyleBuilder)->setFontSize(10)->setBackgroundColor(Color::YELLOW)->build();

            $printBranch = ($branchId <= 0);

            $writer->addRow(new Row([new Cell('')], null));
            $writer->addRow(new Row([new Cell('LAPORAN TRANSFER')], $titleStyle));
            $writer->addRow(new Row([new Cell('')], null));
            $writer->addRow(new Row([
                new Cell('Tanggal'),
                new Cell(':'),
                new Cell($tglReport)
            ], $titleStyle));

            $headerColumns = [];

            if (!$printBranch && !empty($titleBranch)) {
                $writer->addRow(new Row([
                    new Cell('Cabang'),
                    new Cell(':'),
                    new Cell($titleBranch)
                ], $titleStyle));
            } else {
                $headerColumns[] = 'Cabang';
            }

            $headerColumns = array_merge($headerColumns, [
                'Tanggal', 'Kode', 'Manager', 'Posisi',
            ]);

            $printBank = true;
            if (!empty($bankCode)) {
                $writer->addRow(new Row([
                    new Cell('Bank'),
                    new Cell(':'),
                    new Cell(Arr::get(BANK_TRANSFER_LIST, $bankCode))
                ], $titleStyle));

                $printBank = false;
            } else {
                $headerColumns[] = 'Bank';
            }

            $headerColumns = array_merge($headerColumns, [
                'Pemakaian Omzet', 'Savings', 'Total Omzet', 'Total Transfer',
            ]);

            $printStatus = true;
            if (!empty($statusName)) {
                $titleStatusStyle = $titleStyle;
                if ($statusId == PAYMENT_STATUS_PENDING) {
                    $titleStatusStyle = $titleWarnStyle;
                } elseif ($statusId == PAYMENT_STATUS_REJECTED) {
                    $titleStatusStyle = $titleRedStyle;
                }

                $writer->addRow(new Row([
                    new Cell('Status', $titleStyle),
                    new Cell(':', $titleStyle),
                    new Cell($statusName, $titleStatusStyle)
                ], null));

                $printStatus = false;
            } else {
                $headerColumns[] = 'Status';
            }

            $headerNumbers = [];
            $lastColumn = count($headerColumns) - 1;
            foreach (array_keys($headerColumns) as $index) {
                $col = $index + 1;
                $colText = strval($col);
                if ($col == $lastColumn) {
                    $formula = ($col - 1) . ' - ' . ($col - 3) . ' - ' . ($col - 2);
                    $colText .= " = {$formula}";
                }

                $headerNumbers[] = $colText;
            }

            $headerColumnCells = [];
            $headerNumberCells = [];

            foreach ($headerColumns as $key => $text) {
                $headerColumnCells[] = new Cell($text);
                $headerNumberCells[] = new Cell($headerNumbers[$key]);
            }

            $writer->addRow(new Row([new Cell('')], null));
            $writer->addRow(new Row($headerColumnCells, $headerStyle));
            $writer->addRow(new Row($headerNumberCells, $headerSmallStyle));

            $appStructure = app('appStructure');
            $rowBranchId = 0;

            foreach ($rows as $row) {
                $cells = [];

                if ($printBranch) {
                    if (($changeBranch = ($row->branch_id != $rowBranchId)) && ($rowBranchId > 0)) {
                        $writer->addRow(new Row([new Cell('')], null));
                    }

                    $cells[] = new Cell($changeBranch ? $row->branch_name : '', $rowBoldStyle);
                }

                $style = $rowStyle;
                if ($printStatus) {
                    if ($row->transfer_status == PAYMENT_STATUS_REJECTED) {
                        $style = $rowRedStyle;
                    } elseif ($row->transfer_status == PAYMENT_STATUS_PENDING) {
                        $style = $rowWarnStyle;
                    }
                }

                $values = [
                    new Cell($row->payment_date, $style),
                    new Cell($row->code, $style),
                    new Cell($row->manager_name, $style),
                    new Cell($appStructure->nameById(false, intval($row->manager_position)), $style),
                ];

                if ($printBank) {
                    $values[] = new Cell($row->bank_name, $style);
                }

                $values = array_merge($values, [
                    new Cell(intval($row->omzet_used), $style),
                    new Cell(intval($row->total_savings), $style),
                    new Cell(intval($row->total_omzets), $style),
                    new Cell(intval($row->total_transfer), $style),
                ]);

                if ($printStatus) {
                    $values[] = new Cell(Arr::get(PAYMENT_STATUS_LIST, $row->transfer_status), $style);
                }

                $cells = array_merge($cells, $values);
                $writer->addRow(new Row($cells, null));

                $rowBranchId = intval($row->branch_id);
            }
        })->toBrowser();
    }
}
