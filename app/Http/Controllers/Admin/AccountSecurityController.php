<?php

namespace App\Http\Controllers\Admin;

use App\Services\SecurityAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AccountSecurityController extends AdminController
{
    public function show(Request $request)
    {
        $user = $request->user();
        Gate::authorize('updatePreferences', $user);
        $factor = $user->twoFactorAuthentication;
        $remainingCodes = $user->recoveryCodes()->whereNull('used_at')->count();
        $newCodes = $request->session()->pull('account.new_recovery_codes', []);
        return view('admin.account-security', compact('factor', 'remainingCodes', 'newCodes'));
    }

    public function rotate(Request $request)
    {
        $user = $request->user();
        Gate::authorize('updatePreferences', $user);
        abort_unless($user->twoFactorAuthentication?->confirmed_at, 403, 'Autentikasi dua faktor belum aktif.');
        $codes = [];
        DB::transaction(function () use ($user, &$codes) {
            $user->recoveryCodes()->delete();
            for ($i = 0; $i < 8; $i++) {
                $code = strtoupper(bin2hex(random_bytes(4)));
                $codes[] = $code;
                $user->recoveryCodes()->create(['code_hash' => Hash::make($code)]);
            }
        });
        app(SecurityAudit::class)->record('recovery_codes_rotated', 'success', $user, $request, null, ['count' => count($codes)]);
        $request->session()->flash('account.new_recovery_codes', $codes);
        return redirect()->route('admin.account.security')->with('success', 'Recovery code lama dicabut. Simpan code baru sekarang; code hanya ditampilkan sekali.');
    }
}
