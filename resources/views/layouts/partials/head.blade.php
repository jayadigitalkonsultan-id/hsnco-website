@php
    $pageTitle = trim($seoPageTitle ?: $__env->yieldContent('title', $name));
    $pageDescription = trim($seoPageDescription ?: $__env->yieldContent('description', $description));
@endphp
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{ $pageDescription }}"><meta name="robots" content="index,follow,max-image-preview:large"><meta name="theme-color" content="#080808">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:type" content="website"><meta property="og:title" content="{{ $pageTitle }}"><meta property="og:description" content="{{ $pageDescription }}"><meta property="og:url" content="{{ $canonicalUrl }}">
<title>{{ $pageTitle }}</title>
<meta property="og:site_name" content="{{ $name }}">
@if($seoImage)<meta property="og:image" content="{{ $seoImage }}"><meta property="og:image:alt" content="{{ $seoImageAlt }}"><meta name="twitter:card" content="summary_large_image">@else<meta name="twitter:card" content="summary">@endif
<meta name="twitter:title" content="{{ $pageTitle }}"><meta name="twitter:description" content="{{ $pageDescription }}">
<link rel="icon" href="{{ $favicon }}">
<link rel="preload" href="{{ asset('assets/fonts/manrope.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
@foreach(['variables','style','components',request()->routeIs('home') ? 'home' : 'inner-pages','responsive','motion'] as $sheet)
<link rel="stylesheet" href="{{ asset('assets/css/'.$sheet.'.css') }}{{ in_array($sheet, ['motion', 'home']) ? '?v='.filemtime(public_path('assets/css/'.$sheet.'.css')) : '' }}">
@endforeach
@if(request()->routeIs('home'))<link rel="preload" href="{{ asset('assets/images/hero.jpg') }}" as="image">@endif
