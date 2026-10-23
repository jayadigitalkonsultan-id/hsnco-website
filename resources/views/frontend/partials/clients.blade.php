@php
    $logoClients = $clients->filter(fn($client) => filled($client->logo_url));
@endphp
@if($logoClients->isNotEmpty())
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/clients.css') }}?v={{ filemtime(public_path('assets/css/clients.css')) }}">@endpush
@push('scripts')<script defer src="{{ asset('assets/js/clients.js') }}?v={{ filemtime(public_path('assets/js/clients.js')) }}"></script>@endpush
<section class="clients-section" aria-labelledby="clients-title" data-client-showcase>
    <div class="container">
        <h2 class="clients-title" id="clients-title"><a href="{{ route('clients') }}" aria-label="View all our clients">Our Clients</a></h2>
        <div class="clients-showcase-rows">
            @foreach([0, 1] as $row)
            @php
                $split = (int) ceil($logoClients->count()/2);
                $rowClients = $row === 0 ? $logoClients->values() : $logoClients->values()->slice($split)->concat($logoClients->values()->take($split));
            @endphp
            <div class="clients-rail {{ $row === 1 ? 'clients-rail-reverse' : '' }}" tabindex="0" role="region" aria-label="Client logos, row {{ $row + 1 }}. Hover or focus to pause; swipe to explore.">
                <div class="clients-track"><div class="clients-group">
                    @foreach($rowClients as $client)
                    <div class="client-logo-area {{ $client->publicImage?->caption === 'dark-surface' ? 'client-logo-dark' : '' }}">
                        <img src="{{ $client->logo_url }}" alt="{{ $row === 0 ? $client->name : '' }}" width="240" height="110" loading="lazy" decoding="async">
                    </div>
                    @endforeach
                </div></div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif