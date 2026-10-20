@extends('layouts.auth')
@section('title', 'Masuk ke ruang admin')
@section('intro', 'Gunakan email dan kata sandi akun yang telah didaftarkan oleh administrator.')
@section('content')
<form method="post" action="{{ route('login.store') }}" class="auth-login-form">
    @csrf
    <div class="mb-4">
        <label class="form-label" for="email">Alamat email</label>
        <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" placeholder="Masukkan alamat email Anda" required autofocus>
    </div>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-2"><label class="form-label mb-0" for="password">Kata sandi</label><a class="auth-inline-link" href="{{ route('password.request') }}">Lupa kata sandi?</a></div>
        <div class="auth-password-wrap"><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required><button class="auth-password-toggle" type="button" data-password-toggle aria-controls="password" aria-pressed="false" hidden>Tampilkan</button></div>
    </div>
    <button class="btn btn-dark auth-submit w-100" type="submit">Masuk ke ruang admin <span aria-hidden="true">→</span></button>
</form>
<div class="auth-activation"><span>Baru menerima akses akun?</span> <a href="{{ route('activation.create') }}">Aktivasi akun Anda <span aria-hidden="true">↗</span></a></div>
<p class="auth-access-note">Gunakan akun pribadi Anda. Jangan membagikan kata sandi atau kode verifikasi kepada siapa pun.</p>
@endsection
