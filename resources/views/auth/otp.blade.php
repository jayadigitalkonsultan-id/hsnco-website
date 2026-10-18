@extends('layouts.auth')
@section('title','Verifikasi OTP email')
@section('intro','Kami mengirim kode enam digit ke '.$email.'. Kode berlaku '.config('security.otp.expires_minutes').' menit.')
@section('content')
<form method="post" action="{{ route('two-factor.verify') }}" class="vstack gap-3">@csrf <div><label class="form-label" for="code">Kode OTP</label><input class="form-control" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" maxlength="6" required autofocus></div><button class="btn btn-dark" type="submit">Verifikasi</button></form><form method="post" action="{{ route('two-factor.resend') }}" class="mt-3">@csrf <button class="btn btn-link p-0" type="submit">Kirim ulang kode</button></form><form method="post" action="{{ route('logout') }}" class="mt-3">@csrf <button class="btn btn-link p-0 text-danger" type="submit">Keluar</button></form>
@endsection
