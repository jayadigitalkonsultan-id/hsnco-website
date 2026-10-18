@extends('layouts.auth')
@section('title','Reset kata sandi')
@section('intro','Masukkan email akun aktif Anda. Faktor kedua tetap diperlukan sesudah reset.')
@section('content')<form method="post" action="{{ route('password.email') }}" class="vstack gap-3">@csrf <div><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" required autofocus></div><button class="btn btn-dark">Kirim tautan reset</button></form>@endsection
