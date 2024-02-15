<?php

namespace App\Http\Controllers\Main\Reports;

use App\Reports\Excel\BonusDistributor as ExcelBonusDistributor;
use App\Models\Branch;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

trait BonusDistributor
{
    public function indexReportBonusDistributor(Request $request)
    {
        $branches = Branch::orderBy('name')->byActive()->get();
        $dateRange = $this->dateFilter();
        $currentBranchId = session('filter.branchId', -1);
        $currentDistributorId = session('filter.managerId', -1);
        $currentDistributor = ($currentDistributorId > 0) ? User::byId($currentDistributorId)->first() : null;

        return view('main.reports.global.bonus-distributor', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentManager' => $currentDistributor,
            'windowTitle' => 'Laporan Bonus Distributor',
            'breadcrumbs' => ['Laporan', 'Bonus', 'Distributor']
        ]);
    }

    private function baseQueryBonusDistributor(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));
        $distributorId = intval($request->get('manager_id', -1));
        $bonusType = BONUS_TYPE_MGR_DISTRIBUTOR;

        $query = DB::table('bonus_members')
            ->join('mitra_purchases', 'mitra_purchases.id', '=', 'bonus_members.transaction_id')
            ->join('branches', 'branches.id', '=', 'mitra_purchases.branch_id')
            ->join('users', 'users.id', '=', 'bonus_members.user_id')
            ->selectRaw("
            bonus_members.user_id as manager_id, users.name as manager_name, users.username,
            mitra_purchases.branch_id, branches.name as branch_name,
            mitra_purchases.total_transfer as omzet, bonus_members.bonus_amount as bonus
            ")
            ->whereRaw("(bonus_members.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(bonus_members.bonus_type = {$bonusType})");

        if ($branchId > 0) {
            $query = $query->whereRaw("(mitra_purchases.branch_id = {$branchId})");
        }

        if ($distributorId > 0) {
            $query = $query->whereRaw("(mitra_purchases.manager_id = {$distributorId})");
        }

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'managerId' => $distributorId,
            'query' => $query
        ];
    }

    public function dataTableReportBonusDistributor(Request $request)
    {
        $base = $this->baseQueryBonusDistributor($request);

        session([
            'filter.dates' => ['start' => $base->startDate, 'end' => $base->endDate],
            'filter.branchId' => $base->branchId,
            'filter.managerId' => $base->managerId,
        ]);

        $baseQuery = $base->query;
        $query = DB::table(DB::raw('(' . $baseQuery->toSql() . ') as sum_manager'))
            ->selectRaw("
            manager_id, manager_name, username, branch_name, SUM(omzet) as total_omzet, SUM(bonus) as total_bonus
            ")
            ->groupByRaw("manager_id, manager_name, username, branch_name");

        return datatables()->query($query)
            ->editColumn('manager_name', function ($row) {
                $html = '<span class="fw-bold">%s</span><span class="ms-1 fw-normal fst-italic">(%s)</span>';
                return new HtmlString(sprintf($html, $row->manager_name, $row->username));
            })
            ->editColumn('total_omzet', function ($row) {
                return new HtmlString(formatCurrency($row->total_omzet, 0, true, false));
            })
            ->editColumn('total_bonus', function ($row) {
                return new HtmlString(formatCurrency($row->total_bonus, 0, true, false));
            })
            ->escapeColumns()
            ->toJson();
    }

    public function downloadReportBonusDistributor(Request $request)
    {
        $base = $this->baseQueryBonusDistributor($request);

        $rows = DB::table(DB::raw('(' . $base->query->toSql() . ') as sum_manager'))
            ->selectRaw("
            manager_id, manager_name, username, branch_name, SUM(omzet) as total_omzet, SUM(bonus) as total_bonus
            ")
            ->groupByRaw("manager_id, manager_name, username, branch_name")
            ->orderBy('manager_name')
            ->orderBy('manager_id')
            ->orderBy('branch_name')
            ->orderBy('branch_id')
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

        $filename = "BonusDistributor{$branchName}{$managerName}-{$tgl}.{$fileFormat}";
        $titleHeader = 'Laporan Total Bonus Distributor';

        if ($fileFormat == 'pdf') {
            return Pdf::loadView('main.reports.bonus.distributor', [
                'filename' => $filename,
                'rows' => $rows,
                'startDate' => $base->startDate,
                'endDate' => $base->endDate,
                'branch' => $branch,
                'manager' => $manager,
                'titleHeader' => $titleHeader,
            ])->stream($filename);
        }

        return (new ExcelBonusDistributor(
            $rows,
            $base->startDate,
            $base->endDate,
            $branch,
            $manager,
            $titleHeader
        ))->download($filename);
    }
}
