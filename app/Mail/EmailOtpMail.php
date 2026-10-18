<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public User $user, public string $code) {}
    public function build(): self { return $this->subject('Kode verifikasi HSNCO')->view('mail.otp', ['code'=>$this->code, 'expires'=>config('security.otp.expires_minutes')]); }
}
