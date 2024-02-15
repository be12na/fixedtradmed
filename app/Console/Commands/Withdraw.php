<?php

namespace App\Console\Commands;

use App\Models\UserBonus;
use App\Models\UserWithdraw;
// use App\Notifications\SummaryBonus;
use Carbon\Carbon;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as rowCollection;
use Illuminate\Support\Facades\DB;

class Withdraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:withdraw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Withdraw available bonus to withdraw';

    private array $existsCode = [];
    private rowCollection $usersGetWD;
    private int $minWithdraw = 100000;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->usersGetWD = collect();
        $this->existsCode = UserWithdraw::query()->get('wd_code')->pluck('wd_code')->toArray();

        $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_SPONSOR), 'Bonus Sponsor');
        // $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_RO), 'Bonus Sponsor RO');
        $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_CASHBACK_RO), 'Bonus Cashback RO');
        $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_POINT_RO), 'Bonus Point RO');
        // $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_CASHBACK_ACTIVATION), 'Bonus Cashback Sponsoring');
        // $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_GENERASI), 'Bonus Titik Generasi');
        $this->setWithdrawBonus($this->queryBonuses(BONUS_MITRA_PRESTASI), 'Bonus Prestasi');
        ///////////////////////////////////////////////

        // $this->sendNotificationsWithdraw();
    }

    private function queryBonuses(array|int $type, Closure $userConditions = null, string $userWith = null): Collection
    {
        if (!is_array($type)) $type = [$type];

        return UserBonus::query()->byType($type)
            ->forWithdraw(operator: '<=', userConditions: $userConditions)
            ->with('user', function ($user) use ($userWith) {
                if (!empty($userWith)) $user = $user->with($userWith);

                return $user->with('memberActiveBank');
            })->get();
    }

    private function setWithdrawBonus(Collection $bonuses, string $title): void
    {
        if ($bonuses->isNotEmpty()) {
            $dateCarbon = Carbon::now();
            $wdDate = $dateCarbon->format('Y-m-d');
            $withdrawCreated = false;

            DB::beginTransaction();

            try {
                // group by user
                foreach ($bonuses->groupBy('user_id') as $userId => $bonusesByUser) {
                    $user = $bonusesByUser->first()->user;
                    $userBank = $user->memberActiveBank;
                    $isUpgraded = $user->has_package;
                    $hasRO = $user->has_repeat_order;

                    if (empty($this->usersGetWD->where('id', '=', $user->id)->first())) {
                        $this->usersGetWD->push($user);
                    }
                    // group by bonus type
                    foreach ($bonusesByUser->groupBy('bonus_type') as $bonusType => $bonusesByType) {
                        $bonusan = $bonusesByType;

                        if (!$isUpgraded) {
                            $bonusan = $bonusan->where('should_upgrade', '=', false)->values();
                        }

                        if (!$hasRO) {
                            $bonusan = $bonusan->where('should_ro', '=', false)->values();
                        }

                        $bonusIds = $bonusan->pluck('id')->toArray();
                        $totalBonus = $bonusan->sum('bonus_amount');

                        if ($totalBonus < $this->minWithdraw) continue;

                        $fee = WD_FEE;
                        $totalTransfer = $totalBonus - $fee;
                        $wdCode = UserWithdraw::makeCode($bonusType, $dateCarbon, $this->existsCode);

                        $wdValues = [
                            'wd_code' => $wdCode,
                            'user_id' => $userId,
                            'wd_date' => $wdDate,
                            'bank_code' => $userBank->bank_code,
                            'bank_name' => $userBank->bank_name,
                            'bank_acc_no' => $userBank->account_no,
                            'bank_acc_name' => $userBank->account_name,
                            'wd_bonus_type' => $bonusType,
                            'total_bonus' => $totalBonus,
                            'fee' => $fee,
                            'total_transfer' => $totalTransfer,
                            'status' => CLAIM_STATUS_PENDING,
                        ];

                        $wd = UserWithdraw::create($wdValues);
                        $bonusValues = ['wd_id' => $wd->id];

                        UserBonus::query()->byId($bonusIds)->update($bonusValues);

                        $this->existsCode[] = $wdCode;
                        $withdrawCreated = true;
                    }
                }

                DB::commit();

                if ($withdrawCreated) {
                    $this->info("Withdraw {$title} berhasil");
                } else {
                    $this->warn("Tidak ada {$title} yang dapat ditarik.");
                }
            } catch (\Exception $e) {
                DB::rollBack();

                $this->error("Withdraw {$title} error:" . $e->getMessage());
            }
        } else {
            $this->warn("Tidak ada {$title} yang dapat ditarik.");
        }
    }

    // private function sendNotificationsWithdraw(): void
    // {
    //     if ($this->usersGetWD->isNotEmpty()) {
    //         foreach ($this->usersGetWD as $user) {
    //             $user->notify(new SummaryBonus($user, 'database', 'mail'));
    //             $user->notify(new SummaryBonus($user, 'database', 'whatsapp'));
    //         }
    //     }
    // }
}
