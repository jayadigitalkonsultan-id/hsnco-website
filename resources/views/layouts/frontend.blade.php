<!doctype html>
<html lang="en"><head>
@include('layouts.partials.head')
@stack('styles')
<link rel="stylesheet" href="{{ asset('assets/css/typography.css') }}?v={{ filemtime(public_path('assets/css/typography.css')) }}">
</head><body class="{{ request()->routeIs('home') ? 'home-page' : 'inner-page' }}" data-page="{{ request()->route()->getName() }}" data-asset-base="{{ asset('assets') }}/">
@include('layouts.partials.header')
<main id="main" tabindex="-1">@yield('content')</main>
@include('layouts.partials.footer')
<noscript><p class="no-script container">Navigation links remain available. Interactive previews and form validation require JavaScript.</p></noscript>
@include('layouts.partials.scripts')
@stack('scripts')
</body></html>
