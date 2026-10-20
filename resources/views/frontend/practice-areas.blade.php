@extends('layouts.frontend')
@php
    $banner = $practiceSections->get('banner');
    $directory = $practiceSections->get('practice-directory');
    $integrated = $practiceSections->get('integrated-services');
    $cta = $practiceSections->get('closing-cta');
    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];
    $integratedContent = $integrated?->content ?? [];
    $ctaContent = $cta?->content ?? [];
@endphp
@section('title')
{{ $banner?->heading ?? 'Bidang Praktik' }} — HSNCO
@endsection
@section('description')
{{ $bannerContent['description'] ?? 'Layanan hukum HS & CO: nasihat hukum, pendapat hukum, audit hukum, due diligence, serta pendampingan perdata, komersial, dan pidana.' }}
@endsection
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Bidang praktik" :eyebrow="$bannerContent['eyebrow'] ?? 'Keahlian lintas bidang'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Bidang praktik.')) !!}</x-slot:heading><x-slot:intro>{{ $bannerContent['description'] ?? 'Solusi hukum komprehensif untuk kebutuhan perdata, komersial, dan pidana.' }}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $directory || $directory->is_visible)
@include('frontend.practice-areas.service-profile')
@endif

@if (! $integrated || $integrated->is_visible)
<section class="section-space surface-paper"><div class="container"><div class="row g-5"><div class="col-md-6"><p class="eyebrow">{{ $integratedContent['eyebrow'] ?? 'Pendekatan satu firma' }}</p><x-frontend.section-heading>{!! nl2br(e($integrated?->heading ?? 'Keahlian tanpa batas.')) !!}</x-frontend.section-heading></div><div class="col-md-6"><p>{{ $integratedContent['description'] ?? 'Persoalan kompleks sering melibatkan lebih dari satu bidang. Perspektif transaksi, regulasi, dan sengketa dipadukan untuk menjawab setiap tantangan.' }}</p></div></div></div></section>
@endif

@include('frontend.partials.billing-link')
@if (! $cta || $cta->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4 align-items-end"><div class="col-lg-8"><p class="eyebrow">{{ $ctaContent['eyebrow'] ?? 'Bekerja bersama HSNCO' }}</p><x-frontend.section-heading>{!! nl2br(e($cta?->heading ?? 'Keahlian yang tepat. Di saat yang tepat.')) !!}</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><x-frontend.button :href="$ctaContent['button_url'] ?? (route('contact').'#enquiry')">{{ $ctaContent['button_label'] ?? 'Mulai percakapan' }} <span aria-hidden="true">→</span></x-frontend.button></div></div></div></section>
@endif
<x-frontend.practice-portfolio-modal />
@endsection
