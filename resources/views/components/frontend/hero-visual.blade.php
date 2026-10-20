@props(['primary' => null, 'alt' => ''])
<div class="hero-visual" data-hero-slideshow aria-hidden="{{ $alt === '' ? 'true' : 'false' }}">
    <img class="hero-image hero-slide" src="{{ $primary ?: asset('assets/images/hero.jpg') }}" alt="{{ $alt }}" width="1920" height="1280" fetchpriority="high">
    <img class="hero-image hero-slide" src="{{ asset('assets/images/office.jpg') }}" alt="" width="1920" height="1280" decoding="async" fetchpriority="low">
    <img class="hero-image hero-slide" src="{{ asset('assets/images/corporate.jpg') }}" alt="" width="1920" height="1280" decoding="async" fetchpriority="low">
</div>