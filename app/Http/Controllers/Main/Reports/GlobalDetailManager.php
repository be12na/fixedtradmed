<?php

namespace App\Http\Controllers\Main\Reports;

use App\Reports\Excel\GlobalDetailManager as ExcelGlobalDetailManager;
use App\Models\Branch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

trait GlobalDetailManager
{
    public function indexReportGlobalDetailManager(Request $request)
    {
        $branches = Branch::orderBy('name')->byActive()->get();
        $dateRange = $this->dateFilter();
        $currentBranchId = session('filter.branchId', -1);
        $currentManagerId = session('filter.managerId', -1);
        $currentManager = ($currentManagerId > 0) ? User::byId($currentManagerId)->first() : null;

        return view('main.reports.global.manager-detail-index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentManager' => $currentManager,
            'windowTitle' => 'Laporan Global Detail Manager',
            'breadcrumbs' => ['Laporan', 'Global', 'Detail', 'Manager']
        ]);
    }

    private function baseQueryGlobalDetailManager(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));
        $managerId = intval($request->get('team_id', -1));
        $boxId = PRODUCT_UNIT_BOX;
        $pcsId = PRODUCT_UNIT_PCS;

        $appStructure = app('appStructure');
        $selectMemberPosition = $appStructure->getSqlPositions(true);

        $querySale = DB::table('branch_sales')
            ->join('branch_sales_products', 'branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
            ->join(DB::raw('users as salesman'), 'salesman.id', '=', 'branch_sales.salesman_id')
            ->join(DB::raw('users as upline'), 'upline.id', '=', 'salesman.upline_id')
            ->join(DB::raw("({$selectMemberPosition}) as member_positions"), 'member_positions.id', '=', 'salesman.position_int')
            ->join(DB::raw("({$selectMemberPosition}) as upline_positions"), 'upline_positions.id', '=', 'upline.position_int')
            ->selectRaw("
            branch_sales.salesman_id as team_id, salesman.name as team_name, 
            salesman.username, salesman.position_int as position_id, member_positions.name as position_name, 
            salesman.upline_id, upline.name as upline_name, upline.position_int as upline_position_id, upline_positions.name as upline_position_name,
            branch_sales.manager_id,
            branch_sales.branch_id, branch_sales_products.product_id, 'Team' as team_scope,
            SUM(CASE WHEN branch_sales_products.product_unit = {$boxId} THEN branch_sales_products.product_qty 
            ELSE 0 END) as qty_box,
            SUM(CASE WHEN branch_sales_products.product_unit = {$pcsId} THEN branch_sales_products.product_qty 
            ELSE 0 END) as qty_pcs,
            SUM(branch_sales_products.total_price) as total_omzet,
            1 as is_crew
            ")
            ->whereRaw("(branch_sales.sale_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw('(branch_sales.is_active = 1)')
            ->whereRaw('(branch_sales.deleted_at IS NULL)')
            ->whereRaw('(branch_sales_products.is_active = 1)')
            ->whereRaw('(branch_sales_products.deleted_at IS NULL)')
            ->groupByRaw("branch_sales.salesman_id, salesman.name, salesman.username, salesman.position_int, member_positions.name, salesman.upline_id, upline.name, branch_sales.manager_id, branch_sales.branch_id, branch_sales_products.product_id, upline.position_int, upline_positions.name");

        $queryMitra = DB::table('mitra_purchases')
            ->join('mitra_purchase_products', 'mitra_purchase_products.mitra_purchase_id', '=', 'mitra_purchases.id')
            ->join(DB::raw('users as mitra'), 'mitra.id', '=', 'mitra_purchases.mitra_id')
            ->join(DB::raw('users as referral'), 'referral.id', '=', 'mitra.referral_id')
            ->join(DB::raw("({$selectMemberPosition}) as referral_positions"), 'referral_positions.id', '=', 'referral.position_int')
            ->selectRaw("
            mitra_purchases.mitra_id as team_id, mitra.name as team_name, 
            mitra.username, mitra.position_ext as position_id, 'Member' as position_name, 
            mitra.referral_id as upline_id, referral.name as upline_name, referral.position_int as upline_position_id, referral_positions.name as upline_position_name, 
            mitra.referral_id as manager_id,
            mitra.branch_id, mitra_purchase_products.product_id,  'Team' as team_scope,
            SUM(CASE WHEN mitra_purchase_products.product_unit = {$boxId} THEN mitra_purchase_products.product_qty 
            ELSE 0 END) as qty_box,
            SUM(CASE WHEN mitra_purchase_products.product_unit = {$pcsId} THEN mitra_purchase_products.product_qty 
            ELSE 0 END) as qty_pcs,
            SUM(mitra_purchase_products.total_price) as total_omzet,
            0 as is_crew
            ")
            ->whereRaw("(mitra_purchases.purchase_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw('(mitra_purchases.is_active = 1)')
            ->whereRaw('(mitra_purchases.deleted_at IS NULL)')
            ->whereRaw('(mitra_purchase_products.is_active = 1)')
            ->whereRaw('(mitra_purchase_products.deleted_at IS NULL)')
            ->groupByRaw("mitra_purchases.mitra_id, mitra.name, mitra.username, mitra.position_ext, mitra.referral_id, referral.name, mitra.branch_id, mitra_purchase_products.product_id, referral.position_int, referral_positions.name");

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
            ->join('branches', 'branches.id', '=', 'rekap.branch_id')
            ->join('products', 'products.id', '=', 'rekap.product_id')
            ->selectRaw("
            rekap.team_id,
            rekap.team_name, rekap.username, 
            rekap.position_id, rekap.position_name,
            rekap.upline_id, rekap.upline_name,
            rekap.upline_position_id, rekap.upline_position_name,
            rekap.branch_id, branches.name as branch_name,
            rekap.product_id, products.name as product_name,
            rekap.qty_box, rekap.qty_pcs, rekap.total_omzet,
            rekap.is_crew
            ");

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'managerId' => $managerId,
            'query' => $query
        ];
    }

    public function dataTableReportGlobalDetailManager(Request $request)
    {
        $base = $this->baseQueryGlobalDetailManager($request);

        session([
            'filter.dates' => ['start' => $base->startDate, 'end' => $base->endDate],
            'filter.branchId' => $base->branchId,
            'filter.managerId' => $base->managerId,
        ]);

        $baseQuery = $base->query;
        $query = DB::table(DB::raw('(' . $baseQuery->toSql() . ') as sum_manager'))->mergeBindings($baseQuery)
            ->selectRaw("
            team_id, team_name, username, position_id, position_name, upline_id, upline_name, upline_position_id, upline_position_name, 
            is_crew, null as branch_name, null as product_name,
            SUM(qty_box) as qty_box, SUM(qty_pcs) as qty_pcs, SUM(total_omzet) as total_omzet,
            GROUP_CONCAT(
                CONCAT(
                    branch_id, 
                    ',', branch_name, 
                    ',', product_id, 
                    ',', product_name, 
                    ',', qty_box, 
                    ',', qty_pcs, 
                    ',', total_omzet
                )
                SEPARATOR '|'
            ) as details
            ")
            ->groupByRaw("team_id, team_name, username, position_name, upline_name, is_crew, upline_position_id, upline_position_name");

        return datatables()->query($query)
            ->editColumn('team_name', function ($row) {
                $html = '<div><span>%s</span><span class="ms-1 fw-normal fst-italic">(%s)</span></div>';
                return new HtmlString(sprintf($html, $row->team_name, $row->username));
            })
            ->editColumn('position_id', function ($row) {
                return $row->position_name;
            })
            ->editColumn('upline_name', function ($row) {
                $result = '<div>' . $row->upline_name . '</div>';
                $result .= '<div class="text-primary fst-italic text-decoration-underline">' . $row->upline_position_name . '</div>';

                return new HtmlString($result);
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
            ->editColumn('details', function ($row) {
                $details = explode('|', $row->details);
                $result = collect();

                foreach ($details as $rowDetail) {
                    $detail = explode(',', $rowDetail);
                    $branchId = $detail[0];
                    $productId = $detail[2];
                    if (empty($branch = $result
                        ->where('branch_id', '=', $branchId)
                        ->where('product_id', '=', $productId)
                        ->first())) {

                        $branch = (object) [
                            'branch_id' => intval($branchId),
                            'branch_name' => $detail[1],
                            'product_id' => intval($productId),
                            'product_name' => $detail[3],
                            'qty_box' => 0,
                            'html_qty_box' => '0',
                            'qty_pcs' => 0,
                            'html_qty_pcs' => '0',
                            'total_omzet' => 0,
                            'html_total_omzet' => '0',
                        ];

                        $result->push($branch);
                    }

                    $branch->qty_box += intval($detail[4]);
                    $branch->qty_pcs += intval($detail[5]);
                    $branch->total_omzet += intval($detail[6]);
                    $branch->html_qty_box = formatNumber($branch->qty_box, 0);
                    $branch->html_qty_pcs = formatNumber($branch->qty_pcs, 0);
                    $branch->html_total_omzet = formatCurrency($branch->total_omzet, 0, true, false);
                }

                $result = $result->sortBy([['branch_name', 'asc'], ['product_name' => 'asc']]);

                return new HtmlString($result->toJson());
            })
            ->escapeColumns()
            ->toJson();
    }

    public function downloadReportGlobalDetailManager(Request $request)
    {
        $base = $this->baseQueryGlobalDetailManager($request);
        $rows = $base->query
            ->orderBy('rekap.team_name')
            ->orderBy('rekap.team_id')
            ->orderBy('rekap.position_id')
            ->orderBy('branches.name')
            ->orderBy('rekap.branch_id')
            ->orderBy('products.name')
            ->orderBy('rekap.product_id')
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

        $filename = "GlobalDetailsMGR{$branchName}{$managerName}-{$tgl}.{$fileFormat}";

        if ($fileFormat == 'pdf') {
            return Pdf::loadView('main.reports.global.pdf.manager-detail', [
                'filename' => $filename,
                'rows' => $rows,
                'startDate' => $base->startDate,
                'endDate' => $base->endDate,
                'branch' => $branch,
                'manager' => $manager,
                'titleHeader' => 'Laporan Rincian Penjualan',
            ])->stream($filename);
        }

        return (new ExcelGlobalDetailManager(
            $rows,
            $base->startDate,
            $base->endDate,
            $branch,
            $manager,
            'Laporan Rincian Penjualan'
        ))->download($filename);
    }
}
