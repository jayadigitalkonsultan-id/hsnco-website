@extends('layouts.frontend')
@section('title',$industry['name'].' — HS & CO')
@section('description',$industry['summary'])
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/industries.css') }}?v={{ filemtime(public_path('assets/css/industries.css')) }}">@endpush
@section('content')
<section class="industry-detail-hero"><img src="{{ $industry['image_url'] }}" alt="" width="1920" height="1280" decoding="async"><div class="container"><nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('industries') }}">Industries</a></li><li class="breadcrumb-item active" aria-current="page">{{ $industry['name'] }}</li></ol></nav><p class="eyebrow">{{ $industry['english'] }}</p><h1>{{ $industry['name'] }}</h1><p class="industry-detail-summary">{{ $industry['summary'] }}</p></div></section>
<section class="section-space industry-overview surface-light"><div class="container"><div class="row g-5"><div class="col-lg-7"><p class="eyebrow">Industry overview</p><h2>Business context.<br>Legal perspective.</h2><p class="industry-overview-copy">{{ $industry['overview'] }}</p><a class="text-link" href="{{ route('practice-areas') }}">Explore our practice areas <span aria-hidden="true">&rarr;</span></a></div><div class="col-lg-5"><aside class="industry-focus"><p class="eyebrow">Topics for discussion</p><h3>Areas to consider</h3><ul>@foreach($industry['focus'] as $focus)<li>{{ $focus }}</li>@endforeach</ul><a href="{{ route('contact') }}#enquiry">Discuss this industry <span aria-hidden="true">&#8599;</span></a></aside></div></div></div></section>
<section class="section-space industries-directory surface-dark"><div class="container"><div class="row g-4 align-items-end mb-5"><div class="col-lg-8"><p class="eyebrow">Further perspectives</p><h2>Explore other industries.</h2></div><div class="col-lg-4 text-lg-end"><a class="text-link" href="{{ route('industries') }}">All industries &rarr;</a></div></div><div class="row g-4">@foreach($relatedIndustries as $related)<div class="col-md-6 col-xl-4"><x-frontend.industry-card :industry="$related" :index="$loop->iteration" /></div>@endforeach</div></div></section>
@if($industry['key_contacts']->isNotEmpty())
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/team.css') }}?v={{ filemtime(public_path('assets/css/team.css')) }}">@endpush
<section class="section-space surface-dark team-directory" aria-labelledby="key-contacts-title"><div class="container">
<p class="eyebrow">Key Contact</p><h2 id="key-contacts-title">Your key contacts.</h2>
<p>Select a profile to learn more about your key contacts for this industry.</p>
<div class="row g-4 g-xl-5 people-grid">@foreach($industry['key_contacts'] as $professional)<x-frontend.professional-card :professional="$professional" />@endforeach</div>
</div></section>
<x-frontend.portfolio-modal />
@push('scripts')<script>window.HSNCO_PROFESSIONALS = {{ Illuminate\Support\Js::from($profileModalData) }};</script>@endpush
@endif
@include('frontend.partials.industry-contact')
@endsection