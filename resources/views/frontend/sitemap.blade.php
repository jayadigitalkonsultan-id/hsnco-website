@extends('layouts.frontend')
@php
    $banner = $sitemapSections->get('banner');
    $directory = $sitemapSections->get('directory');
    $showBanner = ! $banner || $banner->is_visible;
    $banner = $banner?->is_visible ? $banner : null;
    $directory = $directory?->is_visible ? $directory : null;
    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];
    $intro = $bannerContent['description'] ?? 'Find information about the firm, practice areas, professionals, insights and ways to contact HSNCO.';
@endphp
@section('title')
{{ $banner?->heading ?? 'Sitemap' }} — HSNCO
@endsection
@section('description')
{{ \Illuminate\Support\Str::limit(strip_tags($intro), 160) }}
@endsection
@section('content')
@if ($showBanner)
<x-frontend.page-banner current="Sitemap" :eyebrow="$bannerContent['eyebrow'] ?? 'Explore HSNCO'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'A clear path to every page.')) !!}</x-slot:heading><x-slot:intro>{!! app(\App\Services\Cms\RichTextSanitizer::class)->sanitize($intro) !!}</x-slot:intro></x-frontend.page-banner>
@endif

<section class="section-space surface-paper" aria-labelledby="sitemapDirectoryHeading">
    <div class="container">
        <div class="row g-4 align-items-end mb-4">
            <div class="col-lg-7">
                <p class="eyebrow">{{ $directoryContent['eyebrow'] ?? 'Public page directory' }}</p>
                <x-frontend.section-heading>{!! nl2br(e($directory?->heading ?? 'Explore HSNCO')) !!}</x-frontend.section-heading>
            </div>
            <div class="col-lg-5"><x-frontend.rich-text :html="$directoryContent['description'] ?? 'Find the pages and public links available on the HSNCO website.'" /></div>
        </div>
        <div class="sitemap-panel">
            <div class="row g-0">
                <div class="col-md-6 sitemap-tree-column">
                    <div id="siteTreeControls" hidden>
                        <label class="form-label" for="siteTreeSearch">Find a page</label>
                        <input class="form-control" type="search" id="siteTreeSearch" placeholder="Search website pages" autocomplete="off">
                        <div class="tree-tools">
                            <button type="button" id="expandSiteTree">Expand all</button>
                            <button type="button" id="collapseSiteTree">Collapse all</button>
                        </div>
                    </div>
                    <div id="siteTree" aria-label="Website structure" hidden></div>
                    <nav aria-label="Public pages">
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
                        <p class="eyebrow">Your next destination</p>
                        <h2 id="sitePageTitle">Explore HSNCO</h2>
                        <p id="sitePageDescription">Select a page to learn more. Direct links remain available without JavaScript.</p>
                        <a class="text-link" id="openSitePage" href="{{ route('home') }}" hidden>Open page terpilih <span aria-hidden="true">→</span></a>
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
