<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisterNotification extends Notification
{
    use Queueable;

    private User|null $user;
    private string $usingVia;
    private array $options;

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

        if ($this->usingVia == 'onesender') $this->setWhatsappContent();
    }

    private function setWhatsappContent(): void
    {
        $user = $this->user;
        $referral = $user->referral;
        $appName = config('app.name');

        $contents = [
            "*Informasi Pendaftaran Member {$appName}*",
            "",
            "Selamat datang *{$user->name}*",
            "Terima kasih sudah bergabung dengan *{$appName}*. Berikut adalah informasi data yang anda gunakan untuk mendaftar:",
            "",
            "*Data Personal*",
            "Nama Lengkap : {$user->name}",
            "Email : {$user->email}",
            "Handphone : {$user->phone}",
            "Username : {$user->username}",
            "Silahkan login di Member Area https://affiliate.tradmed.id/",
        ];

        if ($referral) {
            $contents = array_merge($contents, [
                "",
                "*Data Referral*",
                "Nama Lengkap : {$referral->name} ({$referral->username})",
                "Handphone : {$referral->phone}",
                "",
                "*Salam Sukses {$appName}*",
            ]);
        }

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
            ->subject('Informasi Pendaftaran')
            ->view('email.register-mail', [
                'user' => $this->user
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
