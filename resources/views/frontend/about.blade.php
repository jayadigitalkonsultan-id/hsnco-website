@extends('layouts.frontend')
@section('title', $aboutPage?->seo_title ?: ($aboutPage?->title ?: 'Tentang Firma').' — HSNCO')
@section('description', $aboutPage?->seo_description ?: ($aboutPage?->description ?: 'Kenali filosofi, nilai, dan pendekatan firma HSNCO.'))
@section('content')
    @if ($aboutPageIsPublished)
        @include('frontend.about.cms-sections')
    @else
        <x-frontend.page-banner current="Tentang Firma" eyebrow="Tentang firma">
            <x-slot:heading>Tentang firma.</x-slot:heading>
            <x-slot:intro>Informasi tentang firma sedang disiapkan.</x-slot:intro>
        </x-frontend.page-banner>
        <section class="section-space surface-paper">
            <div class="container">
                <x-frontend.section-heading>Mulai percakapan.</x-frontend.section-heading>
                <a class="text-link" href="{{ route('contact') }}#enquiry">Hubungi kami <span aria-hidden="true">&rarr;</span></a>
            </div>
        </section>
    @endif
@endsection
