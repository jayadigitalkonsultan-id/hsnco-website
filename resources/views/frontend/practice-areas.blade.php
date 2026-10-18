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
{{ $bannerContent['description'] ?? 'Jelajahi bidang praktik HSNCO melalui pratinjau layanan yang mudah diakses.' }}
@endsection
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Bidang praktik" :eyebrow="$bannerContent['eyebrow'] ?? 'Keahlian lintas bidang'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Bidang praktik.')) !!}</x-slot:heading><x-slot:intro>{{ $bannerContent['description'] ?? 'Keahlian hukum khusus yang dipadukan sesuai kebutuhan bisnis setiap perkara. Jelajahi bidang praktik berikut.' }}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $directory || $directory->is_visible)
<section class="section-space surface-charcoal"><div class="container">
    <div class="row g-4 align-items-end mb-5"><div class="col-lg-7"><p class="eyebrow">{{ $directoryContent['eyebrow'] ?? 'Keahlian kami' }}</p><x-frontend.section-heading>{!! nl2br(e($directory?->heading ?? 'Keahlian terfokus. Nasihat terpadu.')) !!}</x-frontend.section-heading></div><div class="col-lg-5"><p>{{ $directoryContent['description'] ?? 'Keahlian lintas bidang kami terhubung untuk memberi nasihat yang terpadu sesuai kebutuhan setiap perkara.' }}</p></div></div>
    <div class="practice-list">
        @foreach ($practiceAreas as $index => $practice)
        <article id="{{ $practice->slug }}" class="practice-row row g-3 align-items-center"><div class="col-2 col-md-1"><span class="row-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span></div><div class="col-10 col-md-4"><h3>{{ $practice->name }}</h3></div><div class="col-12 col-md-5"><p>{{ $practice->summary }}</p></div><div class="col-12 col-md-2 text-md-end"><button class="text-link" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-practice="{{ $practice->slug }}" data-practice-title="{{ $practice->name }}" data-practice-summary="{{ $practice->summary }}" data-practice-body="{{ $practice->body }}" data-practice-image="{{ $practice->public_image_url ?? asset('assets/images/office.jpg') }}" data-practice-image-alt="{{ $practice->publicImage?->alt_text ?: $practice->name }}" aria-label="Jelajahi {{ $practice->name }}">Jelajahi <span aria-hidden="true">↗</span></button></div></article>
        @endforeach
        @if ($practiceAreas->isEmpty())<p class="content-notice">Bidang praktik akan ditampilkan setelah konten disetujui dan dipublikasikan.</p>@endif
    </div>
</div></section>
@endif

@if (! $integrated || $integrated->is_visible)
<section class="section-space surface-paper"><div class="container"><div class="row g-5"><div class="col-md-6"><p class="eyebrow">{{ $integratedContent['eyebrow'] ?? 'Pendekatan satu firma' }}</p><x-frontend.section-heading>{!! nl2br(e($integrated?->heading ?? 'Keahlian tanpa batas.')) !!}</x-frontend.section-heading></div><div class="col-md-6"><p>{{ $integratedContent['description'] ?? 'Persoalan kompleks sering melibatkan lebih dari satu bidang. Perspektif transaksi, regulasi, dan sengketa dipadukan untuk menjawab setiap tantangan.' }}</p></div></div></div></section>
@endif

@if (! $cta || $cta->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4 align-items-end"><div class="col-lg-8"><p class="eyebrow">{{ $ctaContent['eyebrow'] ?? 'Bekerja bersama HSNCO' }}</p><x-frontend.section-heading>{!! nl2br(e($cta?->heading ?? 'Keahlian yang tepat. Di saat yang tepat.')) !!}</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><x-frontend.button :href="$ctaContent['button_url'] ?? (route('contact').'#enquiry')">{{ $ctaContent['button_label'] ?? 'Mulai percakapan' }} <span aria-hidden="true">→</span></x-frontend.button></div></div></div></section>
@endif
<x-frontend.detail-modal />
@endsection
