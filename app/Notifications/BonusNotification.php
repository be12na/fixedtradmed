<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\UserBonus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BonusNotification extends Notification
{
    use Queueable;

    private User $user;
    private string $usingVia;
    private array $options;
    private int $bonus;
    private string $bonusAt;
    private string $bonusType;

    public $content;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, string $usingVia, array $options = null)
    {
        $this->user = $user;
        $this->usingVia = strtolower($usingVia);
        $this->options = !is_null($options) ? $options : [];
        $this->setVarBonus();

        if ($this->usingVia == 'onesender') $this->setWhatsappContent();
    }

    private function setVarBonus(): void
    {
        $this->bonus = 0;
        $this->bonusAt = '';
        $this->bonusType = '';

        if (array_key_exists('id', $this->options)) {
            $userBonus = UserBonus::query()->byId($this->options['id'])->first();

            if (!empty($userBonus)) {
                $this->bonus = $userBonus->bonus_amount;
                $this->bonusAt = formatDatetime($userBonus->created_at, __('format.date.medium') . ', H:i');
                $this->bonusType = $userBonus->bonus_type_name;
            }
        }
    }

    private function setWhatsappContent(): void
    {
        $user = $this->user;
        $appName = config('app.name');
        $bonus = formatNumber($this->bonus);
        $bonusAt = $this->bonusAt;
        $bonusType = $this->bonusType;

        $contents = [
            "*{$appName}*",
            "{$bonusAt}",
            "",
            "Selamat, sahabat {$appName} / {$user->name}",
            "Anda mendapatkan Bonus {$bonusType} sebesar:",
            "Rp *{$bonus}*,-",
            "",
            "Semoga menjadi rejeki yang berkah, besar, bermanfaat bagi Anda, Keluarga, dan Banyak Orang.",
            "",
            "Terima Kasih",
            "",
            "Mohon untuk tidak membalas pesan ini, pesan otomatis",
        ];

        $this->content = implode("\r\n", $contents);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [$this->usingVia];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Informasi Bonus ' . $this->bonusType)
            ->view('email.bonus-mail', [
                'user' => $this->user,
                'bonus' => $this->bonus,
                'bonusType' => $this->bonusType,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->options;
    }
}
