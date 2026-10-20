@extends('mail.layout')
@section('title', 'Verifikasi akses Anda')
@section('preheader', 'Gunakan kode verifikasi untuk melanjutkan masuk ke portal HSNCO.')
@section('content')
<p style="margin:0 0 24px;font-size:15px;line-height:1.8;">Gunakan kode berikut pada halaman verifikasi untuk melanjutkan masuk ke portal administrasi HSNCO.</p>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 24px;"><tr><td align="center" style="padding:24px 12px;background-color:#f5f2e9;border:1px solid #e5deca;">
<p style="margin:0 0 12px;font-size:11px;letter-spacing:2px;color:#78643e;">KODE VERIFIKASI</p>
<p style="margin:0;font-family:'Courier New',monospace;font-size:34px;font-weight:bold;letter-spacing:6px;color:#183e34;">{{ $code }}</p>
</td></tr></table>
<p style="margin:0 0 20px;font-size:14px;line-height:1.8;">Kode berlaku <strong>{{ $expires }} menit</strong>. Masukkan kode pada browser tempat Anda memulai login.</p>
<p style="margin:0 0 20px;padding:16px;border-left:3px solid #b99b62;background-color:#faf9f5;font-size:13px;line-height:1.8;">Tim HSNCO tidak akan meminta kode ini. Jangan membagikannya kepada siapa pun.</p>
<p style="margin:0;font-size:13px;line-height:1.8;color:#65716b;">Jika Anda tidak sedang mencoba masuk, abaikan email ini dan periksa keamanan akun Anda.</p>
@endsection