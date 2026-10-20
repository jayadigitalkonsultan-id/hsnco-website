@extends('mail.layout')
@section('title', 'Aktifkan akun Anda')
@section('preheader', 'Buat kata sandi untuk mengakses portal administrasi HSNCO.')
@section('content')
<p style="margin:0 0 16px;font-size:15px;line-height:1.8;">Halo,</p>
<p style="margin:0 0 24px;font-size:15px;line-height:1.8;">Kami menerima permintaan aktivasi untuk <strong>{{ $user->email }}</strong>. Buat kata sandi Anda untuk melanjutkan ke portal administrasi HSNCO.</p>
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 24px;"><tr><td bgcolor="#183e34" style="border-radius:4px;text-align:center;mso-padding-alt:16px 26px;"><a href="{{ $url }}" style="display:inline-block;padding:16px 26px;border:1px solid #183e34;border-radius:4px;color:#ffffff;font-size:15px;line-height:1.4;font-weight:bold;text-decoration:none;">Buat kata sandi akun</a></td></tr></table>
<p style="margin:0 0 24px;padding:16px;background-color:#f5f2e9;border-left:3px solid #b99b62;font-size:13px;line-height:1.8;">Tautan berlaku <strong>{{ $expires }} menit</strong> dan hanya dapat digunakan sekali.</p>
<p style="margin:0 0 12px;font-size:14px;font-weight:bold;">Langkah berikutnya</p>
<p style="margin:0 0 24px;font-size:14px;line-height:1.9;">1. Buat dan simpan kata sandi Anda.<br>2. Masuk menggunakan email dan kata sandi.<br>3. Masukkan kode OTP yang dikirim ke email ini.</p>
<p style="margin:0 0 10px;font-size:12px;line-height:1.8;color:#65716b;">Jika tombol tidak dapat dibuka, salin tautan berikut ke browser Anda:</p>
<p style="margin:0 0 24px;font-size:12px;line-height:1.8;word-break:break-all;overflow-wrap:anywhere;"><a href="{{ $url }}" style="color:#245d4e;text-decoration:underline;">{{ $url }}</a></p>
<p style="margin:0;font-size:13px;line-height:1.8;color:#65716b;">Tidak meminta aktivasi? Abaikan email ini. Akun tidak akan diaktifkan tanpa menyelesaikan verifikasi.</p>
@endsection