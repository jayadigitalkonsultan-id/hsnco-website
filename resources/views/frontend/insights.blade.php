@extends('layouts.frontend')
@php
    $banner = $insightSections->get('banner');
    $directory = $insightSections->get('publications');
    $cta = $insightSections->get('closing-cta');
    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];
    $ctaContent = $cta?->content ?? [];
@endphp
@section('title')
{{ $banner?->heading ?? 'Wawasan & Publikasi' }} — HSNCO
@endsection
@section('description')
{{ $bannerContent['description'] ?? 'Analisis dan wawasan hukum dari HSNCO.' }}
@endsection
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Publikasi" :eyebrow="$bannerContent['eyebrow'] ?? 'Wawasan hukum'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Wawasan & perspektif.')) !!}</x-slot:heading><x-slot:intro>{{ $bannerContent['description'] ?? 'Analisis dan wawasan hukum dari para profesional HSNCO.' }}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $directory || $directory->is_visible)
<section class="section-space surface-paper"><div class="container">
    <div class="row g-4 align-items-end mb-4"><div class="col-lg-7"><p class="eyebrow">{{ $directoryContent['eyebrow'] ?? 'Publikasi HSNCO' }}</p><x-frontend.section-heading>{!! nl2br(e($directory?->heading ?? 'Perspektif untuk keputusan penting.')) !!}</x-frontend.section-heading></div><div class="col-lg-5">@if (filled($directoryContent['description'] ?? null))<p>{{ $directoryContent['description'] }}</p>@endif</div></div>
    @if ($leadInsight)
    <div class="row g-5 mt-1"><article class="col-lg-7 insight-lead" id="{{ $leadInsight->slug }}">
        @if ($leadInsight->cover_url)<img src="{{ $leadInsight->cover_url }}" alt="{{ $leadInsight->publicCoverMedia?->alt_text ?: 'Sampul '.$leadInsight->title }}" width="900" height="600" loading="lazy" decoding="async">@else<div class="insight-cover-placeholder" role="img" aria-label="Sampul {{ $leadInsight->title }} belum tersedia">Sampul belum tersedia</div>@endif
        <p class="eyebrow mt-4">Publikasi{{ $leadInsight->published_author_name ? " · ".$leadInsight->published_author_name : "" }}</p><h2>{{ $leadInsight->title }}</h2><p>{{ $leadInsight->excerpt }}</p><p class="insight-date">{{ $leadInsight->published_at?->timezone(config('cms.display_timezone'))->format('d/m/Y') }}</p><button class="text-link" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-insight="{{ $leadInsight->slug }}">Baca artikel <span aria-hidden="true">→</span></button>
    </article><div class="col-lg-5">
        @foreach ($insightStories as $insight)
        <article class="insight-story" id="{{ $insight->slug }}">
            @if ($insight->cover_url)<img class="insight-story-image" src="{{ $insight->cover_url }}" alt="{{ $insight->publicCoverMedia?->alt_text ?: 'Sampul '.$insight->title }}" width="900" height="600" loading="lazy" decoding="async">@endif
            <p class="eyebrow">Publikasi{{ $insight->published_author_name ? " · ".$insight->published_author_name : "" }}</p><h2>{{ $insight->title }}</h2><p>{{ $insight->excerpt }}</p><p class="insight-date">{{ $insight->published_at?->timezone(config('cms.display_timezone'))->format('d/m/Y') }}</p><button class="text-link" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-insight="{{ $insight->slug }}">Baca artikel <span aria-hidden="true">→</span></button>
        </article>
        @endforeach
    </div></div>
    @else
        <p class="empty-state">Belum ada publikasi yang tersedia.</p>
    @endif
    @if ($insights->hasPages())<nav class="mt-5" aria-label="Navigasi halaman publikasi">{{ $insights->links() }}</nav>@endif
</div></section>
@endif

@if (! $cta || $cta->is_visible)
<section class="section-space surface-dark"><div class="container"><div class="row g-4 align-items-end"><div class="col-lg-8"><p class="eyebrow">{{ $ctaContent['eyebrow'] ?? 'Bekerja bersama HSNCO' }}</p><x-frontend.section-heading>{!! nl2br(e($cta?->heading ?? 'Wawasan untuk pasar yang berkembang.')) !!}</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><x-frontend.button :href="$ctaContent['button_url'] ?? (route('contact').'#enquiry')">{{ $ctaContent['button_label'] ?? 'Mulai percakapan' }} <span aria-hidden="true">→</span></x-frontend.button></div></div></div></section>
@endif
<x-frontend.detail-modal />
@endsection
@push('scripts')
<script>window.HSNCO_INSIGHTS = {{ Illuminate\Support\Js::from($insightModalData) }};</script>
@endpush
