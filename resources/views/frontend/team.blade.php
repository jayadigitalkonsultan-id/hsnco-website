@extends('layouts.frontend')
@php
    $banner = $teamSections->get('banner');
    $directory = $teamSections->get('professionals');
    $cta = $teamSections->get('closing-cta');
    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];
    $ctaContent = $cta?->content ?? [];
    $categoryLabels = config('professionals.categories');
    $professionalGroups = $professionals->groupBy('level')->sortBy(fn ($group, $key) => array_search($key, array_keys($categoryLabels), true) === false ? 999 : array_search($key, array_keys($categoryLabels), true));
@endphp
@section('title', ($banner?->heading ?? 'Tim Profesional').' — HSNCO')
@section('description', $bannerContent['description'] ?? 'Kenali tim profesional HS & CO Counsellors at Law.')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/team.css') }}?v={{ filemtime(public_path('assets/css/team.css')) }}">
@endpush
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Profesional" :eyebrow="$bannerContent['eyebrow'] ?? 'Tim di balik layanan hukum'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Tim profesional.')) !!}</x-slot:heading><x-slot:intro>{{ $bannerContent['description'] ?? 'Kenali tim profesional dan ruang lingkup keahlian HS & CO Counsellors at Law.' }}</x-slot:intro></x-frontend.page-banner>
@endif
@if (! $directory || $directory->is_visible)
<section class="section-space surface-paper team-directory"><div class="container">
    <p class="eyebrow">{{ $directoryContent['eyebrow'] ?? 'Direktori profesional' }}</p>
    <x-frontend.section-heading>{!! nl2br(e($directory?->heading ?? 'Keahlian yang saling melengkapi.')) !!}</x-frontend.section-heading>
    @if (filled($directoryContent['description'] ?? null))<p>{{ $directoryContent['description'] }}</p>@endif
    <div class="team-tools"><div class="row g-4 align-items-end">
        <div class="col-lg-4"><label class="form-label" for="professionalSearch">Cari profesional</label><input class="form-control" id="professionalSearch" type="search" placeholder="Nama, jabatan, atau keahlian" autocomplete="off"></div>
        <div class="col-lg-8"><div class="filters" role="group" aria-label="Filter kategori profesional"><button class="btn btn-filter" type="button" data-filter="all" aria-pressed="true">Semua</button>@foreach($professionalGroups as $level => $members)<button class="btn btn-filter" type="button" data-filter="{{ $level }}" aria-pressed="false">{{ $categoryLabels[$level] ?? Illuminate\Support\Str::headline($level) }}</button>@endforeach</div></div>
    </div></div>
    <p data-results role="status" aria-live="polite" class="result-count">{{ $professionals->count() }} profesional</p>
    @foreach($professionalGroups as $level => $members)
    <section class="team-group" data-profile-group aria-labelledby="group-{{ $level }}">
        <div class="team-group-heading"><h3 id="group-{{ $level }}">{{ $categoryLabels[$level] ?? Illuminate\Support\Str::headline($level) }}</h3><span>{{ str_pad((string) $members->count(), 2, '0', STR_PAD_LEFT) }} profil</span></div>
        <div class="row g-4 g-xl-5 people-grid">@foreach($members as $professional)<x-frontend.professional-card :professional="$professional" />@endforeach</div>
    </section>
    @endforeach
    <p class="empty-state" @if ($professionals->isNotEmpty()) hidden @endif>Tidak ada profil profesional yang sesuai. Coba kata kunci atau kategori lainnya.</p>
</div></section>
@endif
@if (! $cta || $cta->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4 align-items-end"><div class="col-lg-8"><p class="eyebrow">{{ $ctaContent['eyebrow'] ?? 'Bekerja bersama HSNCO' }}</p><x-frontend.section-heading>{!! nl2br(e($cta?->heading ?? 'Diskusikan kebutuhan hukum Anda.')) !!}</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><x-frontend.button :href="$ctaContent['button_url'] ?? (route('contact').'#enquiry')">{{ $ctaContent['button_label'] ?? 'Mulai percakapan' }} <span aria-hidden="true">&rarr;</span></x-frontend.button></div></div></div></section>
@endif
<x-frontend.portfolio-modal />
@endsection
@push('scripts')
<script>window.HSNCO_PROFESSIONALS = {{ Illuminate\Support\Js::from($profileModalData) }};</script>
@endpush