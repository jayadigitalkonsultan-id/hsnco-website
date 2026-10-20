@php
    $mapAddress = $verifiedContactDetails['address'] ?: config('contact-map.address');
    $mapQuery = rawurlencode($mapAddress);
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query='.$mapQuery;
    $embedUrl = 'https://maps.google.com/maps?q='.$mapQuery.'&z=16&output=embed';
@endphp
@if(filled($mapAddress))
<section class="section-space contact-location surface-light" id="lokasi-kantor" aria-labelledby="contact-location-title">
    <div class="container">
        <div class="row g-4 align-items-end mb-4"><div class="col-lg-7"><p class="eyebrow">Lokasi kantor</p><h2 id="contact-location-title">Lokasi kantor<br>HS &amp; CO.</h2></div><div class="col-lg-5"><p class="contact-location-intro">Lihat lokasi pada peta atau buka Google Maps untuk merencanakan perjalanan Anda.</p></div></div>
        <div class="row g-0 contact-map-panel">
            <div class="col-lg-4 contact-map-information"><span class="contact-map-label">HS &amp; CO / COUNSELLORS AT LAW</span><h3>Kantor kami</h3><address>{{ $mapAddress }}</address><a class="contact-map-link" href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer">Buka di Google Maps <span aria-hidden="true">&#8599;</span><span class="visually-hidden"> (tab baru)</span></a><a class="contact-map-enquiry" href="#enquiry">Hubungi tim kami <span aria-hidden="true">&rarr;</span></a></div>
            <div class="col-lg-8 contact-map-frame"><iframe src="{{ $embedUrl }}" title="Peta alamat kantor HS &amp; CO: {{ $mapAddress }}" width="100%" height="440" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>
        </div>
        <p class="contact-map-note">Peta disediakan oleh Google Maps berdasarkan alamat kantor. Jika peta tidak tampil, gunakan tautan Buka di Google Maps.</p>
    </div>
</section>
@endif