@extends('layouts.auth')
@section('title','Buat kata sandi')
@section('intro','Buat kata sandi untuk email yang menerima tautan aktivasi ini. Setelah selesai, Anda dapat masuk melalui halaman login.')
@section('content')
<form method="post" action="{{ route('activation.update',$token) }}" class="vstack gap-3">@csrf <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ $email }}" autocomplete="email" readonly required></div><div><label class="form-label" for="name">Nama</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required></div><div><label class="form-label" for="password">Kata sandi</label><input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required></div><div><label class="form-label" for="password_confirmation">Konfirmasi kata sandi</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div><button class="btn btn-dark" type="submit">Simpan kata sandi dan masuk</button></form>
@endsection
