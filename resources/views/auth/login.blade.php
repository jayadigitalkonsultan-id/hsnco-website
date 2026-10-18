@extends('layouts.auth')
@section('title','Masuk')
@section('intro','Masuk dengan email yang telah didaftarkan oleh Super Administrator.')
@section('content')
<form method="post" action="{{ route('login.store') }}" class="vstack gap-3">@csrf <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div><div><label class="form-label" for="password">Kata sandi</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div><button class="btn btn-dark" type="submit">Lanjutkan</button></form><p class="mt-4 mb-0"><a href="{{ route('activation.create') }}">Aktivasi akun</a> · <a href="{{ route('password.request') }}">Lupa kata sandi</a></p>
@endsection
