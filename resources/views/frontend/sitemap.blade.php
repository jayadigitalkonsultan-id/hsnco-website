@extends('layouts.frontend')
@section('title')
Peta Situs — HSNCO
@endsection
@section('description')
Jelajahi halaman situs HSNCO melalui peta situs visual yang mudah diakses.
@endsection
@section('content')
<x-frontend.page-banner current="Peta Situs" eyebrow="Jelajahi HSNCO"><x-slot:heading>Jalur yang jelas.<br><em>Setiap halaman.</em></x-slot:heading><x-slot:intro>Jelajahi informasi firma, bidang praktik, direktori profesional, dan pratinjau publikasi.</x-slot:intro></x-frontend.page-banner><section class="section-space surface-paper"><div class="container"><div class="sitemap-panel"><div class="row g-0"><div class="col-md-6 sitemap-tree-column"><label class="form-label" for="siteTreeSearch">Cari halaman</label><input class="form-control" type="search" id="siteTreeSearch" placeholder="Cari halaman situs"><div class="tree-tools"><button type="button" id="expandSiteTree">Buka semua</button><button type="button" id="collapseSiteTree">Tutup semua</button></div><div id="siteTree"></div><ul class="sitemap-fallback" id="siteTreeFallback"><li><a data-site-page href="{{ route('home') }}">Beranda</a></li><li><a data-site-page href="{{ route('about') }}">Tentang Firma</a></li><li><a data-site-page href="{{ route('practice-areas') }}">Bidang Praktik</a></li><li><a data-site-page href="{{ route('team') }}">Profesional</a></li><li><a data-site-page href="{{ route('insights') }}">Publikasi</a></li><li><a data-site-page href="{{ route('contact') }}">Kontak</a></li></ul></div><div class="col-md-6"><div class="sitemap-detail"><p class="eyebrow">Tujuan Anda berikutnya</p><h2 id="sitePageTitle">Jelajahi HSNCO</h2><p>Pilih halaman untuk melanjutkan kunjungan. Direktori halaman juga dapat diakses dengan papan ketik.</p><a class="text-link" id="openSitePage" href="{{ route('home') }}" hidden>Buka halaman terpilih <span aria-hidden="true">→</span></a></div></div></div></div></div></section>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/jstree/themes/default/style.min.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/plugins.css') }}">
@endpush
@push('scripts')
<script defer src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script><script defer src="{{ asset('assets/vendor/jstree/jstree.min.js') }}"></script><script defer src="{{ asset('assets/plugins-init/jstree-init.js') }}"></script>
@endpush
