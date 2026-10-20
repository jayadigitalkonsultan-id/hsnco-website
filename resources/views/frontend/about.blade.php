@extends('layouts.frontend')
@section('title', $aboutPage?->seo_title ?: ($aboutPage?->title ?: 'Tentang Firma').' — HSNCO')
@section('description', $aboutPage?->seo_description ?: ($aboutPage?->description ?: 'Kenali filosofi, nilai, dan pendekatan firma HSNCO.'))
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v={{ filemtime(public_path('assets/css/about.css')) }}">
@endpush
@section('content')

    @if ($aboutPageIsPublished)
        @include('frontend.about.cms-sections')
    @else
        <x-frontend.page-banner current="Tentang Firma" eyebrow="Tentang firma">
            <x-slot:heading>Tentang firma.</x-slot:heading>
            <x-slot:intro>Layanan hukum profesional dan efektif bagi klien Indonesia maupun internasional.</x-slot:intro>
        </x-frontend.page-banner>
        @include('frontend.about.introduction', [
            'introEyebrow' => 'Tentang kami',
            'introHeading' => 'HS & CO Counsellors at Law',
            'introColumnOne' => config('about.sections.introduction.content.column_one'),
            'introColumnTwo' => config('about.sections.introduction.content.column_two'),
        ])
        @section('about-closing-cta')
        @include('frontend.about.contact-cta', [
            'ctaEyebrow' => 'Konsultasi hukum',
            'ctaHeading' => 'Mari bicarakan kebutuhan Anda.',
            'ctaLabel' => 'Hubungi tim kami',
            'ctaUrl' => route('contact').'#enquiry',
        ])
        @endsection
    @endif
@include('frontend.partials.clients')
@yield('about-closing-cta')
@endsection
