@extends('layouts.frontend')
@php
    $banner = $sitemapSections->get('banner');
    $directory = $sitemapSections->get('directory');
    $showBanner = ! $banner || $banner->is_visible;
    $banner = $banner?->is_visible ? $banner : null;
    $directory = $directory?->is_visible ? $directory : null;
    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];
    $intro = $bannerContent['description'] ?? 'Temukan informasi firma, bidang praktik, profesional, publikasi, dan cara menghubungi HSNCO.';
@endphp
@section('title')
{{ $banner?->heading ?? 'Peta Situs' }} — HSNCO
@endsection
@section('description')
{{ \Illuminate\Support\Str::limit(strip_tags($intro), 160) }}
@endsection
@section('content')
@if ($showBanner)
<x-frontend.page-banner current="Peta Situs" :eyebrow="$bannerContent['eyebrow'] ?? 'Jelajahi HSNCO'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Jalur yang jelas. Setiap halaman.')) !!}</x-slot:heading><x-slot:intro>{!! app(\App\Services\Cms\RichTextSanitizer::class)->sanitize($intro) !!}</x-slot:intro></x-frontend.page-banner>
@endif

<section class="section-space surface-paper" aria-labelledby="sitemapDirectoryHeading">
    <div class="container">
        <div class="row g-4 align-items-end mb-4">
            <div class="col-lg-7">
                <p class="eyebrow">{{ $directoryContent['eyebrow'] ?? 'Direktori halaman publik' }}</p>
                <x-frontend.section-heading>{!! nl2br(e($directory?->heading ?? 'Jelajahi HSNCO')) !!}</x-frontend.section-heading>
            </div>
            <div class="col-lg-5"><x-frontend.rich-text :html="$directoryContent['description'] ?? 'Cari halaman dan tautan publik yang tersedia di situs HSNCO.'" /></div>
        </div>
        <div class="sitemap-panel">
            <div class="row g-0">
                <div class="col-md-6 sitemap-tree-column">
                    <div id="siteTreeControls" hidden>
                        <label class="form-label" for="siteTreeSearch">Cari halaman</label>
                        <input class="form-control" type="search" id="siteTreeSearch" placeholder="Cari halaman situs" autocomplete="off">
                        <div class="tree-tools">
                            <button type="button" id="expandSiteTree">Buka semua</button>
                            <button type="button" id="collapseSiteTree">Tutup semua</button>
                        </div>
                    </div>
                    <div id="siteTree" aria-label="Struktur halaman situs" hidden></div>
                    <nav aria-label="Daftar halaman publik">
                        <ul class="sitemap-fallback" id="siteTreeFallback">
                            @foreach ($sitemapTreeData as $item)
                                <li>
                                    <a data-site-page href="{{ $item['href'] }}" data-description="{{ $item['description'] }}">{{ $item['text'] }}</a>
                                    @if (! empty($item['children']))
                                        <ul>
                                            @foreach ($item['children'] as $child)
                                                <li><a data-site-page href="{{ $child['href'] }}" data-description="{{ $child['description'] }}">{{ $child['text'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
                <div class="col-md-6">
                    <div class="sitemap-detail" aria-live="polite">
                        <p class="eyebrow">Tujuan Anda berikutnya</p>
                        <h2 id="sitePageTitle">Jelajahi HSNCO</h2>
                        <p id="sitePageDescription">Pilih halaman dari daftar untuk membaca informasi tujuan. Tanpa JavaScript, tautan langsung tetap tersedia.</p>
                        <a class="text-link" id="openSitePage" href="{{ route('home') }}" hidden>Buka halaman terpilih <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/jstree/themes/default/style.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/plugins.css') }}">
@endpush
@push('scripts')
<script defer src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script defer src="{{ asset('assets/vendor/jstree/jstree.min.js') }}"></script>
<script defer src="{{ asset('assets/plugins-init/jstree-init.js') }}"></script>
@endpush
