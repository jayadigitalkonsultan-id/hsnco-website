@extends('layouts.admin')
@section('title', 'Keamanan akun')
@section('intro', 'Lihat status autentikasi dua faktor dan kelola recovery code untuk akun Anda sendiri.')
@section('content')
<section class="panel"><div class="panel-heading"><div><h2>Autentikasi dua faktor</h2><p>Metode: {{ $factor?->method?->value ?? 'Belum aktif' }}. Status: {{ $factor?->confirmed_at ? 'Aktif' : 'Belum dikonfirmasi' }}.</p></div></div><div class="editor-body"><p>Recovery code tersisa: <strong>{{ $remainingCodes }}</strong></p>@if($factor?->confirmed_at)<form method="post" action="{{ route('admin.account.security.rotate') }}">@csrf<button class="btn btn-outline-danger">Cabut dan buat recovery code baru</button></form>@else<p class="text-muted">Hubungi Super Administrator untuk mengaktifkan autentikasi dua faktor pada akun ini.</p>@endif</div></section>
@if($newCodes)<section class="panel mt-4"><div class="panel-heading"><div><h2>Recovery code baru</h2><p>Salin dan simpan di tempat aman. Daftar ini tidak akan ditampilkan kembali.</p></div></div><div class="editor-body"><ul class="font-monospace">@foreach($newCodes as $code)<li>{{ $code }}</li>@endforeach</ul></div></section>@endif
@endsection
