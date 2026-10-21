@extends('layouts.frontend')
@section('title', 'Our Clients - HS & CO')
@section('description', 'Discover the clients of HS & CO Counsellors at Law across a range of industries.')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/clients.css') }}?v={{ filemtime(public_path('assets/css/clients.css')) }}">@endpush
@section('content')
<x-frontend.page-banner current="Our Clients" eyebrow="Our clients"><x-slot:heading>Trusted across industries.</x-slot:heading><x-slot:intro>We are pleased to serve clients across a broad range of industries, both in Indonesia and internationally.</x-slot:intro></x-frontend.page-banner>
<section class="section-space surface-paper clients-directory" aria-label="All HSNCO clients">
    <div class="container"><div class="row g-3 g-lg-4">
        @forelse($directoryClients as $client)
        <div class="col-6 col-md-4 col-xl-3" data-client-entry>
            <div class="client-directory-tile {{ $client->logo_url && $client->publicImage?->caption === 'dark-surface' ? 'client-logo-dark' : '' }}">
                @if($client->logo_url)
                    <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" width="240" height="120" loading="lazy" decoding="async">
                @else
                    <span class="client-directory-name">{{ $client->name }}</span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12"><p class="content-notice">Our client directory is being updated.</p></div>
        @endforelse
    </div></div>
</section>
@endsection