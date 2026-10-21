@extends('layouts.frontend')
@section('title','Industries — HS & CO')
@section('description','Explore eleven industries and their legal context, from agribusiness to technology and e-commerce.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/industries.css') }}?v={{ filemtime(public_path('assets/css/industries.css')) }}">@endpush
@section('content')
<x-frontend.page-banner current="Industries" eyebrow="Industries"><x-slot:heading>Understanding industries.<br>Informing perspectives.</x-slot:heading><x-slot:intro>Every industry has its own dynamics and legal considerations. Explore your business context as a starting point for a conversation with the HS &amp; CO team.</x-slot:intro></x-frontend.page-banner>
<section class="section-space industries-directory surface-dark" aria-labelledby="industries-title"><div class="container"><div class="row g-4 align-items-end mb-5"><div class="col-lg-7"><p class="eyebrow">Cross-sector perspectives</p><h2 id="industries-title">Diverse industries.<br><em>Distinct legal needs.</em></h2></div><div class="col-lg-5"><p class="industries-intro">Select an industry to explore its activities and the legal considerations relevant to your needs.</p></div></div><div class="row g-4">@foreach($industries as $industry)<div class="col-md-6 col-xl-4"><x-frontend.industry-card :industry="$industry" :index="$loop->iteration" /></div>@endforeach</div><p class="industries-image-note">Images illustrate the business context.</p></div></section>
@include('frontend.partials.industry-contact')
@endsection