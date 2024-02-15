<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificationSender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:send-notification {driver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications that have been recorded in the database based on the registered drivers';

    private int $limit = 10;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->sendNow();
    }

    private function sendNow()
    {
        $driver = strtolower($this->argument('driver'));
        if ($driver == 'whatsapp') $driver = 'onesender';

        $notifications = DB::table('notifications')->whereJsonContains('data->driver', $driver)
            ->orderBy('created_at')
            ->limit($this->limit)
            ->get();

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);
            $recipientClass = $notification->notifiable_type;
            $notificationClass = $notification->type;
            $recipient = $recipientClass::query()->byId($notification->notifiable_id)->first();

            if (!empty($recipient)) {
                try {
                    $recipient->notify(new $notificationClass($recipient, $data['driver'], $data));

                    $this->sendSuccess($notification->id);
                } catch (\Exception $e) {
                    $this->sendError($notification->id, $data, $e->getMessage());
                }
            }
        }
    }

    private function sendSuccess($notificationId): void
    {
        DB::table('notifications')->where('id', '=', $notificationId)->delete();
    }

    private function sendError($notificationId, array $data, $message): void
    {
        $data['error'] = $message;

        $values = [
            'data' => json_encode($data),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        DB::table('notifications')->where('id', '=', $notificationId)->update($values);
    }
}
