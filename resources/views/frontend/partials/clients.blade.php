@if($clients->isNotEmpty())
@push('styles')<link rel="stylesheet" href="{{ asset('assets/css/clients.css') }}">@endpush
@push('scripts')<script defer src="{{ asset('assets/js/clients.js') }}"></script>@endpush
<section class="section-space surface-paper clients-section" aria-labelledby="clients-title" data-client-showcase>
 <div class="container">
  <div class="row g-4 align-items-end mb-4"><div class="col-lg-6"><p class="eyebrow">Klien kami</p><h2 class="section-heading" id="clients-title">Kepercayaan lintas sektor.<br><em>Hubungan yang berarti.</em></h2></div><div class="col-lg-6"><p class="clients-intro">{{ config('clients.introduction') }}</p></div></div>
  <div class="clients-toolbar"><div class="clients-filters" aria-label="Filter sektor klien" hidden><button type="button" data-client-filter="all" aria-pressed="true">Semua sektor</button>@foreach(config('clients.categories') as $key => $label)<button type="button" data-client-filter="{{ $key }}" aria-pressed="false">{{ $label }}</button>@endforeach</div><div class="clients-controls" hidden><button type="button" data-client-prev aria-label="Klien sebelumnya">&larr;</button><button type="button" data-client-next aria-label="Klien berikutnya">&rarr;</button></div></div>
  <div class="clients-rail" tabindex="0" role="region" aria-label="Slideshow klien HSNCO">
   @foreach($clients as $client)
    <article class="client-card" data-client-category="{{ $client->category }}">
     <div class="client-logo-area {{ $client->publicImage?->caption === 'dark-surface' ? 'client-logo-dark' : '' }}">@if($client->logo_url)<img src="{{ $client->logo_url }}" alt="Logo {{ $client->name }}" width="220" height="100" loading="lazy" decoding="async">@else<span class="client-nameplate">{{ $client->name }}</span>@endif</div>
     <div class="client-card-copy"><span class="client-sector">{{ config('clients.categories.'.$client->category) }}</span><h3>{{ $client->name }}</h3>@if($client->summary)<p>{{ $client->summary }}</p>@endif
     @if($client->body)<button type="button" class="client-detail" data-bs-toggle="modal" data-bs-target="#clientDetailModal" data-client-name="{{ $client->name }}" data-client-description="{{ $client->body }}" data-client-sector="{{ config('clients.categories.'.$client->category) }}">Gambaran penugasan <span aria-hidden="true">&#8599;</span></button>@endif</div>
    </article>
   @endforeach
  </div>
  <p class="clients-status" aria-live="polite" aria-atomic="true">{{ $clients->count() }} klien &middot; Geser untuk menjelajahi</p>
 </div>
</section>
<div class="modal fade" id="clientDetailModal" tabindex="-1" aria-labelledby="clientDetailTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h4" id="clientDetailTitle">Gambaran penugasan</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><p class="eyebrow" data-client-modal-sector></p><p data-client-modal-body class="client-engagement"></p></div></div></div></div>
@endif