@props(['current','eyebrow'])
@php
    $bannerImage = match(request()->route()?->getName()) {
        'practice-areas' => 'corporate.jpg',
        'billing-fees' => 'finance.jpg',
        'team' => 'values.jpg',
        'insights' => 'investment.jpg',
        'contact' => 'office.jpg',
        'sitemap' => 'transactions.jpg',
        default => 'hero.jpg',
    };
@endphp
<section class="inner-hero inner-hero-animated">
    <div class="banner-visual" aria-hidden="true"><img src="{{ asset('assets/images/'.$bannerImage) }}" alt="" width="1920" height="1280" decoding="async"></div>
    <div class="container"><x-frontend.breadcrumb :current="$current" /><div class="row g-4 g-lg-5 align-items-end"><div class="col-lg-6"><p class="eyebrow">{{ $eyebrow }}</p><h1>{{ $heading }}</h1></div><div class="col-lg-6"><p class="banner-intro">{{ $intro }}</p></div></div><span class="banner-accent" aria-hidden="true"></span></div>
</section>