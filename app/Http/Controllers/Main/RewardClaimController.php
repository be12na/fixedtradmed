<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\MitraRewardClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class RewardClaimController extends Controller
{
    public function index(Request $request)
    {
        $currentStatus = session('filter.reward-status', -1);

        return view('main.bonuses.points.reward-index', [
            'windowTitle' => "Poin Reward",
            'breadcrumbs' => ['Poin', 'Reward'],
            'currentStatus' => $currentStatus,
        ]);
    }

    public function dataTable(Request $request)
    {
        $status = $request->get('reward_status', -1);
        $listStatus = [CLAIM_STATUS_FINISH, CLAIM_STATUS_PENDING];

        if (is_null($status) || !in_array($status, $listStatus)) $status = -1;

        $query = MitraRewardClaim::query()
            ->with(['user', 'reward']);

        if ($status == CLAIM_STATUS_FINISH) {
            $query = $query->onFinish();
        } elseif ($status == CLAIM_STATUS_PENDING) {
            $query = $query->onPending();
        } else {
            $query = $query->onStatus();
        }

        session([
            'filter.reward-status' => $status,
        ]);

        return datatables()->eloquent($query)
            ->editColumn('created_at', function ($row) {
                return formatFullDate($row->created_at);
            })
            ->addColumn('member_name', function ($row) {
                $format = '<div>%s</div><div class="small fst-italic">(%s)</div>';

                return new HtmlString(sprintf($format, $row->user->name, $row->user->username));
            })
            ->addColumn('reward_name', function ($row) {
                return $row->reward->reward;
            })
            ->addColumn('reward_status', function ($row) {
                $text = Arr::get(CLAIM_STATUS_LIST, $row->status);
                $style = $row->is_finish ? 'bg-success text-light' : 'bg-warning text-dark';
                $format = '<span class="py-1 px-2 small %s">%s</span>';

                return new HtmlString(sprintf($format, $style, $text));
            })
            ->addColumn('detail', function ($row) {
                $route = route('main.point.claim.detail', ['mitraRewardClaim' => $row->id]);
                $btnDetail = "<button type=\"button\" class=\"btn btn-sm btn-outline-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#modal-detail\" data-modal-url=\"{$route}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString($btnDetail);

                return '';
            })
            ->escapeColumns()
            ->toJson();
    }

    public function detail(Request $request)
    {
        $mitraRewardClaim = $request->mitraRewardClaim;

        return view('main.bonuses.points.reward-detail', [
            'mitraRewardClaim' => $mitraRewardClaim,
        ]);
    }

    public function confirm(Request $request)
    {
        $user = $request->user();
        $mitraRewardClaim = $request->confirmMitraRewardClaim;

        $mitraRewardClaim->update([
            'status' => CLAIM_STATUS_FINISH,
            'status_at' => now(),
            'status_by' => $user->id,
        ]);

        session([
            'message' => 'Klaim reward berhasil dikonfirmasi.',
            'messageClass' => 'success'
        ]);

        return response(route('main.point.claim.index'), 200);
    }
}
