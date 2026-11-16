@extends('layouts.frontend')
@section('title')
Strategic Legal Counsel — HSNCO
@endsection
@section('description')
Explore the legal services, practice areas, approach and professionals of HSNCO.
@endsection
@section('content')
@if ($homePageIsPublished)
    @include('frontend.home.cms-sections')
@endif

@unless ($homePageIsPublished)
<section class="home-hero" id="home"><x-frontend.hero-visual /><div class="container hero-content"><p class="eyebrow">An independent Indonesian law firm</p><x-frontend.home-heading /><div class="row g-4 align-items-end mt-3"><div class="col-lg-5 ms-lg-auto text-lg-end"><a class="text-link" href="{{ route('about') }}">Discover HSNCO <span aria-hidden="true">→</span></a></div></div></div></section>
@endunless


@unless ($homePageIsPublished)
@include('frontend.home.industries-heading')
@endunless
@unless ($homePageIsPublished)
@include('frontend.home.industries')
@endunless
@unless ($homePageIsPublished)
@include('frontend.home.service-formats', ['formatsSection' => null])
@endunless
@unless ($homePageIsPublished)
@include('frontend.home.practice-summary')
@endunless
@unless ($homePageIsPublished)
@section('home-consultation')
<section class="section-space surface-paper" id="contact"><div class="container"><div class="row g-5"><div class="col-lg-6"><p class="eyebrow">Start a conversation</p><x-frontend.section-heading>Deep expertise.<br><em>Clear direction.</em></x-frontend.section-heading><p>Tell us about your legal needs.</p>
@if ($homeContactDetails['address'])<p>{{ $homeContactDetails['address'] }}</p>@endif
@if ($homeContactDetails['phone'])<p><a href="tel:{{ $homeContactDetails['phone_href'] }}">{{ $homeContactDetails['phone'] }}</a></p>@endif
@if ($homeContactDetails['email'])<p><a href="mailto:{{ $homeContactDetails['email'] }}">{{ $homeContactDetails['email'] }}</a></p>@endif
@if (! $homeContactDetails['address'] && ! $homeContactDetails['phone'] && ! $homeContactDetails['email'])<p class="content-notice">Verified contact details will be made available here.</p>@endif
<a class="text-link" href="{{ route('contact') }}">Contact information <span aria-hidden="true">→</span></a></div><div class="col-lg-6"><x-frontend.enquiry-form prefix="home" /></div></div></div></section>
@endsection
@endunless
@include('frontend.home.publications')
@include('frontend.partials.clients')
@yield('home-consultation')
@endsection
