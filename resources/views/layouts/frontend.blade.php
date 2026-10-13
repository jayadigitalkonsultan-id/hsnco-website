<!doctype html>
<html lang="id"><head>
@include('layouts.partials.head')
@stack('styles')
</head><body class="{{ request()->routeIs('home') ? 'home-page' : 'inner-page' }}" data-page="{{ request()->route()->getName() }}" data-asset-base="{{ asset('assets') }}/">
@include('layouts.partials.header')
<main id="main" tabindex="-1">@yield('content')</main>
@include('layouts.partials.footer')
<noscript><p class="no-script container">Tautan navigasi tetap tersedia. Pratinjau interaktif dan validasi formulir memerlukan JavaScript.</p></noscript>
@include('layouts.partials.scripts')
@stack('scripts')
</body></html>
