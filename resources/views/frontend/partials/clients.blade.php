@php($logoClients = $clients->filter(fn($client) => filled($client->logo_url)))
@if($logoClients->isNotEmpty())
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/clients.css') }}?v={{ filemtime(public_path('assets/css/clients.css')) }}">@endpush
@push('scripts')<script defer src="{{ asset('assets/js/clients.js') }}?v={{ filemtime(public_path('assets/js/clients.js')) }}"></script>@endpush
<section class="clients-section surface-paper" aria-labelledby="clients-title" data-client-showcase>
    <div class="container">
        <h2 class="clients-title" id="clients-title"><a href="{{ route('clients') }}" aria-label="View all our clients">Our Clients</a></h2>
        <div class="clients-rail" tabindex="0" role="region" aria-label="Client logos. Hover or focus to pause; swipe to explore.">
            <div class="clients-track"><div class="clients-group">
                @foreach($logoClients as $client)
                <div class="client-logo-area {{ $client->publicImage?->caption === 'dark-surface' ? 'client-logo-dark' : '' }}">
                    <img src="{{ $client->logo_url }}" alt="{{ $client->name }}" width="220" height="100" loading="lazy" decoding="async">
                </div>
                @endforeach
            </div></div>
        </div>
    </div>
</section>
@endif