<?php

namespace App\Http\Controllers\Main;

use App\Helpers\AppStructure;
use App\Http\Controllers\Controller;
use App\Reports\Excel\SummaryBonus;
use App\Reports\Excel\SummaryDetailBonus;
use Barryvdh\DomPDF\Facade\Pdf;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\WriterInterface;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Spatie\SimpleExcel\SimpleExcelWriter;
use stdClass;

class BonusController extends Controller
{
    private AppStructure $appStructure;

    private $summaryPageNames = [
        'royalty', 'override', 'team', 'sale', 'total'
    ];

    public function __construct()
    {
        $this->appStructure = app('appStructure');
    }

    public function index(Request $request)
    {
        $dateRange = $this->dateFilter();
        $routeName = $request->route()->getName();
        $filterIntExt = false;
        // $bonusType = BONUS_TYPE_ROYALTY;
        // $bonusTitle = 'Royalty';
        // $totalUrl = route('main.bonus.royalty.total');
        // $dataUrl = route('main.bonus.royalty.datatable');
        // $downloadExcel = route('main.bonus.royalty.download.excel');

        // if ($routeName == 'main.bonus.override.index') {
        //     $bonusType = BONUS_TYPE_OVERRIDE;
        //     $bonusTitle = 'Override';
        //     $totalUrl = route('main.bonus.override.total');
        //     $dataUrl = route('main.bonus.override.datatable');
        //     $downloadExcel = route('main.bonus.override.download.excel');
        // } elseif ($routeName == 'main.bonus.team.index') {
        //     $bonusType = BONUS_TYPE_TEAM;
        //     $bonusTitle = 'Team';
        //     $totalUrl = route('main.bonus.team.total');
        //     $dataUrl = route('main.bonus.team.datatable');
        //     $downloadExcel = route('main.bonus.team.download.excel');
        // } elseif ($routeName == 'main.bonus.sale.index') {
        //     $bonusType = BONUS_TYPE_SALE;
        //     $bonusTitle = 'Penjualan';
        //     $totalUrl = route('main.bonus.sale.total');
        //     $dataUrl = route('main.bonus.sale.datatable');
        //     $downloadExcel = route('main.bonus.sale.download.excel');
        // } else {
        //     $filterIntExt = true;
        // }

        $bonusType = BONUS_TYPE_SALE;
        $bonusTitle = 'Penjualan';
        $totalUrl = route('main.bonus.sale.total');
        $dataUrl = route('main.bonus.sale.datatable');
        $downloadExcel = route('main.bonus.sale.download.excel');

        if ($routeName == 'main.bonus.team.index') {
            $bonusType = BONUS_TYPE_TEAM;
            $bonusTitle = 'Team';
            $totalUrl = route('main.bonus.team.total');
            $dataUrl = route('main.bonus.team.datatable');
            $downloadExcel = route('main.bonus.team.download.excel');
        }

        return view('main.bonuses.table-detail', [
            'dateRange' => $dateRange,
            'bonusType' => $bonusType,
            'totalUrl' => $totalUrl,
            'dataUrl' => $dataUrl,
            'downloadExcel' => $downloadExcel,
            'filterIntExt' => $filterIntExt,
            'windowTitle' => "Bonus {$bonusTitle}",
            'breadcrumbs' => ['Bonus', $bonusTitle],
        ]);
    }

    public function indexSummary(Request $request)
    {
        $prefixName = $request->summaryName;

        if (!in_array($prefixName, $this->summaryPageNames)) {
            return pageError('Halaman tidak ditemukan.');
        }

        if ($prefixName == 'total') {
            return $this->indexTotalSummary($request);
        }

        return $this->indexDetailSummary($request);
    }

    public function indexTotalSummary(Request $request)
    {
        $dateRange = $this->dateFilter();

        return view('main.bonuses.summary-total', [
            'dateRange' => $dateRange,
            'activeMenu' => 'totalSummary',
            'bonusTitle' => 'Total Summary',
            'windowTitle' => 'Bonus Summary',
            'breadcrumbs' => ['Bonus', 'Summary', 'Total'],
        ]);
    }

    public function indexDetailSummary(Request $request)
    {
        $dateRange = $this->dateFilter();
        $prefixName = $request->summaryName;
        // $activeMenu = 'royaltySummary';
        // $bonusType = BONUS_TYPE_ROYALTY;
        // $bonusTitle = 'Royalty';

        // if ($prefixName == 'override') {
        //     $bonusType = BONUS_TYPE_OVERRIDE;
        //     $activeMenu = 'overrideSummary';
        //     $bonusTitle = 'Override';
        // } elseif ($prefixName == 'team') {
        //     $bonusType = BONUS_TYPE_TEAM;
        //     $activeMenu = 'teamSummary';
        //     $bonusTitle = 'Team';
        // } elseif ($prefixName == 'sale') {
        //     $bonusType = BONUS_TYPE_SALE;
        //     $activeMenu = 'saleSummary';
        //     $bonusTitle = 'Penjualan';
        // }

        $bonusType = BONUS_TYPE_SALE;
        $activeMenu = 'saleSummary';
        $bonusTitle = 'Penjualan';

        if ($prefixName == 'team') {
            $bonusType = BONUS_TYPE_TEAM;
            $activeMenu = 'teamSummary';
            $bonusTitle = 'Team';
        }

        return view('main.bonuses.summary-detail', [
            'dateRange' => $dateRange,
            'bonusType' => $bonusType,
            'activeMenu' => $activeMenu,
            'prefixName' => $prefixName,
            'bonusTitle' => 'Summary Bonus ' . $bonusTitle,
            'windowTitle' => 'Summary Bonus ' . $bonusTitle,
            'breadcrumbs' => ['Bonus', 'Summary', $bonusTitle],
        ]);
    }

    private function querySourceMitra(Carbon $startDate, Carbon $endDate): Builder
    {
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');
        $statusApproved = PROCESS_STATUS_APPROVED;
        $productBox = PRODUCT_UNIT_BOX;
        $productPcs = PRODUCT_UNIT_PCS;

        return DB::table('mitra_purchases')
            ->join('users', function ($join) {
                return $join->on('users.id', '=', 'mitra_purchases.mitra_id')
                    ->whereRaw('(users.user_group = ' . USER_GROUP_MEMBER . ')')
                    ->whereRaw('(users.user_type = ' . USER_TYPE_MITRA . ')');
            })
            ->join('branches', 'branches.id', '=', 'users.branch_id')
            ->join('mitra_purchase_products', 'mitra_purchase_products.mitra_purchase_id', '=', 'mitra_purchases.id')
            ->selectRaw("
            mitra_purchases.id,
            mitra_purchases.id as transaction_id,
            mitra_purchases.code as transfer_code,
            mitra_purchases.code as transaction_code,
            branches.name as branch_name,
            SUM(CASE WHEN mitra_purchase_products.product_unit = {$productBox} THEN mitra_purchase_products.product_qty ELSE 0 END) as qty_box,
            SUM(CASE WHEN mitra_purchase_products.product_unit = {$productPcs} THEN mitra_purchase_products.product_qty ELSE 0 END) as qty_pcs,
            mitra_purchases.total_purchase as total_price,
            0 as is_internal
            ")
            ->whereRaw("(mitra_purchases.purchase_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(mitra_purchases.purchase_status = {$statusApproved})")
            ->whereRaw("(mitra_purchases.is_active = 1)")
            ->whereRaw("(mitra_purchases.deleted_at is NULL)")
            ->groupByRaw("
            mitra_purchases.id,
            mitra_purchases.id,
            mitra_purchases.code,
            branches.name,
            mitra_purchases.total_purchase
            ");
    }

    private function querySourceMember(Carbon $startDate, Carbon $endDate): Builder
    {
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');
        $statusApproved = PROCESS_STATUS_APPROVED;
        $productBox = PRODUCT_UNIT_BOX;
        $productPcs = PRODUCT_UNIT_PCS;

        return DB::table('branch_transfers')
            ->join('branch_transfer_details', 'branch_transfer_details.transfer_id', '=', 'branch_transfers.id')
            ->join('branches', 'branches.id', '=', 'branch_transfers.branch_id')
            ->join('branch_sales', 'branch_sales.id', '=', 'branch_transfer_details.sale_id')
            ->join('branch_sales_products', 'branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
            ->selectRaw("
            branch_transfers.id,
            branch_transfer_details.sale_id as transaction_id,
            branch_transfers.code as transfer_code,
            branch_sales.code as transaction_code,
            branches.name as branch_name,
            SUM(CASE WHEN branch_sales_products.product_unit = {$productBox} THEN branch_sales_products.product_qty ELSE 0 END) as qty_box,
            SUM(CASE WHEN branch_sales_products.product_unit = {$productPcs} THEN branch_sales_products.product_qty ELSE 0 END) as qty_pcs,
            SUM(branch_sales_products.total_price) as total_price,
            1 as is_internal
            ")
            ->whereRaw("(branch_transfers.transfer_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(branch_transfers.transfer_status = {$statusApproved})")
            ->whereRaw("(branch_transfers.deleted_at is NULL)")
            ->groupByRaw("
            branch_transfers.id,
            branch_transfer_details.sale_id,
            branch_transfers.code,
            branch_sales.code,
            branches.name
            ");
    }

    public function totalBonus(Request $request)
    {
        // ini dikhususkan summary member internal (none mitra)
        $routeName = $request->route()->getName();
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $bonusType = BONUS_TYPE_ROYALTY;
        $royaltyExternal = false;

        $baseColumns = [
            'user_id',
            'position_id',
            'bonus_type',
            'bonus_date',
            'is_internal',
            'level_id',
            'bonus_base',
            'bonus_percent',
            DB::raw('floor(bonus_amount) as bonus_amount'),
            'transfer_id',
            'transaction_id',
        ];

        $queryBonus = DB::table('bonus_members')
            ->whereRaw("(bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')");

        if ($routeName == 'main.bonus.override.total') {
            $bonusType = BONUS_TYPE_OVERRIDE;
        } elseif ($routeName == 'main.bonus.team.total') {
            $bonusType = BONUS_TYPE_TEAM;
        } elseif ($routeName == 'main.bonus.sale.total') {
            $bonusType = BONUS_TYPE_SALE;
            $queryBonus = $queryBonus->whereRaw('(is_internal = 1)');
        } else {
            $isInternal = (bool) intval($request->get('internal', '0'));
            $queryBonus = $queryBonus->whereRaw('(is_internal = ' . ($isInternal ? 1 : 0) . ')');
            $royaltyExternal = !$isInternal;
        }

        $queryBonus = $queryBonus
            ->whereRaw("(bonus_type = {$bonusType})")
            ->select($baseColumns);

        if (($bonusType == BONUS_TYPE_ROYALTY) && $royaltyExternal) {
            $queryTransfer = $this->querySourceMitra($startDate, $endDate)->toSql();
        } else {
            $queryTransfer = $this->querySourceMember($startDate, $endDate)->toSql();
        }

        $bonuses = DB::table(DB::raw('(' . $queryBonus->toSql() . ') as bonuses'))
            ->join(DB::raw("({$queryTransfer}) as transfer"), function ($join) {
                return $join->on('transfer.id', '=', 'bonuses.transfer_id')
                    ->on('transfer.transaction_id', '=', 'bonuses.transaction_id');
            })
            ->leftJoin('users', function ($join) {
                return $join->on('users.id', '=', 'bonuses.user_id')
                    ->whereRaw('(users.user_group = ' . USER_GROUP_MEMBER . ')')
                    ->whereRaw('(users.user_type = ' . USER_TYPE_MEMBER . ')');
            })
            ->selectRaw("
                bonuses.bonus_date,
                bonuses.user_id,
                bonuses.transfer_id,
                bonuses.transaction_id,
                transfer.transfer_code,
                transfer.transaction_code,
                transfer.branch_name,
                bonuses.bonus_base,
                bonuses.bonus_percent,
                SUM(bonuses.bonus_amount) as bonus_amount
            ")
            ->groupByRaw("
                bonuses.bonus_date,
                bonuses.user_id,
                bonuses.transfer_id,
                bonuses.transaction_id,
                transfer.transfer_code,
                transfer.transaction_code,
                transfer.branch_name,
                bonuses.bonus_base,
                bonuses.bonus_percent
            ")
            ->orderByRaw("
                transfer.branch_name,
                bonuses.transfer_id,
                transfer.transaction_id
            ")
            ->get()
            ->groupBy('bonus_date');

        $totalAll = 0;
        $groupUsers = [];

        foreach ($bonuses as $bonusDate => $bonusByDate) {
            $groupDateUserId = "tr#groupUser___{$bonusDate}";
            $groupByUsers = $bonusByDate->groupBy('user_id');

            foreach ($groupByUsers as $userId => $bonusUsers) {
                $groupDateKey = "{$groupDateUserId}___{$userId}";

                $userDataByDate = [
                    'target_id' => $groupDateKey,
                    'details' => [],
                ];

                foreach ($bonusUsers as $bonusUser) {
                    $bName = $bonusUser->branch_name;
                    $tCode = $bonusUser->transaction_code;
                    $userDataByDate['details'][] = [
                        'branch' => "<span class=\"fw-bold\">{$bName}</span><span class=\"mx-1\">:</span><span>{$tCode}</span>",
                        'omzet' => formatCurrency($bonusUser->bonus_base, 0, true, false),
                        'percent' => formatAutoNumber($bonusUser->bonus_percent, false, 2) . '%',
                        'bonus' => formatCurrency($bonusUser->bonus_amount, 0, true, false),
                    ];

                    $totalAll += $bonusUser->bonus_amount;
                }

                $groupUsers[] = $userDataByDate;
            }
        }

        $response = [
            'total_all' => formatCurrency($totalAll, 0, true, true),
            'rows' => $groupUsers
        ];

        return response()->json($response);
    }

    public function dataTable(Request $request)
    {
        // ini dikhususkan summary member internal (none mitra)
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $bonusType = $request->get('bonus_type', BONUS_TYPE_ROYALTY);
        $saleMitra = (bool) intval($request->get('is_mitra', '0'));
        $isSaleMitra = (($bonusType == BONUS_TYPE_SALE) && $saleMitra);
        $supportId = USER_INT_NONE;
        $supportName = $this->appStructure->namePositionById(true, USER_INT_NONE);
        $userGroupMember = USER_GROUP_MEMBER;
        $userTypeMember = USER_TYPE_MEMBER;
        $userType = $isSaleMitra ? USER_TYPE_MITRA : USER_TYPE_MEMBER;
        $onlyPositions = $isSaleMitra ? [USER_EXT_MTR] : [];
        $selectMemberPosition = $this->appStructure->getSqlPositions(!$isSaleMitra, $onlyPositions);

        $isRoyaltyExternal = false;

        $internal = [0, 1];
        if ($request->route()->getName() == 'main.bonus.royalty.datatable') {
            $isRoyaltyInternal = (bool) intval($request->get('internal', '0'));
            $internal = [$isRoyaltyInternal ? 1 : 0];
            $isRoyaltyExternal = !$isRoyaltyInternal;
        }

        $internal = implode(',', $internal);

        $query = DB::table('bonus_members')
            ->join(DB::raw("(
                SELECT {$supportId} as id, '{$supportName}' as name, '{$supportName}' as username, {$supportId} as position_id 
                UNION
                SELECT id, name, username,
                (CASE WHEN user_type = $userTypeMember THEN position_int ELSE position_ext END) as position_id
                FROM users
                WHERE (user_group = {$userGroupMember}) AND (user_type = {$userType})
            ) as members"), 'members.id', '=', 'bonus_members.user_id')
            ->join(DB::raw("({$selectMemberPosition}) as member_positions"), 'member_positions.id', '=', 'members.position_id')
            ->join(DB::raw("(
                SELECT bm.bonus_date, SUM(floor(bm.bonus_amount)) as total_date
                FROM bonus_members as bm
                WHERE 
                (bm.bonus_type = {$bonusType}) 
                AND (bm.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')
                AND (bm.is_internal IN({$internal}))
                GROUP BY bm.bonus_date
                having (SUM(floor(bm.bonus_amount)) > 0)
            ) as summary_dates"), 'summary_dates.bonus_date', '=', 'bonus_members.bonus_date')
            ->selectRaw("
                bonus_members.user_id,
                bonus_members.bonus_date,
                bonus_members.bonus_date as tanggal,
                members.name,
                members.username,
                members.position_id,
                member_positions.name as position_name,
                member_positions.code as position_code,
                COALESCE(summary_dates.total_date, 0) as total_tgl,
                SUM(floor(bonus_members.bonus_amount)) as total_user,
                '' as branch_names, '' as omzets_transaction, '' as percents_bonus
            ")
            ->whereRaw("(bonus_members.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(bonus_members.bonus_type = {$bonusType})")
            ->whereRaw("(bonus_members.is_internal IN({$internal}))")
            ->groupByRaw('
                bonus_members.user_id,
                bonus_members.bonus_date,
                members.name,
                members.username,
                members.position_id,
                member_positions.name,
                member_positions.code,
                summary_dates.total_date
            ')
            ->havingRaw("(SUM(floor(bonus_members.bonus_amount)) > 0)")
            ->toSql();

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
        ]);

        $result = datatables()->query(DB::table(DB::raw("({$query}) as bonusan")))
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->tanggal);
            })
            ->editColumn('position_id', function ($row) use ($bonusType, $isRoyaltyExternal) {
                $positionName = $row->position_name;
                if (($row->position_id == USER_INT_MGR) && ($bonusType == BONUS_TYPE_ROYALTY) && ($isRoyaltyExternal === true)) {
                    $positionName .= '<span class="ms-1 fw-normal fst-italic">(Agent Referral)</span>';

                    return new HtmlString($positionName);
                }

                return $positionName;
            })
            ->editColumn('total_tgl', function ($row) {
                return floatval($row->total_tgl);
            })
            ->editColumn('total_user', function ($row) {
                return floatval($row->total_user);
            })
            ->escapeColumns();

        return $result->toJson();
    }

    private function baseQueryTotalSummary(Request $request)
    {
        $selectMemberPosition = $this->appStructure->getSqlPositions(true);
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $bonusTypeRoyalty = BONUS_TYPE_ROYALTY;
        $bonusTypeOverride = BONUS_TYPE_OVERRIDE;
        $bonusTypeTeam = BONUS_TYPE_TEAM;
        $bonusTypeSale = BONUS_TYPE_SALE;
        $userGroupMember = USER_GROUP_MEMBER;
        $userTypeMember = USER_TYPE_MEMBER;
        $supportId = USER_INT_NONE;
        $supportName = $this->appStructure->namePositionById(true, USER_INT_NONE);

        $sumQuery = DB::table('bonus_members')
            ->join(DB::raw("(
                SELECT 0 as id, '{$supportName}' as name, {$supportId} as position_id
                UNION ALL
                SELECT id, name, position_int as position_id
                FROM users
                WHERE (user_group = {$userGroupMember}) AND (user_type = {$userTypeMember})
                ) as members
            "), 'members.id', '=', 'bonus_members.user_id')
            ->join(DB::raw("({$selectMemberPosition}) as positions"), 'positions.id', '=', 'bonus_members.position_id')
            ->whereRaw("(bonus_members.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(bonus_members.bonus_type in({$bonusTypeTeam}, {$bonusTypeSale}))")
            ->selectRaw("
                bonus_members.user_id,
                bonus_members.position_id,
                bonus_members.bonus_type,
                members.name,
                positions.name as position_name,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeRoyalty} THEN bonus_members.qty_box
                ELSE 0 END) as box_royalty,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeRoyalty} THEN bonus_members.qty_pcs
                ELSE 0 END) as pcs_royalty,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeOverride} THEN bonus_members.qty_box
                ELSE 0 END) as box_override,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeOverride} THEN bonus_members.qty_pcs
                ELSE 0 END) as pcs_override,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeTeam} THEN bonus_members.qty_box
                ELSE 0 END) as box_team,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeTeam} THEN bonus_members.qty_pcs
                ELSE 0 END) as pcs_team,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeSale} THEN bonus_members.qty_box
                ELSE 0 END) as box_sale,
                SUM(CASE WHEN bonus_members.bonus_type = {$bonusTypeSale} THEN bonus_members.qty_pcs
                ELSE 0 END) as pcs_sale,
                SUM(CASE 
                    WHEN bonus_members.bonus_type = {$bonusTypeRoyalty} THEN floor(bonus_members.bonus_amount) 
                    ELSE 0 END) as total_royalty,
                SUM(CASE 
                    WHEN bonus_members.bonus_type = {$bonusTypeOverride} THEN floor(bonus_members.bonus_amount) 
                    ELSE 0 END) as total_override,
                SUM(CASE 
                    WHEN bonus_members.bonus_type = {$bonusTypeTeam} THEN floor(bonus_members.bonus_amount) 
                    ELSE 0 END) as total_team,
                SUM(CASE 
                    WHEN bonus_members.bonus_type = {$bonusTypeSale} THEN floor(bonus_members.bonus_amount) 
                    ELSE 0 END) as total_sale
            ")
            ->groupByRaw("
                bonus_members.user_id,
                bonus_members.position_id,
                bonus_members.bonus_type,
                members.name,
                positions.name
            ")
            ->toSql();

        $query = DB::table(DB::raw("({$sumQuery}) as bonus_bonus"))
            ->selectRaw("
                user_id, 
                position_id, 
                name, 
                position_name,
                SUM(box_royalty) as box_royalty,
                SUM(pcs_royalty) as pcs_royalty,
                SUM(box_override) as box_override,
                SUM(pcs_override) as pcs_override,
                SUM(box_team) as box_team,
                SUM(pcs_team) as pcs_team,
                SUM(box_sale) as box_sale,
                SUM(pcs_sale) as pcs_sale,
                SUM(total_royalty) as royalty,
                SUM(total_override) as override,
                SUM(total_team) as team,
                SUM(total_sale) as sale,
                SUM(total_team + total_sale) as total_bonus
            ")
            ->groupByRaw('user_id, name, position_id, position_name')
            ->havingRaw("SUM(total_team + total_sale) > 0");

        // SUM(total_royalty + total_override + total_team + total_sale) as total_bonus

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'query' => $query,
        ];
    }

    public function dataTableSummary(Request $request)
    {
        $prefixName = $request->summaryName;

        if (!in_array($prefixName, $this->summaryPageNames)) {
            return ajaxError('Data tidak ditemukan.', 404);
        }

        if ($prefixName == 'total') {
            return $this->dataTableTotalSummary($request);
        }

        return $this->dataTableDetailSummary($request);
    }

    public function dataTableTotalSummary(Request $request)
    {
        $data = $this->baseQueryTotalSummary($request);

        session([
            'filter.dates' => ['start' => $data->startDate, 'end' => $data->endDate],
        ]);

        $result = datatables()->query($data->query)
            ->editColumn('position_id', function ($row) {
                return $row->position_name;
            })
            ->editColumn('royalty', function ($row) {
                return floatval($row->royalty);
            })
            ->editColumn('override', function ($row) {
                return floatval($row->override);
            })
            ->editColumn('team', function ($row) {
                return floatval($row->team);
            })
            ->editColumn('sale', function ($row) {
                return floatval($row->sale);
            })
            ->editColumn('total_bonus', function ($row) {
                return floatval($row->total_bonus);
            })
            ->escapeColumns();

        return $result->toJson();
    }

    private function baseQueryDetailSummary(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $bonusType = $request->get('bonus_type', BONUS_TYPE_ROYALTY);
        $saleMitra = (bool) intval($request->get('is_mitra', '0'));
        $isSaleMitra = (($bonusType == BONUS_TYPE_SALE) && $saleMitra);
        $supportId = USER_INT_NONE;
        $supportName = $this->appStructure->namePositionById(true, USER_INT_NONE);
        $userGroupMember = USER_GROUP_MEMBER;
        $userTypeMember = USER_TYPE_MEMBER;
        $userType = $isSaleMitra ? USER_TYPE_MITRA : USER_TYPE_MEMBER;
        $onlyPositions = $isSaleMitra ? [USER_EXT_MTR] : [];
        $selectMemberPosition = $this->appStructure->getSqlPositions(!$isSaleMitra, $onlyPositions);

        $query = DB::table('bonus_members')
            ->join(DB::raw("({$selectMemberPosition}) as positions"), 'positions.id', '=', 'bonus_members.position_id')
            ->join(DB::raw("(
                SELECT {$supportId} as id, '{$supportName}' as name, '{$supportName}' as username, {$supportId} as position_id 
                UNION
                SELECT id, name, username,
                (CASE WHEN user_type = $userTypeMember THEN position_int ELSE position_ext END) as position_id
                FROM users
                WHERE (user_group = {$userGroupMember}) AND (user_type = {$userType})
            ) as members"), 'members.id', '=', 'bonus_members.user_id')
            ->join(DB::raw("(
                SELECT bm.position_id, SUM(floor(bm.bonus_amount)) as total_position
                FROM bonus_members as bm
                WHERE (bm.bonus_type = {$bonusType}) AND (bm.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')
                GROUP BY bm.position_id
                having (SUM(floor(bm.bonus_amount)) > 0)
            ) as summary_positions"), 'summary_positions.position_id', '=', 'bonus_members.position_id')
            ->selectRaw("
                bonus_members.user_id,
                members.name,
                members.username,
                bonus_members.position_id,
                bonus_members.position_id as position,
                positions.name as position_name,
                positions.code as position_code,
                COALESCE(summary_positions.total_position, 0) as total_position,
                SUM(floor(bonus_members.bonus_amount)) as total_user
            ")
            ->whereRaw("(bonus_members.bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')")
            ->whereRaw("(bonus_members.bonus_type = {$bonusType})")
            ->groupByRaw('
                bonus_members.user_id,
                members.name,
                members.username,
                bonus_members.position_id,
                positions.name,
                positions.code,
                summary_positions.total_position
            ')
            ->havingRaw("(SUM(floor(bonus_members.bonus_amount)) > 0)");

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'query' => $query
        ];
    }

    public function dataTableDetailSummary(Request $request)
    {
        $data = $this->baseQueryDetailSummary($request);

        session([
            'filter.dates' => ['start' => $data->startDate, 'end' => $data->endDate],
        ]);

        $sql = $data->query->toSql();

        $result = datatables()->query(DB::table(DB::raw("({$sql}) as bonusan")))
            ->editColumn('position', function ($row) {
                return $row->position_name;
            })
            ->editColumn('total_user', function ($row) {
                return floatval($row->total_user);
            })
            ->escapeColumns();

        return $result->toJson();
    }

    // export
    private function queryDownload(Request $request): stdClass
    {
        $routeName = $request->route()->getName();
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $baseColumns = [
            'user_id',
            'position_id',
            'bonus_type',
            'bonus_date',
            'is_internal',
            DB::raw('COALESCE(level_id, -1) as level_id'),
            'bonus_base',
            'bonus_percent',
            DB::raw('floor(bonus_amount) as bonus_amount'),
            'transfer_id',
            'transaction_id',
        ];

        $bonusType = $request->get('bonus_type', BONUS_TYPE_ROYALTY);
        $supportId = USER_INT_NONE;
        $supportName = $this->appStructure->namePositionById(true, USER_INT_NONE);
        $userGroupMember = USER_GROUP_MEMBER;
        $userTypeMember = USER_TYPE_MEMBER;
        $royaltyExternal = false;
        $dataName = 'Royalty';

        $queryBonus = DB::table('bonus_members')
            ->whereRaw("(bonus_date BETWEEN '{$formatStart}' AND '{$formatEnd}')");

        if ($routeName == 'main.bonus.override.download.excel') {
            $bonusType = BONUS_TYPE_OVERRIDE;
            $dataName = 'Override';
        } elseif ($routeName == 'main.bonus.team.download.excel') {
            $bonusType = BONUS_TYPE_TEAM;
            $dataName = 'Team';
        } elseif ($routeName == 'main.bonus.sale.download.excel') {
            $bonusType = BONUS_TYPE_SALE;
            $dataName = 'Penjualan';
            $queryBonus = $queryBonus->whereRaw('(is_internal = 1)');
        } else {
            $isInternal = (bool) intval($request->get('internal', '0'));
            $queryBonus = $queryBonus->whereRaw('(is_internal = ' . ($isInternal ? 1 : 0) . ')');
            $royaltyExternal = !$isInternal;
        }

        $selectMemberPosition = $this->appStructure->getSqlPositions(true);

        $queryBonus = $queryBonus
            ->whereRaw("(bonus_type = {$bonusType})")
            ->select($baseColumns);

        if (($bonusType == BONUS_TYPE_ROYALTY) && $royaltyExternal) {
            $queryTransfer = $this->querySourceMitra($startDate, $endDate)->toSql();
        } else {
            $queryTransfer = $this->querySourceMember($startDate, $endDate)->toSql();
        }

        $bonuses = DB::table(DB::raw('(' . $queryBonus->toSql() . ') as bonuses'))
            ->join(DB::raw("({$queryTransfer}) as transfer"), function ($join) {
                return $join->on('transfer.id', '=', 'bonuses.transfer_id')
                    ->on('transfer.transaction_id', '=', 'bonuses.transaction_id');
            })
            ->join(DB::raw("(
                SELECT {$supportId} as id, '{$supportName}' as name, '{$supportName}' as username, {$supportId} as position_id 
                UNION
                SELECT id, name, username,
                position_int as position_id
                FROM users
                WHERE (user_group = {$userGroupMember}) AND (user_type = {$userTypeMember})
            ) as members"), 'members.id', '=', 'bonuses.user_id')
            ->join(DB::raw("({$selectMemberPosition}) as member_positions"), 'member_positions.id', '=', 'members.position_id')
            ->selectRaw("
                bonuses.bonus_date,
                bonuses.user_id,
                members.name as member_name,
                members.position_id as member_position_id,
                member_positions.code as member_position_code,
                member_positions.name as member_position_name,
                bonuses.transfer_id,
                transfer.transaction_id,
                transfer.transfer_code,
                transfer.transaction_code,
                transfer.branch_name as branch_name,
                bonuses.bonus_base,
                bonuses.bonus_percent,
                SUM(bonuses.bonus_amount) as bonus_amount
            ")
            ->groupByRaw("
                bonuses.bonus_date,
                bonuses.user_id,
                members.name,
                members.position_id,
                member_positions.code,
                member_positions.name,
                bonuses.transfer_id,
                transfer.transaction_id,
                transfer.transfer_code,
                transfer.transaction_code,
                transfer.branch_name,
                bonuses.bonus_base,
                bonuses.bonus_percent
            ")
            ->orderByRaw("
                bonuses.bonus_date,
                members.position_id,
                members.name,
                transfer.branch_name,
                bonuses.transfer_id,
                transfer.transaction_id
            ")
            ->get();

        return (object) [
            'dataName' => $dataName,
            'routeName' => $routeName,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'bonusType' => $bonusType,
            'royaltyExternal' => $royaltyExternal,
            'rows' => $bonuses
        ];
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->queryDownload($request);
        $rows = $data->rows;

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $startFormatted = $data->startDate->format('Ymd');
        $endFormatted = $data->endDate->format('Ymd');
        $tglName = $startFormatted;
        $tglReport = formatFullDate($data->startDate);

        if ($startFormatted != $endFormatted) {
            $tglName = "{$startFormatted}-{$endFormatted}";
            $tglReport = $tglReport . ' s/d ' . formatFullDate($data->endDate);
        }

        $downloadName = $dataName = $data->dataName;
        $royaltyExternal = $data->royaltyExternal;
        $bonusType = $data->bonusType;

        if ($data->bonusType == BONUS_TYPE_ROYALTY) {
            $downloadName = $downloadName . '-' . ($data->royaltyExternal ? 'External' : 'Internal');
        }
        $downloadName = "Rincian-{$downloadName}-{$tglName}.xlsx";

        SimpleExcelWriter::streamDownload($downloadName, 'xlsx', function (WriterInterface $writer) use ($downloadName, $tglReport, $dataName, $bonusType, $royaltyExternal, $rows) {
            $writer->openToBrowser($downloadName);

            $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->build();
            $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->setCellAlignment('center')->setBackgroundColor(Color::LIGHT_BLUE)->build();
            $rowStyle = (new StyleBuilder)->setFontSize(10)->build();
            $rowBoldStyle = (new StyleBuilder)->setFontSize(10)->setFontBold()->build();

            $mainTitle = 'DAFTAR RINCIAN BONUS ' . strtoupper($dataName);
            if ($bonusType == BONUS_TYPE_ROYALTY) {
                if ($royaltyExternal) {
                    $mainTitle .= ' EXTERNAL';
                } else {
                    $mainTitle .= ' INTERNAL';
                }
            }

            $writer->addRow(new Row([new Cell('')], null));
            $writer->addRow(new Row([new Cell($mainTitle)], $titleStyle));
            $writer->addRow(new Row([new Cell('')], null));
            $writer->addRow(new Row([
                new Cell('Tanggal'),
                new Cell(':'),
                new Cell($tglReport)
            ], $titleStyle));

            $writer->addRow(new Row([
                new Cell('No'),
                new Cell('Tanggal'),
                new Cell('Posisi'),
                new Cell('Nama'),
                new Cell(''),
                new Cell('Bonus Dari'),
                new Cell(''),
                new Cell('Bonus (%)'),
                new Cell('Jml. Bonus (Rp)'),
            ], $headerStyle));
            $writer->addRow(new Row([
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell('Cabang'),
                new Cell('No. Transaksi'),
                new Cell('Omzet (Rp)'),
                new Cell(''),
                new Cell(''),
            ], $headerStyle));

            $byDates = $rows->groupBy('bonus_date');
            $nomor = 1;
            $dateBonus = '';
            $totalBonus = 0;

            foreach ($byDates as $bonusDate => $byDate) {
                $position = '';
                foreach ($byDate->groupBy('member_position_name') as $positionName => $byPosition) {
                    $userName = '';
                    foreach ($byPosition->groupBy('member_name') as $memberName => $memberBonus) {
                        $bonusNomor = 0;
                        $userTotalBonus = 0;
                        foreach ($memberBonus as $bonus) {
                            $bonusValue = intval(floor($bonus->bonus_amount));

                            $writer->addRow(new Row([
                                new Cell(($bonusNomor != $nomor) ? $nomor : '', $rowStyle),
                                new Cell(($dateBonus != $bonusDate) ? formatFullDate($bonusDate) : '', $rowBoldStyle),
                                new Cell(($position != $positionName) ? $positionName : '', $rowBoldStyle),
                                new Cell(($userName != $memberName) ? $memberName : '', $rowBoldStyle),
                                new Cell($bonus->branch_name, $rowStyle),
                                new Cell($bonus->transaction_code, $rowStyle),
                                new Cell(intval($bonus->bonus_base), $rowStyle),
                                new Cell(floatval($bonus->bonus_percent), $rowStyle),
                                new Cell($bonusValue, $rowStyle),
                            ], null));

                            $dateBonus = $bonusDate;
                            $position = $positionName;
                            $userName = $memberName;
                            $bonusNomor = $nomor;
                            $userTotalBonus += $bonusValue;
                            $totalBonus += $bonusValue;
                        }

                        // $writer->addRow(new Row([
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell(''),
                        //     new Cell($userTotalBonus),
                        // ], $rowBoldStyle));

                        $userName = $memberName;
                        $nomor++;
                    }

                    $userName = '';
                }
            }

            $writer->addRow(new Row([
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell(''),
                new Cell($totalBonus),
            ], $rowBoldStyle));
        })->toBrowser();
    }

    public function downloadSummary(Request $request)
    {
        $prefixName = $request->summaryName;

        if (!in_array($prefixName, $this->summaryPageNames)) {
            return pageError('Halaman tidak ditemukan.');
        }

        if ($prefixName == 'total') {
            return $this->downloadReportTotalSummary($request);
        }

        return $this->downloadDetailSummary($request);
    }

    public function downloadReportTotalSummary(Request $request)
    {
        $base = $this->baseQueryTotalSummary($request);

        $rows = $base->query
            ->orderBy('position_id')
            ->orderBy('name')
            ->orderBy('user_id')
            ->get();

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $tgl = $base->startDate->format('Ymd');

        if ($tgl != $base->endDate->format('Ymd')) {
            $tgl .= '-' . $base->endDate->format('Ymd');
        }

        $fileFormat = strtolower($request->exportFormat);
        if ($fileFormat != 'pdf') {
            $fileFormat = 'xlsx';
        }

        $filename = "TotalSummaryBonus-{$tgl}.{$fileFormat}";
        $titleHeader = 'Laporan Total Bonus';

        if ($fileFormat == 'pdf') {
            return Pdf::loadView('main.bonuses.pdf.summary-total', [
                'filename' => $filename,
                'rows' => $rows,
                'startDate' => $base->startDate,
                'endDate' => $base->endDate,
                'titleHeader' => $titleHeader,
            ])->setPaper(config('dompdf.defines.default_paper_size'), 'landscape')->stream($filename);
        }

        return (new SummaryBonus(
            $rows,
            $base->startDate,
            $base->endDate,
            $titleHeader
        ))->download($filename);
    }

    public function downloadDetailSummary(Request $request)
    {
        $base = $this->baseQueryDetailSummary($request);

        $rows = $base->query
            // ->orderBy('bonus_members.position_id')
            ->orderBy('members.position_id')
            ->orderBy('members.name')
            ->orderBy('members.username')
            ->get();

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $tgl = $base->startDate->format('Ymd');

        if ($tgl != $base->endDate->format('Ymd')) {
            $tgl .= '-' . $base->endDate->format('Ymd');
        }

        $name = $request->summaryName;
        if ($name == 'sale') $name = 'Penjualan';
        $name = ucfirst($name);

        $fileFormat = strtolower($request->exportFormat);
        if ($fileFormat != 'pdf') {
            $fileFormat = 'xlsx';
        }

        $filename = "SummaryBonus{$name}-{$tgl}.{$fileFormat}";
        $titleHeader = "Laporan Total Bonus {$name}";

        if ($fileFormat == 'pdf') {
            return Pdf::loadView('main.bonuses.pdf.summary-detail', [
                'filename' => $filename,
                'rows' => $rows,
                'startDate' => $base->startDate,
                'endDate' => $base->endDate,
                'titleHeader' => $titleHeader,
            ])->stream($filename);
        }

        return (new SummaryDetailBonus(
            $rows,
            $base->startDate,
            $base->endDate,
            $titleHeader
        ))->download($filename);
    }
}
