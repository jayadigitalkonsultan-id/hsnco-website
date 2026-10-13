<!doctype html>
<html lang="en"><head>
@include('layouts.partials.head')
@stack('styles')
</head><body class="{{ request()->routeIs('home') ? 'home-page' : 'inner-page' }}" data-page="{{ request()->route()->getName() }}" data-asset-base="{{ asset('assets') }}/">
@include('layouts.partials.header')
<main id="main" tabindex="-1">@yield('content')</main>
@include('layouts.partials.footer')
<noscript><p class="no-script container">Navigation links remain available. Interactive previews and local form validation require JavaScript.</p></noscript>
@include('layouts.partials.scripts')
@stack('scripts')
</body></html>
