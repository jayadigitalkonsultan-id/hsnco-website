@extends('layouts.frontend')
@php
    $banner = $teamSections->get('banner');
    $directory = $teamSections->get('professionals');
    $cta = $teamSections->get('closing-cta');
    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];
    $ctaContent = $cta?->content ?? [];
@endphp
@section('title')
{{ $banner?->heading ?? 'Tim Profesional' }} — HSNCO
@endsection
@section('description')
{{ $bannerContent['description'] ?? 'Jelajahi direktori profesional HSNCO dan bidang keahlian yang telah disetujui.' }}
@endsection
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Profesional" :eyebrow="$bannerContent['eyebrow'] ?? 'Tim di balik layanan hukum'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Tim profesional.')) !!}</x-slot:heading><x-slot:intro>{{ $bannerContent['description'] ?? 'Direktori profesional HSNCO dengan bidang keahlian dan pengalaman yang telah disetujui.' }}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $directory || $directory->is_visible)
<section class="section-space surface-paper"><div class="container">
    <div class="row g-4 g-lg-5 align-items-end mb-5"><div class="col-lg-7"><p class="eyebrow">{{ $directoryContent['eyebrow'] ?? 'Direktori profesional' }}</p><x-frontend.section-heading>{!! nl2br(e($directory?->heading ?? 'Pengalaman yang dapat diandalkan.')) !!}</x-frontend.section-heading></div><div class="col-lg-5"><label class="form-label" for="professionalSearch">Cari profesional</label><input class="form-control" id="professionalSearch" type="search" placeholder="Cari nama, jabatan, atau bidang praktik"><div class="filters mt-3" role="group" aria-label="Filter profesional"><button class="btn btn-filter" type="button" data-filter="all" aria-pressed="true">Semua</button><button class="btn btn-filter" type="button" data-filter="partner" aria-pressed="false">Mitra</button><button class="btn btn-filter" type="button" data-filter="associate" aria-pressed="false">Asosiat</button></div></div></div>
    @if (filled($directoryContent['description'] ?? null))<p class="mb-5">{{ $directoryContent['description'] }}</p>@endif
    <p data-results role="status" aria-live="polite" class="result-count">{{ $professionals->count() }} profesional</p>
    <div class="row g-5 people-grid">
        @if ($featuredProfessional)<x-frontend.professional-card :professional="$featuredProfessional" featured />@endif
        @foreach ($teamMembers as $professional)<x-frontend.professional-card :professional="$professional" />@endforeach
    </div>
    <p class="empty-state" @if ($professionals->isNotEmpty()) hidden @endif>Tidak ada profil profesional yang sesuai. Profil ditampilkan setelah disetujui dan dipublikasikan.</p>
</div></section>
@endif

@if (! $cta || $cta->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4 align-items-end"><div class="col-lg-8"><p class="eyebrow">{{ $ctaContent['eyebrow'] ?? 'Bekerja bersama HSNCO' }}</p><x-frontend.section-heading>{!! nl2br(e($cta?->heading ?? 'Wawasan berpengalaman. Komitmen pribadi.')) !!}</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><x-frontend.button :href="$ctaContent['button_url'] ?? (route('contact').'#enquiry')">{{ $ctaContent['button_label'] ?? 'Mulai percakapan' }} <span aria-hidden="true">→</span></x-frontend.button></div></div></div></section>
@endif
<x-frontend.detail-modal />
@endsection
@push('scripts')
<script>window.HSNCO_PROFESSIONALS = {{ Illuminate\Support\Js::from($profileModalData) }};</script>
@endpush
