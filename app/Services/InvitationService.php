<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Mail\AccountInvitationMail;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    public function invite(User $actor, string $email, string $role): User
    {
        return DB::transaction(function () use ($actor, $email, $role) {
            $user = User::query()->create(['email' => $email]);
            $user->forceFill(['role' => $role, 'status' => UserStatus::Invited, 'invited_by' => $actor->id])->save();
            UserInvitation::query()->create(['user_id' => $user->id, 'email' => $user->email, 'invited_by' => $actor->id]);
            app(SecurityAudit::class)->record('invitation_created', 'success', $actor, null, null, ['subject_user_id' => $user->id]);
            return $user;
        });
    }

    /** @return bool true only if a new link was sent */
    public function sendForEmail(string $email): bool
    {
        $invitation = UserInvitation::query()->with('user')->where('email', $email)->first();
        if (! $invitation || ! $invitation->user || $invitation->user->status !== UserStatus::Invited || $invitation->revoked_at || $invitation->accepted_at) return false;
        if ($invitation->last_sent_at?->gt(now()->subSeconds(config('security.invitation.resend_seconds')))) return false;

        $plain = Str::random(64);
        DB::transaction(function () use ($invitation, $plain) {
            $locked = UserInvitation::query()->lockForUpdate()->findOrFail($invitation->id);
            $locked->forceFill([
                'token_hash' => hash('sha256', $plain), 'issued_at' => now(),
                'expires_at' => now()->addMinutes(config('security.invitation.expires_minutes')), 'last_sent_at' => now(),
            ])->save();
        });
        try {
            Mail::to($invitation->email)->send(new AccountInvitationMail($invitation->user, $plain));
            app(SecurityAudit::class)->record('invitation_sent', 'success', $invitation->user);
            return true;
        } catch (\Throwable $e) {
            report($e);
            app(SecurityAudit::class)->record('invitation_sent', 'failed', $invitation->user, null, 'mail_delivery_failed');
            return false;
        }
    }

    public function consume(string $token, string $email, string $name, string $password): ?User
    {
        return DB::transaction(function () use ($token, $email, $name, $password) {
            $invitation = UserInvitation::query()->with('user')->where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            if (! $invitation || ! hash_equals((string) $invitation->email, $email) || ! $invitation->user || $invitation->user->status !== UserStatus::Invited || $invitation->revoked_at || $invitation->accepted_at || $invitation->expires_at?->isPast()) return null;
            $user = $invitation->user;
            $user->forceFill(['name' => $name, 'password' => $password, 'email_verified_at' => now(), 'password_changed_at' => now(), 'status' => UserStatus::PendingTwoFactor])->save();
            $invitation->forceFill(['accepted_at' => now(), 'token_hash' => null])->save();
            app(SecurityAudit::class)->record('invitation_accepted', 'success', $user);
            return $user;
        });
    }
}
