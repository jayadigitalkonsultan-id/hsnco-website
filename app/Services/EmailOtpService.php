<?php

namespace App\Services;

use App\Enums\TwoFactorMethod;
use App\Enums\UserStatus;
use App\Mail\EmailOtpMail;
use App\Models\TwoFactorChallenge;
use App\Models\TwoFactorRecoveryCode;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class EmailOtpService
{
    public function send(User $user, Request $request, string $purpose = 'login'): bool
    {
        $cooldown = 'otp:resend:'.$user->id.':'.hash('sha256', $request->session()->getId());
        if (RateLimiter::tooManyAttempts($cooldown, 1)) return false;
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $binding = hash('sha256', $request->session()->getId());
        $challenge = DB::transaction(function () use ($user, $purpose, $binding, $code) {
            TwoFactorChallenge::query()->where('user_id', $user->id)->where('purpose', $purpose)->whereNull('consumed_at')->lockForUpdate()->update(['consumed_at' => now()]);
            return TwoFactorChallenge::query()->create(['user_id' => $user->id, 'purpose' => $purpose, 'method' => TwoFactorMethod::Email, 'session_binding_hash' => $binding, 'code_hash' => Hash::make($code), 'expires_at' => now()->addMinutes(config('security.otp.expires_minutes'))]);
        });
        RateLimiter::hit($cooldown, config('security.otp.resend_seconds'));
        try {
            Mail::to($user->email)->send(new EmailOtpMail($user, $code));
            app(SecurityAudit::class)->record('challenge_sent', 'success', $user, $request, null, ['purpose' => $purpose]);
            return true;
        } catch (\Throwable $e) {
            $challenge->forceFill(['consumed_at' => now()])->save();
            report($e); app(SecurityAudit::class)->record('challenge_sent', 'failed', $user, $request, 'mail_delivery_failed');
            return false;
        }
    }

    /** @return array{ok:bool,recovery_codes:array<int,string>} */
    public function verify(User $user, Request $request, string $code, string $purpose = 'login'): array
    {
        $key = 'otp:verify:'.$user->id.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, config('security.otp.verify_per_minute'))) return ['ok' => false, 'recovery_codes' => []];
        RateLimiter::hit($key, 60);
        return DB::transaction(function () use ($user, $request, $code, $purpose) {
            $approved = $purpose === 'enrollment' ? \App\Models\AllowedActivationEmail::approved($user->email) : null;
            if ($purpose === 'enrollment' && !$approved) return ['ok' => false, 'recovery_codes' => []];
            $challenge = TwoFactorChallenge::query()->where('user_id', $user->id)->where('purpose', $purpose)->where('session_binding_hash', hash('sha256', $request->session()->getId()))->whereNull('consumed_at')->latest('created_at')->lockForUpdate()->first();
            if (! $challenge || $challenge->expires_at->isPast() || $challenge->attempts >= config('security.otp.max_attempts') || ! Hash::check($code, (string) $challenge->code_hash)) {
                if ($challenge) { $challenge->increment('attempts'); if ($challenge->attempts + 1 >= config('security.otp.max_attempts')) $challenge->forceFill(['consumed_at' => now()])->save(); }
                app(SecurityAudit::class)->record('challenge_failed', 'failed', $user, $request, 'invalid_or_expired');
                return ['ok' => false, 'recovery_codes' => []];
            }
            $challenge->forceFill(['consumed_at' => now()])->save();
            $factor = UserTwoFactorAuthentication::query()->firstOrNew(['user_id' => $user->id]);
            $firstEnrollment = ! $factor->confirmed_at;
            $factor->forceFill(['method' => TwoFactorMethod::Email, 'secret' => null, 'confirmed_at' => now()])->save();
            if ($user->status === UserStatus::PendingTwoFactor) $user->forceFill(['status' => UserStatus::Active, 'role' => $approved->role, 'activated_at' => now()])->save();
            $codes = $firstEnrollment ? $this->issueRecoveryCodes($user) : [];
            app(SecurityAudit::class)->record('challenge_passed', 'success', $user, $request, null, ['purpose' => $purpose]);
            return ['ok' => true, 'recovery_codes' => $codes];
        });
    }

    private function issueRecoveryCodes(User $user): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) { $code = strtoupper(bin2hex(random_bytes(4))); $codes[] = $code; TwoFactorRecoveryCode::query()->create(['user_id' => $user->id, 'code_hash' => Hash::make($code)]); }
        return $codes;
    }
}
