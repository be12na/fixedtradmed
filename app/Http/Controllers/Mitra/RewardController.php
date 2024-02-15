<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\MitraReward;
use App\Models\MitraRewardClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $rewards = MitraReward::query()->byActive(true)->orderBy('point')->get();

        return view('mitra.points.reward-index', [
            'windowTitle' => "Poin Reward",
            'breadcrumbs' => ['Poin', 'Reward'],
            'rewards' => $rewards,
        ]);
    }

    public function claim(Request $request)
    {
        $mitraReward = MitraReward::query()->byPoint(intval($request->point))->byActive()->first();

        if (empty($mitraReward)) return response('Reward tidak sudah tersedia', 404);

        $user = $request->user();

        if (!$user->canClaimReward($mitraReward)) {
            return response('Reward tidak dapat diambil', 403);
        }

        if (!$request->isMethod('POST')) {
            return view('mitra.points.reward-claim', [
                'mitraReward' => $mitraReward
            ]);
        }

        $responCode = 200;
        $responText = route('mitra.point.reward.index');

        DB::beginTransaction();
        try {
            MitraRewardClaim::create([
                'user_id' => $user->id,
                'reward_id' => $mitraReward->id,
            ]);

            DB::commit();

            $point = formatNumber($mitraReward->point, 0);

            session([
                'message' => "Reward: {$mitraReward->reward} ({$point} poin) berhasil diajukan.",
                'messageClass' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $moreMessage = isLive() ? '' : $e->getMessage();

            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi. {$moreMessage}",
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }
}
