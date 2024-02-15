<?php

namespace App\Http\Controllers\Main\Reports;

use App\Models\Branch;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\XLSX\Writer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Spatie\SimpleExcel\SimpleExcelWriter;

trait GlobalProduct
{
    public function indexReportGlobalProduct(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $dateRange = $this->dateFilter();
        $currentBranchId = session('filter.branchId', -1);

        return view('main.reports.global.product-index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'windowTitle' => 'Laporan Global Produk',
            'breadcrumbs' => ['Laporan', 'Global', 'Produk']
        ]);
    }

    public function dataTableReportGlobalProduct(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));

        $branches = Branch::with([
            'sales' => function ($branchSale) use ($startDate, $endDate) {
                return $branchSale->byBetweenDate($startDate, $endDate)
                    ->orderBy('sale_date')
                    ->with(['products' => function ($branchSalesProduct) {
                        return $branchSalesProduct->byActive();
                    }]);
            },
            'branchTransfers' => function ($branchTransfer) use ($startDate, $endDate) {
                return $branchTransfer->byBetweenDate($startDate, $endDate)
                    ->whereIn('transfer_status', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]);
            }
        ]);

        if ($branchId > 0) $branches = $branches->byId($branchId);

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.branchId' => $branchId,
        ]);

        $mainBanks = $this->neo->mainBanks(false)->sortBy('bank_code');
        $bankCodes = array_unique($mainBanks->pluck('bank_code')->toArray());

        return datatables()->eloquent($branches)
            ->editColumn('name', function ($row) use ($bankCodes) {
                return new HtmlString(view('main.reports.global.product-table', [
                    'branch' => $row,
                    'bankCodes' => $bankCodes,
                ])->render());
            })
            ->escapeColumns()
            ->toJson();
    }

    private function queryDownloadGlobalProduct(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));

        $branches = Branch::with([
            'sales' => function ($branchSale) use ($startDate, $endDate) {
                return $branchSale->byBetweenDate($startDate, $endDate)
                    ->orderBy('sale_date')
                    ->with(['products' => function ($branchSalesProduct) {
                        return $branchSalesProduct->byActive();
                    }]);
            },
            'branchTransfers' => function ($branchTransfer) use ($startDate, $endDate) {
                return $branchTransfer->byBetweenDate($startDate, $endDate)
                    ->whereIn('transfer_status', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]);
            }
        ]);

        if ($branchId > 0) $branches = $branches->byId($branchId);

        $mainBanks = $this->neo->mainBanks(false)->sortBy('bank_code');
        $bankCodes = array_unique($mainBanks->pluck('bank_code')->toArray());

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'bankCodes' => $bankCodes,
            'rows' => $branches
                ->orderBy('name')
                ->get(),
        ];
    }

    public function excelReportGlobalProduct(Request $request)
    {
        $data = $this->queryDownloadGlobalProduct($request);
        $branches = $data->rows;

        if ($branches->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $branchId = $data->branchId;
        $bankCodes = $data->bankCodes;
        $startDate = $data->startDate;
        $endDate = $data->endDate;
        $startFormatted = $startDate->format('Ymd');
        $endFormatted = $endDate->format('Ymd');
        $tglName = $startFormatted;
        $tglReport = formatFullDate($data->startDate);
        $startDateLoop = clone $startDate;
        $endDateLoop = clone $endDate;

        if ($startFormatted != $endFormatted) {
            if ($startFormatted > $endFormatted) {
                $tglName = "{$endFormatted}-{$startFormatted}";
                $tglReport = formatFullDate($data->endDate) . ' s/d ' . $tglReport;
                $startDateLoop = clone $endDate;
                $endDateLoop = clone $startDate;
            } else {
                $tglName = "{$startFormatted}-{$endFormatted}";
                $tglReport = $tglReport . ' s/d ' . formatFullDate($data->endDate);
            }
        }

        $downloadName = "Global-Product-{$tglName}";

        if ($branchId > 0) {
            $branch = $branches->first();
            $downloadName .= '-' . strtolower(str_replace(['-', ' ', '.', ','], ['_', '_', '_', '_'], $branch->name));
        }

        $downloadName = $downloadName . ".xlsx";
        $dateColumns = [];

        while ($startDateLoop->format('Ymd') <= $endDateLoop->format('Ymd')) {
            $dateColumns[] = clone $startDateLoop;
            $startDateLoop = $startDateLoop->addDay();
        }

        SimpleExcelWriter::streamDownload(
            $downloadName,
            'xlsx',
            function (Writer $writer) use ($downloadName, $tglReport, $branches, $bankCodes, $dateColumns) {
                $writer->openToBrowser($downloadName);

                $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->build();
                $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->setCellAlignment('center')->build();
                $rowStyle = (new StyleBuilder)->setFontSize(10)->build();
                $summaryStyle = (new StyleBuilder)->setFontSize(10)->setFontBold()->build();

                $writer->addRows([
                    new Row([new Cell('Management Report PT. Neo Erajaya Optima')], $titleStyle),
                    new Row([new Cell('LAPORAN GLOBAL PRODUK DAN PEMASUKAN HARIAN')], $titleStyle),
                    new Row([new Cell('PERIODE ' . $tglReport)], $titleStyle),
                    new Row([new Cell('')], null)
                ]);

                $headerColumns = [
                    new Cell('No'),
                    new Cell('Cabang'),
                ];

                $headerColumns2 = [
                    new Cell(''),
                    new Cell(''),
                ];

                $headerColumns3 = [
                    new Cell(''),
                    new Cell(''),
                ];

                $headerColumns4 = [
                    new Cell(''),
                    new Cell(''),
                ];

                $summaryColumns = [
                    new Cell('Total Pemasukan'),
                    new Cell(''),
                ];

                $dateParams = [];
                $dateColumnIndex = 0;
                foreach ($dateColumns as $dateColumn) {
                    $headerColumns[] = new Cell(($dateColumnIndex > 0) ? '' : 'Tanggal Penjualan');
                    $headerColumns[] = new Cell('');

                    $headerColumns2[] = new Cell($dateColumn->dayName);
                    $headerColumns2[] = new Cell('');

                    $summaryColumns[] = new Cell('');
                    $summaryColumns[] = new Cell('');

                    $param = formatFullDate($dateColumn);
                    $headerColumns3[] = new Cell($param);
                    $headerColumns3[] = new Cell('');

                    $headerColumns4[] = new Cell('Box');
                    $headerColumns4[] = new Cell('Pcs');

                    $dateParams[] = $param;
                    $dateColumnIndex++;
                }

                $headerColumns[] = new Cell('');
                $headerColumns[] = new Cell('');

                $headerColumns2[] = new Cell('Total Box');
                $headerColumns2[] = new Cell('Total Pcs');

                $headerColumns3[] = new Cell('');
                $headerColumns3[] = new Cell('');

                $summaryColumns[] = new Cell('');
                $summaryColumns[] = new Cell('');

                $bankIndex = 0;
                $totalGlobalTransfers = [];

                foreach ($bankCodes as $bankCode) {
                    $headerColumns[] = new Cell(($bankIndex > 0) ? '' : 'Transfer');
                    $headerColumns2[] = new Cell($bankCode);
                    $headerColumns3[] = new Cell('');

                    $totalGlobalTransfers[$bankCode] = 0;
                    $summaryColumns[] = ['totalGlobalTransfers', $bankCode];
                    $bankIndex++;
                }

                $totalGlobalTransfer = 0;
                $totalGlobalSavings = 0;

                $headerColumns[] = new Cell('');
                $headerColumns2[] = new Cell(BANK_000);
                $headerColumns3[] = new Cell('');
                $totalGlobalTransfers[BANK_000] = 0;
                $summaryColumns[] = ['totalGlobalTransfers', BANK_000];

                $headerColumns[] = new Cell('Savings');
                $headerColumns2[] = new Cell('');
                $headerColumns3[] = new Cell('');
                $summaryColumns[] = 'totalGlobalSavings';

                $headerColumns[] = new Cell('Total');
                $headerColumns2[] = new Cell('');
                $headerColumns3[] = new Cell('');
                $summaryColumns[] = 'totalGlobalTransfer';

                $writer->addRows([
                    new Row($headerColumns, $headerStyle),
                    new Row($headerColumns2, $headerStyle),
                    new Row($headerColumns3, $headerStyle),
                    new Row($headerColumns4, $headerStyle),
                ]);

                $rowNumber = 0;

                foreach ($branches as $branch) {
                    $rowNumber += 1;
                    $cols = [
                        new Cell($rowNumber),
                        new Cell($branch->name),
                    ];

                    $branchSale = $branch->sales;
                    $totalBox = 0;
                    $totalPcs = 0;

                    foreach ($dateParams as $dtParam) {
                        $cols[] = new Cell($box = $branchSale->where('sale_date', '=', $dtParam)->sum('sum_quantity_box'));
                        $totalBox += $box;

                        $cols[] = new Cell($pcs = $branchSale->where('sale_date', '=', $dtParam)->sum('sum_quantity_pcs'));
                        $totalPcs += $pcs;
                    }

                    $cols[] = new Cell($totalBox);
                    $cols[] = new Cell($totalPcs);
                    $totalReceived = 0;

                    foreach ($bankCodes as $bank) {
                        $cols[] = new Cell($received = $branch->branchTransfers->where('bank_code', '=', $bank)->sum('total_transfer'));

                        $totalGlobalTransfers[$bank] += $received;
                        $totalGlobalTransfer += $received;
                        $totalReceived += $received;
                    }

                    $cols[] = new Cell($received = $branch->branchTransfers->where('bank_code', '=', BANK_000)->sum('total_transfer'));
                    $totalGlobalTransfers[BANK_000] += $received;
                    $totalGlobalTransfer += $received;
                    $totalReceived += $received;
                    $cols[] = new Cell($savings = $branchSale->sum('savings'));

                    $totalGlobalSavings += $savings;
                    $totalReceived += $savings;
                    $cols[] = new Cell($totalReceived);

                    $writer->addRow(new Row($cols, $rowStyle));
                }

                $totalGlobalTransfer = $totalGlobalTransfer + $totalGlobalSavings;

                for ($i = 0; $i < count($summaryColumns); $i++) {
                    $column = $summaryColumns[$i];

                    if (!($column instanceof Cell)) {
                        if (is_array($column)) {
                            $var = $column[0];
                            $key = $column[1];
                            $column = new Cell($$var[$key]);
                        } else {
                            $var = $column;
                            $column = new Cell($$var);
                        }

                        $summaryColumns[$i] = $column;
                    }
                }

                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row($summaryColumns, $summaryStyle));
            }
        )->toBrowser();
    }
}
