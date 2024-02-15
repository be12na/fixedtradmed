<?php

namespace App\Console\Commands;

use App\Models\MitraPurchase;
use App\Models\UserBonus;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBonusPrestasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-bonus-prestasi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset dan fix Bonus Level Prestasi';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $purchases = MitraPurchase::query()->byApproved()->get();

        DB::beginTransaction();
        try {
            UserBonus::query()->byType(BONUS_MITRA_PRESTASI)->delete();

            foreach ($purchases as $mitraPurchase) {
                $bonusDate = Carbon::createFromTimeString($mitraPurchase->getRawOriginal('status_at'));
                UserBonus::createBonusLevel($mitraPurchase, BONUS_MITRA_LEVEL_PRESTASI, $bonusDate);
            }

            DB::commit();

            $this->info('Fix bonus prestasi selesai');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }
}
