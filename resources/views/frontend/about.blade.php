@extends('layouts.frontend')
@section('title', $aboutPage?->seo_title ?: ($aboutPage?->title ?: 'Tentang Firma').' — HSNCO')
@section('description', $aboutPage?->seo_description ?: ($aboutPage?->description ?: 'Kenali filosofi, nilai, dan pendekatan firma HSNCO.'))
@section('content')
    @if ($aboutPageIsPublished)
        @include('frontend.about.cms-sections')
    @else
        <x-frontend.page-banner current="Tentang Firma" eyebrow="Tentang firma">
            <x-slot:heading>Tentang firma.</x-slot:heading>
            <x-slot:intro>Layanan hukum profesional dan efektif bagi klien Indonesia maupun internasional.</x-slot:intro>
        </x-frontend.page-banner>
        <section class="section-space surface-paper">
            <div class="container"><div class="row g-5">
                <div class="col-lg-4"><p class="eyebrow">Tentang kami</p><x-frontend.section-heading>HS &amp; CO Counsellors at Law</x-frontend.section-heading></div>
                <div class="col-lg-8"><div class="row g-4 editorial-copy">
                    <div class="col-md-6"><x-frontend.rich-text :html="config('about.sections.introduction.content.column_one')" /></div>
                    <div class="col-md-6"><x-frontend.rich-text :html="config('about.sections.introduction.content.column_two')" /></div>
                </div></div>
            </div></div>
        </section>
        <section class="section-space surface-paper">
            <div class="container">
                <x-frontend.section-heading>Mulai percakapan.</x-frontend.section-heading>
                <a class="text-link" href="{{ route('contact') }}#enquiry">Hubungi kami <span aria-hidden="true">&rarr;</span></a>
            </div>
        </section>
    @endif
@include('frontend.partials.clients')
@endsection
