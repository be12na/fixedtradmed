<?php

namespace App\Http\Controllers\Member;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class TeamController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $activeTeams = $request->user()->active_team_without_self_non_manager_list;

        return view('member.team.index', [
            'activeTeams' => $activeTeams,
            'windowTitle' => 'Daftar Anggota',
            'breadcrumbs' => ['Anggota', 'Daftar']
        ]);
    }

    public function mitraIndex(Request $request)
    {
        $routeName = $request->route()->getName();

        if ($routeName == 'member.directMitra.histories.index') {
            $requestMitraId = $request->get('mitra_id');

            if (!empty($requestMitraId)) {
                session([
                    'filter.mitraId' => intval($requestMitraId),
                ]);

                return redirect()->route('member.directMitra.histories.index');
            }

            $currentMitraId = session('filter.mitraId', -1);
            $currentMitra = User::byId($currentMitraId)->first();

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

            return view('member.team.mitra.history', [
                'dateRange' => $dateRange,
                'currentMitra' => $currentMitra,
            ]);
        }

        return view('member.team.mitra.index', [
            'windowTitle' => 'Daftar Mitra',
            'breadcrumbs' => ['Mitra', 'Daftar']
        ]);
    }

    public function mitraDatatable(Request $request)
    {
        $user = $request->user();
        $routeName = $request->route()->getName();

        if ($routeName == 'member.directMitra.histories.datatable') {
            $mitraFilter = $request->get('mitra_id', -1);
            $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
            $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
            $startDate = Carbon::createFromTimestamp(strtotime($start_date));
            $endDate = Carbon::createFromTimestamp(strtotime($end_date));

            $minBonusDate = DB::table('bonus_members')
                ->where('bonus_type', '=', BONUS_TYPE_MGR_DIRECT_MITRA)
                ->where('user_id', '=', $user->id)
                ->min('bonus_date');

            if (empty($minBonusDate)) $minBonusDate = DATE_BONUS_MGR_DIRECT_MITRA;

            $query = DB::table('mitra_purchases')
                ->leftJoin('bonus_members', function ($bonusJoin) use ($user) {
                    return $bonusJoin->on('bonus_members.transfer_id', '=', 'mitra_purchases.id')
                        ->where('bonus_members.user_id', '=', $user->id)
                        ->where('bonus_members.bonus_type', '=', BONUS_TYPE_MGR_DIRECT_MITRA)
                        ->where('bonus_members.is_internal', '=', false);
                })
                ->join(DB::raw('users as mitra'), 'mitra.id', '=', 'mitra_purchases.mitra_id')
                ->selectRaw("
                mitra_purchases.id, mitra_purchases.purchase_date, mitra_purchases.code, 
                mitra_purchases.total_transfer, mitra_purchases.purchase_status,
                mitra.name as mitra_name,
                COALESCE(bonus_members.bonus_amount, 0) as bonus_referral
                ")
                ->whereBetween('mitra_purchases.purchase_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('mitra_purchases.purchase_status', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])
                ->whereNull('mitra_purchases.deleted_at')
                ->where('mitra_purchases.purchase_date', '>=', $minBonusDate)
                ->where('mitra_purchases.referral_id', '=', $user->id);

            if ($mitraFilter > -1) {
                $query = $query->where('mitra_purchases.mitra_id', '=', $mitraFilter);
            }

            session([
                'filter.dates' => ['start' => $startDate, 'end' => $endDate],
                'filter.mitraId' => $mitraFilter,
            ]);

            return datatables()->query($query)
                ->editColumn('purchase_date', function ($row) {
                    return formatFullDate($row->purchase_date);
                })
                ->editColumn('total_transfer', function ($row) {
                    return new HtmlString(formatCurrency($row->total_transfer, 0, true));
                })
                ->editColumn('bonus_referral', function ($row) {
                    return new HtmlString(formatCurrency($row->bonus_referral, 0, true));
                })
                ->addColumn('status', function ($row) {
                    $cls = 'bg-warning';
                    if ($row->purchase_status == PROCESS_STATUS_APPROVED) {
                        $cls = 'bg-success text-light';
                    } elseif ($row->purchase_status == PROCESS_STATUS_REJECTED) {
                        $cls = 'bg-danger text-light';
                    }

                    $text = Arr::get(PROCESS_STATUS_LIST, $row->purchase_status);

                    $html = "<div class=\"text-center\"><span class=\"py-1 px-2 {$cls}\">{$text}<span></div>";

                    return new HtmlString($html);
                })
                ->escapeColumns()
                ->toJson();
        }

        $baseQuery = DB::table('users')
            ->selectRaw('
            users.id, null as photo,
            users.username, users.name, users.mitra_type, users.email, users.phone, 
            users.user_status as status,
            users.sub_domain, users.sub_domain as toko, 
            users.branch_id
            ')
            ->where('users.activated', '=', true)
            ->where('users.user_group', '=', USER_GROUP_MEMBER)
            ->where('users.user_type', '=', USER_TYPE_MITRA)
            ->where('users.referral_id', '=', $user->id)
            ->whereNotNull('users.mitra_type');

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as mitra"))->mergeBindings($baseQuery);

        return datatables()->query($query)
            ->editColumn('photo', function ($row) {
                $html = '<div class="d-block text-center align-middle border rounded-circle overflow-hidden bg-dark" style="width:50px; height:50px; --bs-bg-opacity:0.1;"><img alt="" src="%s" style="width:auto; height:100;"></div>';

                return new HtmlString(sprintf($html, ''));
            })
            ->editColumn('status', function ($row) {
                $clsBg = 'bg-success';
                $clsTxt = 'text-light';
                $statusName = 'Aktif';

                if ($row->status == USER_STATUS_BANNED) {
                    $statusName = 'BANNED';
                    $clsBg = 'bg-danger';
                } elseif ($row->status == USER_STATUS_NEED_ACTIVATE) {
                    $statusName = 'Belum Aktifasi';
                    $clsBg = 'bg-warning';
                    $clsTxt = 'text-dark';
                } elseif ($row->status == USER_STATUS_INACTIVE) {
                    $statusName = 'Tidak Aktif';
                    $clsBg = 'bg-light';
                    $clsTxt = 'text-dark';
                }

                return new HtmlString("<span class=\"py-1 px-2 {$clsBg} {$clsTxt}\">{$statusName}</span>");
            })
            ->editColumn('mitra_type', function ($row) {
                if (in_array($row->mitra_type, array_keys(MITRA_TYPES))) {
                    $clsBg = 'bg-light';
                    $clsTxt = 'text-dark';
                    $type = Arr::get(MITRA_TYPES, $row->mitra_type);

                    if ($row->mitra_type == MITRA_TYPE_AGENT) {
                        $clsBg = 'bg-primary';
                        $clsTxt = 'text-light';
                    }

                    return new HtmlString("<span class=\"py-1 px-2 {$clsBg} {$clsTxt}\">{$type}</span>");
                }

                return '';
            })
            ->addColumn('view', function ($row) {
                $routeView = route('member.directMitra.histories.index', ['mitra_id' => $row->id]);
                $buttonView = "<button type=\"button\" class=\"btn btn-sm btn-outline-info\" onclick=\"window.location.href='{$routeView}';\" title=\"Riwayat Belanja\"><i class=\"fa-solid fa-file-invoice\"></i></button>";

                return new HtmlString($buttonView);
            })
            ->escapeColumns()
            ->toJson();
    }

    public function mitraSelect2(Request $request)
    {
        $user = $request->user();
        $mitraId = intval($request->get('current'));
        $search = $request->get('term');

        $myMitra = DB::table('users')
            ->where('referral_id', '=', $user->id)
            ->where('user_group', '=', USER_GROUP_MEMBER)
            ->where('user_type', '=', USER_TYPE_MITRA);

        if (!empty($search)) {
            $myMitra = $myMitra->where('name', 'like', "%{$search}%");
        }

        if (!empty($mitraId)) {
            $myMitra = $myMitra->orderBy(DB::raw("(CASE WHEN id = {$mitraId} THEN 0 ELSE 1 END)"));
        }

        $myMitra = $myMitra->orderBy('name')->take(50)->get();

        $result = [];
        foreach ($myMitra as $row) {
            $result[] = [
                'id' => $row->id,
                'text' => $row->name,
            ];
        }

        return response()->json($result);
    }
}
