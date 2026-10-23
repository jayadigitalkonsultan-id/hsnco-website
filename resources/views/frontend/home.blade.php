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
<section class="home-values" aria-labelledby="value-title"><img class="" src="{{ asset('assets/images/hsnco4.jpg') }}" alt="" width="1920" height="1280" loading="lazy" decoding="async"><div class="value-copy"><p class="eyebrow">Our values</p><h2 id="value-title">A clear approach.</h2><p id="value-description">We understand the business context behind legal matters. Our role is to bring clarity to complexity and support informed decisions.</p><div class="value-controls" role="group" aria-label="Our values"><button type="button" data-value="0" data-value-title="A clear approach." data-value-description="We understand the business context behind legal matters. Our role is to bring clarity to complexity and support informed decisions." aria-label="Show value: Pendekatan that jelas" aria-pressed="true"><span></span></button><button type="button" data-value="1" data-value-title="Uncompromising integrity." data-value-description="We protect our clients’ interests with independence, care and integrity." aria-label="Show value: Integrity" aria-pressed="false"><span></span></button><button type="button" data-value="2" data-value-title="Reliability in every matter." data-value-description="Responsive support, experienced attention and practical advice throughout each engagement." aria-label="Show value: Reliability" aria-pressed="false"><span></span></button></div></div></section>
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
@unless ($homePageIsPublished)
<section class="section-space surface-light" id="news"><div class="container"><div class="row g-4 align-items-end mb-5"><div class="col-lg-8"><p class="eyebrow">Legal insights</p><x-frontend.section-heading>Latest insights.</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><a class="text-link" href="{{ route('insights') }}">Explore our insights <span aria-hidden="true">→</span></a></div></div><p class="content-notice"><span class="content-status">Content pending approval</span> These titles are publication previews. Verified articles and publication dates are not yet available.</p><div class="row g-4"><article class="col-md-4 insight-card"><span class="content-status">Publication preview</span><img class="" src="{{ asset('assets/images/investment.jpg') }}" alt="Illustrative image publication" width="900" height="600" loading="lazy" decoding="async"><h3>Indonesia’s evolving investment framework: considerations for boards</h3><a class="text-link" href="{{ route('insights') }}#insight-1">View publication preview <span aria-hidden="true">→</span></a></article><article class="col-md-4 insight-card"><span class="content-status">Publication preview</span><img class="" src="{{ asset('assets/images/transactions.jpg') }}" alt="Illustrative image publication" width="900" height="600" loading="lazy" decoding="async"><h3>Cross-border mergers and acquisitions in a changing regulatory landscape</h3><a class="text-link" href="{{ route('insights') }}#insight-2">View publication preview <span aria-hidden="true">→</span></a></article><article class="col-md-4 insight-card"><span class="content-status">Publication preview</span><img class="" src="{{ asset('assets/images/data.jpg') }}" alt="Illustrative image publication" width="900" height="600" loading="lazy" decoding="async"><h3>Data governance and a new era of corporate accountability</h3><a class="text-link" href="{{ route('insights') }}#insight-3">View publication preview <span aria-hidden="true">→</span></a></article></div></div></section>
@endunless
@include('frontend.partials.clients')
@yield('home-consultation')
@endsection
