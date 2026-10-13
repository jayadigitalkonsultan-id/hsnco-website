@php
    $pageTitle = trim($__env->yieldContent('title', 'HSNCO'));
    $pageDescription = trim($__env->yieldContent('description', 'HSNCO website'));
@endphp
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{ $pageDescription }}"><meta name="theme-color" content="#080808">
<meta property="og:type" content="website"><meta property="og:title" content="{{ $pageTitle }}"><meta property="og:description" content="{{ $pageDescription }}">
<title>{{ $pageTitle }}</title>
<link rel="icon" href="{{ asset('assets/icons/favicon.svg') }}" type="image/svg+xml">
<link rel="preload" href="{{ asset('assets/fonts/manrope.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
@foreach(['variables','style','components',request()->routeIs('home') ? 'home' : 'inner-pages','responsive'] as $sheet)
<link rel="stylesheet" href="{{ asset('assets/css/'.$sheet.'.css') }}">
@endforeach
@if(request()->routeIs('home'))<link rel="preload" href="{{ asset('assets/images/hero.jpg') }}" as="image">@endif
