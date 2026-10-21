@extends('layouts.frontend')
@php
    $banner = $practiceSections->get('banner');
    $directory = $practiceSections->get('practice-directory');


    $bannerContent = $banner?->content ?? [];
    $directoryContent = $directory?->content ?? [];


@endphp
@section('title')
{{ $banner?->heading ?? 'Practice Areas' }} — HSNCO
@endsection
@section('description')
{{ $bannerContent['description'] ?? 'HS & CO legal services: advice, legal opinions, audits, due diligence and representation in civil, commercial and criminal matters.' }}
@endsection
@section('content')
@if (! $banner || $banner->is_visible)
<x-frontend.page-banner current="Practice areas" :eyebrow="$bannerContent['eyebrow'] ?? 'Multidisciplinary expertise'"><x-slot:heading>{!! nl2br(e($banner?->heading ?? 'Practice areas.')) !!}</x-slot:heading><x-slot:intro>{{ $bannerContent['description'] ?? 'Comprehensive legal solutions for civil, commercial and criminal matters.' }}</x-slot:intro></x-frontend.page-banner>
@endif

@if (! $directory || $directory->is_visible)
@include('frontend.practice-areas.service-profile')
@endif

@include('frontend.partials.practice-enquiry')
<x-frontend.practice-portfolio-modal />
@endsection
