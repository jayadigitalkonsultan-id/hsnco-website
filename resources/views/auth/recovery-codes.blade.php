@extends('layouts.auth')
@section('title','Simpan recovery code')
@section('intro','Kode ini hanya ditampilkan sekali. Simpan di tempat aman untuk pemulihan faktor kedua.')
@section('content')
<ul class="list-group mb-3">@foreach($codes as $code)<li class="list-group-item font-monospace">{{ $code }}</li>@endforeach</ul><a class="btn btn-dark" href="{{ route('admin.dashboard') }}">Lanjut ke dashboard</a>
@endsection
