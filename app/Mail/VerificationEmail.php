<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationToken;

    public function __construct(User $user, $verificationToken)
    {
        $this->user = $user;
        $this->verificationToken = $verificationToken;
    }

    public function build()
    {
        $verificationUrl = config('app.frontend_url') . '/verify-email?token=' . $this->verificationToken;

        return $this->subject('Verify Your Email - Waisaka Property')
                    ->view('emails.verification')
                    ->with([
                        'userName' => $this->user->nama,
                        'verificationUrl' => $verificationUrl
                    ]);
    }
}