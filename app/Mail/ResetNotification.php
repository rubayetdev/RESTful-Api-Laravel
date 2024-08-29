<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $username;

    /**
     * Create a new message instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token, $username)
    {
        $this->token = $token;
        $this->username = $username;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return
            $this->view('email')
        ->subject('Reset Password Notification')
            ->with([
                'token' => $this->token,
                'username' => $this->username,
            ]);
    }
}
