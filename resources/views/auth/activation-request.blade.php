@extends('layouts.auth')
@section('title','Aktivasi akun')
@section('intro','Masukkan email yang telah didaftarkan. Jika memenuhi syarat, kami mengirim tautan untuk membuat kata sandi.')
@section('content')
<form method="post" action="{{ route('activation.send') }}" class="vstack gap-3">@csrf <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div><button class="btn btn-dark" type="submit">Kirim tautan aktivasi</button></form><p class="mt-4 mb-0"><a href="{{ route('login') }}">Kembali ke masuk</a></p>
@endsection
