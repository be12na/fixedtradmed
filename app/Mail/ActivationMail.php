<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;
    private $asUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $asUser)
    {
        $this->user = $user;
        $this->asUser = $asUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.activation')
            ->with([
                'data' => $this->user, 'asUser' => $this->asUser
            ]);
    }
}
