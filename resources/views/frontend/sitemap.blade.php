@extends('layouts.frontend')
@section('title')
Website Sitemap — HSNCO
@endsection
@section('description')
Explore HSNCO website pages through an accessible visual sitemap.
@endsection
@section('content')
<x-frontend.page-banner current="Sitemap" eyebrow="Explore HSNCO"><x-slot:heading>A clear path.<br><em>Every page.</em></x-slot:heading><x-slot:intro>Find your way around the firm, practice areas, professional directory and editorial previews.</x-slot:intro></x-frontend.page-banner><section class="section-space surface-paper"><div class="container"><div class="sitemap-panel"><div class="row g-0"><div class="col-md-6 sitemap-tree-column"><label class="form-label" for="siteTreeSearch">Find a page</label><input class="form-control" type="search" id="siteTreeSearch" placeholder="Search website pages"><div class="tree-tools"><button type="button" id="expandSiteTree">Expand all</button><button type="button" id="collapseSiteTree">Collapse all</button></div><div id="siteTree"></div><ul class="sitemap-fallback" id="siteTreeFallback"><li><a data-site-page href="{{ route('home') }}">Home</a></li><li><a data-site-page href="{{ route('about') }}">About the Firm</a></li><li><a data-site-page href="{{ route('practice-areas') }}">Practice Areas</a></li><li><a data-site-page href="{{ route('team') }}">Professionals</a></li><li><a data-site-page href="{{ route('insights') }}">Insights</a></li><li><a data-site-page href="{{ route('contact') }}">Contact</a></li></ul></div><div class="col-md-6"><div class="sitemap-detail"><p class="eyebrow">Your next destination</p><h2 id="sitePageTitle">Explore HSNCO</h2><p>Select a page to continue your visit. The page directory is also accessible with a keyboard.</p><a class="text-link" id="openSitePage" href="{{ route('home') }}" hidden>Open selected page <span aria-hidden="true">→</span></a></div></div></div></div></div></section>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/jstree/themes/default/style.min.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/plugins.css') }}">
@endpush
@push('scripts')
<script defer src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script><script defer src="{{ asset('assets/vendor/jstree/jstree.min.js') }}"></script><script defer src="{{ asset('assets/plugins-init/jstree-init.js') }}"></script>
@endpush
