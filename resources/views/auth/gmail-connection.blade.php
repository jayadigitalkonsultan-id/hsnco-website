@extends('layouts.auth')
@section('title', $connected ? 'Gmail terhubung' : 'Koneksi Gmail gagal')
@section('intro', $message)
@section('content')
<p class="auth-access-note">Koneksi ini hanya digunakan untuk pengiriman email website. Proses ini tidak memasukkan Anda ke akun admin HSNCO.</p>
<a class="btn btn-dark w-100 mt-3" href="{{ route('login') }}">Kembali ke halaman masuk</a>
@endsection