@extends('layouts.frontend')
@section('title', 'Our Clients | HSNCO')
@section('description', 'Explore the clients of HSNCO Counsellors at Law.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/clients.css') }}?v={{ filemtime(public_path('assets/css/clients.css')) }}">@endpush
@section('content')
<x-frontend.page-banner current="Our Clients" eyebrow="HS & CO Counsellors at Law"><x-slot:heading>Our Clients.</x-slot:heading><x-slot:intro></x-slot:intro></x-frontend.page-banner>
<section class="clients-directory clients-logo-gallery" aria-label="Our clients">
    <div class="container">
        @if($directoryClients->isNotEmpty())
        <ul class="clients-logo-grid" role="list">
            @foreach($directoryClients as $client)
            <li data-client-entry>
                <div class="client-directory-tile {{ $client->logo_url && $client->publicImage?->caption === 'dark-surface' ? 'client-logo-dark' : '' }} {{ !$client->logo_url ? 'client-directory-wordmark' : '' }}">
                    @if($client->logo_url)
                    <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" width="300" height="180" loading="lazy" decoding="async">
                    @else
                    @php
                        $words = preg_split('/[\s\p{P}]+/u', trim($client->name), -1, PREG_SPLIT_NO_EMPTY);
                        $initials = collect($words)->reject(fn($word) => in_array(mb_strtoupper($word), ['PT', 'CV']))->take(3)->map(fn($word) => mb_substr($word, 0, 1))->implode('');
                    @endphp
                    <span class="client-wordmark-initials" aria-hidden="true">{{ mb_strtoupper($initials) }}</span>
                    <span class="client-wordmark-name {{ mb_strlen($client->name) > 48 ? 'client-wordmark-long' : '' }}">{{ $client->name }}</span>
                    <span class="client-wordmark-rule" aria-hidden="true"></span>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
        @else
        <p class="content-notice">Our client directory is being updated.</p>
        @endif
    </div>
</section>
@endsection
