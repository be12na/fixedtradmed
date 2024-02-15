<?php

namespace App\Http\Controllers\Main\Reports;

use App\Reports\Excel\GlobalManager as ExcelGlobalManager;
use App\Models\Branch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

trait GlobalManager
{
    public function indexReportGlobalManager(Request $request)
    {
        $branches = Branch::orderBy('name')->byActive()->get();
        $dateRange = $this->dateFilter();
        $currentBranchId = session('filter.branchId', -1);
        $currentManagerId = session('filter.managerId', -1);
        $currentManager = ($currentManagerId > 0) ? User::byId($currentManagerId)->first() : null;

        return view('main.reports.global.manager-index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentManager' => $currentManager,
            'windowTitle' => 'Laporan Global Manager',
            'breadcrumbs' => ['Laporan', 'Global', 'Manager']
        ]);
    }

    private function baseQueryGlobalManager(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));
        $managerId = intval($request->get('manager_id', -1));
        $boxId = PRODUCT_UNIT_BOX;
        $pcsId = PRODUCT_UNIT_PCS;
        $approved = PROCESS_STATUS_APPROVED;

        $querySale = DB::table('branch_transfers')
            ->join('branch_transfer_details', 'branch_transfer_details.transfer_id', '=', 'branch_transfers.id')
            ->join('branch_sales', function ($join) use ($formatStart, $formatEnd) {
                return $join->on('branch_sales.id', '=', 'branch_transfer_details.sale_id')
                    ->whereRaw("(branch_sales.sale_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
                    ->whereRaw('(branch_sales.is_active = 1)')
                    ->whereRaw('(branch_sales.deleted_at IS NULL)');
            })
            ->join('branch_sales_products', function ($join) {
                return $join->on('branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
                    ->whereRaw('(branch_sales_products.is_active = 1)')
                    ->whereRaw('(branch_sales_products.deleted_at IS NULL)');
            })
            ->selectRaw("
            branch_sales.manager_id, branch_sales.branch_id, branch_sales_products.product_id,
            SUM(CASE WHEN branch_sales_products.product_unit = {$boxId} THEN branch_sales_products.product_qty 
            ELSE 0 END) as qty_box,
            SUM(CASE WHEN branch_sales_products.product_unit = {$pcsId} THEN branch_sales_products.product_qty 
            ELSE 0 END) as qty_pcs,
            SUM(branch_sales_products.total_price) as total_omzet
            ")
            ->whereRaw("(branch_transfers.transfer_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(branch_transfers.transfer_status = {$approved})")
            ->whereRaw('(branch_transfers.deleted_at IS NULL)')
            ->groupByRaw("branch_sales.manager_id, branch_sales.branch_id, branch_sales_products.product_id");

        $queryMitra = DB::table('mitra_purchases')
            ->join('mitra_purchase_products', 'mitra_purchase_products.mitra_purchase_id', '=', 'mitra_purchases.id')
            ->join(DB::raw('users as mitra'), 'mitra.id', '=', 'mitra_purchases.mitra_id')
            ->selectRaw("
            mitra.referral_id as manager_id, mitra.branch_id, mitra_purchase_products.product_id,
            SUM(CASE WHEN mitra_purchase_products.product_unit = {$boxId} THEN mitra_purchase_products.product_qty 
            ELSE 0 END) as qty_box,
            SUM(CASE WHEN mitra_purchase_products.product_unit = {$pcsId} THEN mitra_purchase_products.product_qty 
            ELSE 0 END) as qty_pcs,
            SUM(mitra_purchase_products.total_price) as total_omzet
            ")
            ->whereRaw("(mitra_purchases.purchase_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(mitra_purchases.purchase_status = {$approved})")
            ->whereRaw('(mitra_purchases.is_active = 1)')
            ->whereRaw('(mitra_purchases.deleted_at IS NULL)')
            ->whereRaw('(mitra_purchase_products.is_active = 1)')
            ->whereRaw('(mitra_purchase_products.deleted_at IS NULL)')
            ->groupByRaw("mitra.referral_id, mitra.branch_id, mitra_purchase_products.product_id");

        if ($branchId > 0) {
            $querySale = $querySale->whereRaw("(branch_sales.branch_id = {$branchId})");
            $queryMitra = $queryMitra->whereRaw("(mitra.branch_id = {$branchId})");
        }

        if ($managerId > 0) {
            $querySale = $querySale->whereRaw("(branch_sales.manager_id = {$managerId})");
            $queryMitra = $queryMitra->whereRaw("(mitra.referral_id = {$managerId})");
        }

        $querySale = $querySale->toSql();
        $queryMitra = $queryMitra->toSql();

        $query = DB::table(DB::raw("({$querySale} UNION ALL {$queryMitra}) as rekap"))
            ->join('users', 'users.id', '=', 'rekap.manager_id')
            ->join('branches', 'branches.id', '=', 'rekap.branch_id')
            ->join('products', 'products.id', '=', 'rekap.product_id')
            ->selectRaw("
            rekap.manager_id,
            users.name as manager_name, users.username,
            rekap.branch_id, branches.name as branch_name,
            rekap.product_id, products.name as product_name,
            rekap.qty_box, rekap.qty_pcs, rekap.total_omzet
            ");

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'managerId' => $managerId,
            'query' => $query
        ];
    }

    public function dataTableReportGlobalManager(Request $request)
    {
        $base = $this->baseQueryGlobalManager($request);

        session([
            'filter.dates' => ['start' => $base->startDate, 'end' => $base->endDate],
            'filter.branchId' => $base->branchId,
            'filter.managerId' => $base->managerId,
        ]);

        $baseQuery = $base->query;
        $query = DB::table(DB::raw('(' . $baseQuery->toSql() . ') as sum_manager'))
            ->selectRaw("
            manager_id, manager_name, username, branch_name, product_name,
            SUM(qty_box) as qty_box, SUM(qty_pcs) as qty_pcs, SUM(total_omzet) as total_omzet
            ")
            ->groupByRaw("manager_id, manager_name, username, branch_name, product_name");
        // GROUP_CONCAT(
        //     CONCAT(
        //         branch_id, 
        //         ',', branch_name, 
        //         ',', product_id, 
        //         ',', product_name, 
        //         ',', qty_box, 
        //         ',', qty_pcs, 
        //         ',', total_omzet
        //     )
        //     SEPARATOR '|'
        // ) as details

        return datatables()->query($query)
            ->editColumn('manager_name', function ($row) {
                $html = '<span class="fw-bold">%s</span><span class="ms-1 fw-normal fst-italic">(%s)</span>';
                return new HtmlString(sprintf($html, $row->manager_name, $row->username));
            })
            ->editColumn('qty_box', function ($row) {
                return formatNumber($row->qty_box);
            })
            ->editColumn('qty_pcs', function ($row) {
                return formatNumber($row->qty_pcs);
            })
            ->editColumn('total_omzet', function ($row) {
                return new HtmlString(formatCurrency($row->total_omzet, 0, true, false));
            })
            // ->editColumn('details', function ($row) {
            //     $details = explode('|', $row->details);
            //     $result = collect();

            //     foreach ($details as $rowDetail) {
            //         $detail = explode(',', $rowDetail);
            //         $branchId = $detail[0];
            //         $productId = $detail[2];
            //         if (empty($branch = $result
            //             ->where('branch_id', '=', $branchId)
            //             ->where('product_id', '=', $productId)
            //             ->first())) {

            //             $branch = (object) [
            //                 'branch_id' => intval($branchId),
            //                 'branch_name' => $detail[1],
            //                 'product_id' => intval($productId),
            //                 'product_name' => $detail[3],
            //                 'qty_box' => 0,
            //                 'html_qty_box' => '0',
            //                 'qty_pcs' => 0,
            //                 'html_qty_pcs' => '0',
            //                 'total_omzet' => 0,
            //                 'html_total_omzet' => '0',
            //             ];

            //             $result->push($branch);
            //         }

            //         $branch->qty_box += intval($detail[4]);
            //         $branch->qty_pcs += intval($detail[5]);
            //         $branch->total_omzet += intval($detail[6]);
            //         $branch->html_qty_box = formatNumber($branch->qty_box, 0);
            //         $branch->html_qty_pcs = formatNumber($branch->qty_pcs, 0);
            //         $branch->html_total_omzet = formatCurrency($branch->total_omzet, 0, true, false);
            //     }

            //     $result = $result->sortBy([['branch_name', 'asc'], ['product_name' => 'asc']]);

            //     return new HtmlString($result->toJson());
            // })
            ->escapeColumns()
            ->toJson();
    }

    public function downloadReportGlobalManager(Request $request)
    {
        $base = $this->baseQueryGlobalManager($request);
        // $rows = $base->query
        //     ->orderBy('users.name')
        //     ->orderBy('rekap.manager_id')
        //     ->orderBy('branches.name')
        //     ->orderBy('rekap.branch_id')
        //     ->orderBy('products.name')
        //     ->orderBy('rekap.product_id')
        //     ->get();

        $rows = DB::table(DB::raw('(' . $base->query->toSql() . ') as sum_manager'))
            ->selectRaw("
            manager_id, manager_name, username, branch_id, branch_name, product_id, product_name,
            SUM(qty_box) as qty_box, SUM(qty_pcs) as qty_pcs, SUM(total_omzet) as total_omzet
            ")
            ->groupByRaw("manager_id, manager_name, username, branch_id, branch_name, product_id, product_name")
            ->orderBy('manager_name')
            ->orderBy('manager_id')
            ->orderBy('branch_name')
            ->orderBy('branch_id')
            ->orderBy('product_name')
            ->orderBy('product_id')
            ->get();

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $tgl = $base->startDate->format('Ymd');

        if ($tgl != $base->endDate->format('Ymd')) {
            $tgl .= '-' . $base->endDate->format('Ymd');
        }

        $branchName = '';
        $managerName = '';
        $branch = null;
        $manager = null;

        if ($base->branchId > 0) {
            $branch = Branch::byId($base->branchId)->first();
            if (!empty($branch)) $branchName = '-' . str_replace(['-', ' '], ['', ''], $branch->name);
        }

        if ($base->managerId > 0) {
            $manager = User::byId($base->managerId)->first();
            if (!empty($manager)) $managerName = '-' . str_replace(['-', ' '], ['', ''], $manager->name);
        }

        $fileFormat = strtolower($request->exportFormat);
        if ($fileFormat != 'pdf') {
            $fileFormat = 'xlsx';
        }

        $filename = "GlobalMGR{$branchName}{$managerName}-{$tgl}.{$fileFormat}";
        $titleHeader = 'Laporan Penjualan Total Per-Manager';

        if ($fileFormat == 'pdf') {
            return Pdf::loadView('main.reports.global.pdf.manager', [
                'filename' => $filename,
                'rows' => $rows,
                'startDate' => $base->startDate,
                'endDate' => $base->endDate,
                'branch' => $branch,
                'manager' => $manager,
                'titleHeader' => $titleHeader,
            ])->stream($filename);
        }

        return (new ExcelGlobalManager(
            $rows,
            $base->startDate,
            $base->endDate,
            $branch,
            $manager,
            $titleHeader
        ))->download($filename);
    }
}
