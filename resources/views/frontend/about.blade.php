@extends('layouts.frontend')
@section('title', $aboutPage?->seo_title ?: ($aboutPage?->title ?: 'About the Firm').' — HSNCO')
@section('description', $aboutPage?->seo_description ?: ($aboutPage?->description ?: 'Discover the philosophy, values and approach of HSNCO.'))
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/about.css') }}?v={{ filemtime(public_path('assets/css/about.css')) }}">
@endpush
@section('content')

    @if ($aboutPageIsPublished)
        @include('frontend.about.cms-sections')
    @else
        <x-frontend.page-banner current="About the Firm" eyebrow="About the firm">
            <x-slot:heading>About the firm.</x-slot:heading>
            <x-slot:intro>Professional and effective legal services for Indonesian and international clients.</x-slot:intro>
        </x-frontend.page-banner>
        @include('frontend.about.introduction', [
            'introEyebrow' => 'About us',
            'introHeading' => 'HS & CO Counsellors at Law',
            'introColumnOne' => config('about.sections.introduction.content.column_one'),
            'introColumnTwo' => config('about.sections.introduction.content.column_two'),
        ])
        @section('about-closing-cta')
        @include('frontend.about.contact-cta', [
            'ctaEyebrow' => 'Legal consultation',
            'ctaHeading' => 'Let us discuss your needs.',
            'ctaLabel' => 'Contact our team',
            'ctaUrl' => route('contact').'#enquiry',
        ])
        @endsection
    @endif
@include('frontend.partials.clients')
@yield('about-closing-cta')
@endsection
