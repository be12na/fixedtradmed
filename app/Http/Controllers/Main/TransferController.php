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

class TransferController extends Controller
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
        $currentStatusId = session('filter.statusId', PROCESS_STATUS_PENDING);

        return view('main.transfers.sales.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentBankCode' => $currentBankCode,
            'currentStatusId' => $currentStatusId,
            'windowTitle' => 'Daftar Transfer Penjualan',
            'breadcrumbs' => ['Transfer', 'Penjualan', 'Daftar']
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
        $sumRejected = ($statusId == PROCESS_STATUS_REJECTED) ? 1 : 0;

        $baseQuery = DB::table('branch_transfers')
            ->join('users', 'users.id', '=', 'branch_transfers.manager_id')
            ->join('branches', 'branches.id', '=', 'branch_transfers.branch_id')
            ->join('branch_members', function ($join) {
                $join->on('branch_members.user_id', '=', 'branch_transfers.manager_id')
                    ->on('branch_members.branch_id', '=', 'branch_transfers.branch_id');
            })
            ->selectRaw("
                branch_transfers.id,
                branch_transfers.code as kode,
                branch_transfers.transfer_date,
                concat(branch_transfers.transfer_date, '-', branch_transfers.id) as tanggal, 
                branch_transfers.bank_name,
                branch_transfers.transfer_status,
                branch_transfers.transfer_status as status,
                branch_transfers.total_omzets,
                branch_transfers.total_crews,
                branch_transfers.total_foundations,
                branch_transfers.total_savings,
                0 as total_bonus,
                branch_transfers.discount_amount,
                branch_transfers.omzet_used,
                branch_transfers.unique_digit,
                branch_transfers.total_transfer,
                users.name as manager_name,
                branch_members.position_ext,
                branch_members.manager_type,
                branches.name as branch_name,
                {$sumRejected} as sum_rejected
            ")
            ->whereBetween('branch_transfers.transfer_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($branchId > 0) {
            $baseQuery = $baseQuery->where('branch_transfers.branch_id', '=', $branchId);
        }

        if (!empty($bankCode)) {
            $baseQuery = $baseQuery->where('branch_transfers.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
            $baseQuery = $baseQuery->where('branch_transfers.transfer_status', '=', $statusId);
        }

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.branchId' => $branchId,
            'filter.bankCode' => $bankCode,
            'filter.statusId' => $statusId,
        ]);

        $structure = $this->appStructure;

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as transferan"))
            ->mergeBindings($baseQuery);

        $result = datatables()->query($query)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->transfer_date);
            })
            ->editColumn('manager_name', function ($row) use ($structure) {
                $content = "<div class=\"d-flex flex-nowrap\">%s</div>";
                $name = "<div>{$row->manager_name}</div>";
                $infos = [];

                $posEx = intval($row->position_ext);
                if (in_array($posEx, [USER_EXT_DIST, USER_EXT_AG])) {
                    $jabatan = $structure->codeById(false, intval($row->position_ext));
                    if (!empty($jabatan)) $infos[] = $jabatan;

                    if ($posEx == USER_EXT_DIST) {
                        $type = Arr::get(USER_BRANCH_MANAGER_CODES, $row->manager_type ?? 'x');
                        if (!empty($type)) $infos[] = $type;
                    }

                    if (!empty($infos)) {
                        $info = implode(' - ', $infos);
                        $name .= "<div class=\"ms-2 text-primary\">{$info}</div>";
                    }
                }

                return new HtmlString(sprintf($content, $name));
            })
            ->editColumn('transfer_status', function ($row) {
                $cls = 'bg-warning';
                if ($row->transfer_status == PROCESS_STATUS_APPROVED) {
                    $cls = 'bg-success text-light';
                } elseif ($row->transfer_status == PROCESS_STATUS_REJECTED) {
                    $cls = 'bg-danger text-light';
                }

                $text = Arr::get(PROCESS_STATUS_LIST, $row->transfer_status);

                $html = "<div class=\"text-center\"><span class=\"py-1 px-2 {$cls}\">{$text}<span></div>";

                return new HtmlString($html);
            })
            ->addColumn('view', function ($row) {
                $routeView = route('main.transfers.sales.detail', ['branchTransfer' => $row->id]);
                $buttonView = "<button type=\"button\" class=\"btn btn-sm btn-outline-success\" onclick=\"window.location.href='{$routeView}';\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString($buttonView);
            })->escapeColumns(['view']);

        return $result->toJson();
    }

    public function detail(Request $request)
    {
        return view('main.transfers.sales.detail', [
            'transfer' => $request->branchTransfer,
            'windowTitle' => 'Detail Transfer Penjualan',
            'breadcrumbs' => ['Transfer', 'Pejualan', 'Detail']
        ]);
    }

    public function actionTransfer(Request $request)
    {
        $transfer = $request->branchTransfer;
        $mode = intval($request->get('action_mode', PROCESS_STATUS_PENDING));

        $responCode = 200;
        $responText = route('main.transfers.sales.index');

        if (!in_array($mode, [PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
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
        $prosesBonus = [];

        if ($mode == PROCESS_STATUS_REJECTED) {
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
                $responText = view('partials.alert', [
                    'message' => $validator->errors()->first(),
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        if ($valid === true) {
            DB::beginTransaction();
            try {
                $transfer->timestamps = false;
                $values['status_at'] = date('Y-m-d H:i:s');
                $values['status_by'] = $request->user()->id;
                $transfer->update($values);

                if ($mode == PROCESS_STATUS_APPROVED) {
                    $bonuses = $this->neo->neoBonuses($transfer);
                    if (!empty($bonuses)) {
                        $prosesBonus = array_merge(
                            $bonuses['bonusRoyalty'],
                            $bonuses['bonusOverride'],
                            $bonuses['bonusTeam'],
                            $bonuses['bonusSale']
                        );

                        foreach ($prosesBonus as $bonus) {
                            BonusMember::create($bonus);
                        }
                    }

                    $omzetValues = $this->neo->omzetFromApprovedTransfer($transfer);
                    if (!empty($omzetValues)) {
                        DB::table('omzet_members')->insert($omzetValues);
                    }
                }

                DB::commit();

                session([
                    'message' => "Data transfer penjualan berhasil {$msg}.",
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

        $query = DB::table('branch_transfers')
            ->join('users', 'users.id', '=', 'branch_transfers.manager_id')
            ->join('branches', 'branches.id', '=', 'branch_transfers.branch_id')
            ->selectRaw("
                branch_transfers.id,
                branch_transfers.code,
                branch_transfers.transfer_date,
                branch_transfers.bank_name,
                branch_transfers.transfer_status,
                branch_transfers.total_omzets,
                branch_transfers.total_crews,
                branch_transfers.total_foundations,
                branch_transfers.total_savings,
                0 as total_bonus,
                branch_transfers.discount_amount,
                branch_transfers.omzet_used,
                branch_transfers.unique_digit,
                branch_transfers.total_transfer,
                users.name as manager_name,
                users.position_ext as manager_position,
                branch_transfers.branch_id,
                branches.name as branch_name
            ")
            ->whereBetween('branch_transfers.transfer_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($branchId != -1) {
            $query = $query->where('branch_transfers.branch_id', '=', $branchId);
        }

        if (!empty($bankCode)) {
            $query = $query->where('branch_transfers.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
            $query = $query->where('branch_transfers.transfer_status', '=', $statusId);
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
                ->orderBy('branch_transfers.transfer_date')
                ->orderBy('users.position_ext')
                ->orderBy('users.name')
                ->orderBy('branch_transfers.id')
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
            $statusName = Arr::get(PROCESS_STATUS_LIST, $data->statusId);
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
                if ($statusId == PROCESS_STATUS_PENDING) {
                    $titleStatusStyle = $titleWarnStyle;
                } elseif ($statusId == PROCESS_STATUS_REJECTED) {
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
                    if ($row->transfer_status == PROCESS_STATUS_REJECTED) {
                        $style = $rowRedStyle;
                    } elseif ($row->transfer_status == PROCESS_STATUS_PENDING) {
                        $style = $rowWarnStyle;
                    }
                }

                $values = [
                    new Cell($row->transfer_date, $style),
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
                    $values[] = new Cell(Arr::get(PROCESS_STATUS_LIST, $row->transfer_status), $style);
                }

                $cells = array_merge($cells, $values);
                $writer->addRow(new Row($cells, null));

                $rowBranchId = intval($row->branch_id);
            }
        })->toBrowser();
    }
}
