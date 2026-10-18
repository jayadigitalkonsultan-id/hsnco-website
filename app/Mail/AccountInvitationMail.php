<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountInvitationMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public User $user, public string $token) {}
    public function build(): self { return $this->subject('Aktivasi akun HSNCO')->view('mail.invitation', ['url' => route('activation.edit', ['token'=>$this->token]), 'expires' => config('security.invitation.expires_minutes')]); }
}
