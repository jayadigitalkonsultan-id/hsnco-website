@php
    $mapAddress = $verifiedContactDetails['address'] ?: config('contact-map.address');
    $mapQuery = rawurlencode($mapAddress);
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query='.$mapQuery;
    $embedUrl = 'https://maps.google.com/maps?q='.$mapQuery.'&z=16&output=embed';
@endphp
@if(filled($mapAddress))
<section class="section-space contact-location surface-light" id="lokasi-kantor" aria-labelledby="contact-location-title">
    <div class="container">
        <div class="row g-4 align-items-end mb-4"><div class="col-lg-7"><p class="eyebrow">Office location</p><h2 id="contact-location-title">Office location<br>HS &amp; CO.</h2></div><div class="col-lg-5"><p class="contact-location-intro">View our location on the map or open Google Maps to plan your visit.</p></div></div>
        <div class="row g-0 contact-map-panel">
            <div class="col-lg-4 contact-map-information"><span class="contact-map-label">HS &amp; CO / COUNSELLORS AT LAW</span><h3>Our office</h3><address>{{ $mapAddress }}</address><a class="contact-map-link" href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer">Open in Google Maps <span aria-hidden="true">&#8599;</span><span class="visually-hidden"> (new tab)</span></a><a class="contact-map-enquiry" href="#enquiry">Contact our team <span aria-hidden="true">&rarr;</span></a></div>
            <div class="col-lg-8 contact-map-frame"><iframe src="{{ $embedUrl }}" title="HS &amp; CO office location: {{ $mapAddress }}" width="100%" height="440" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
        </div>
        <p class="contact-map-note">The map is provided by Google Maps using our office address. If it does not load, please select Open in Google Maps.</p>
    </div>
</section>
@endif