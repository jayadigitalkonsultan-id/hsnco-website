@php
    $serviceGroups = $practiceAreas->map(fn ($practice) => [
        'slug' => $practice->slug,
        'title' => $practice->name,
        'caption' => $practice->summary,
        'image_url' => $practice->public_image_url,
        'items' => collect(preg_split('/\r\n|\r|\n/', $practice->body ?? ''))->map(fn ($line) => trim($line))->filter()->values()->all(),
    ]);
@endphp
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/practice-areas.css') }}">
@endpush
<section class="section-space surface-paper services-introduction" aria-labelledby="services-intro-title">
    <div class="container"><div class="row g-5 align-items-start">
        <div class="col-lg-5"><p class="eyebrow">Layanan kami</p><h2 class="section-heading" id="services-intro-title">Ketelitian hukum.<br><em>Solusi menyeluruh.</em></h2><div class="services-signature" aria-hidden="true">HS &amp; CO <span>COUNSELLORS AT LAW</span></div></div>
        <div class="col-lg-7 services-intro-copy">@foreach(config('services.introduction', []) as $paragraph)<p>{{ $paragraph }}</p>@endforeach<a class="text-link" href="#lingkup-layanan">Jelajahi layanan <span aria-hidden="true">&darr;</span></a></div>
    </div></div>
</section>
<section class="section-space surface-charcoal services-scope" id="lingkup-layanan" aria-labelledby="services-scope-title">
    <div class="container">
        <div class="row g-4 align-items-end services-section-heading"><div class="col-lg-8"><p class="eyebrow">Lingkup layanan</p><x-frontend.section-heading id="services-scope-title">Keahlian yang terarah.<br>Pendampingan yang <em>menyeluruh.</em></x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><a class="text-link" href="{{ route('contact') }}#enquiry">Diskusikan kebutuhan Anda <span aria-hidden="true">&#8599;</span></a></div></div>
        @if ($serviceGroups->isNotEmpty())
        <nav class="services-index" aria-label="Pilih bidang praktik">
            @foreach($serviceGroups as $group)<a href="#{{ $group['slug'] }}"><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $group['title'] }}</a>@endforeach
        </nav>
        <div class="row g-4 align-items-stretch">
            @foreach($serviceGroups as $group)
                <div class="{{ $loop->first ? 'col-12' : 'col-lg-6' }}">
                    <article class="services-panel {{ $loop->first ? 'services-panel-featured' : '' }}" id="{{ $group['slug'] }}" aria-labelledby="service-group-{{ $loop->iteration }}">
                        <header class="services-visual">
                            <img src="{{ $group['image_url'] ?? asset('assets/images/office.jpg') }}" alt="" width="900" height="600" loading="lazy" decoding="async">
                            <div class="services-visual-copy"><span class="services-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><p class="services-category">BIDANG PRAKTIK</p><h3 id="service-group-{{ $loop->iteration }}">{{ $group['title'] }}</h3><p class="services-caption">{{ $group['caption'] }}</p></div></div>
                        </header>
                        <div class="services-panel-body">
                            <ul class="services-items {{ $loop->first ? 'services-items-columns' : '' }}">
                                @foreach($group['items'] as $item)<li><span class="services-item-marker" aria-hidden="true"></span><span>{{ $item }}</span></li>@endforeach
                            </ul>
                            <footer class="services-panel-footer"><a href="{{ route('contact') }}#enquiry" aria-label="Konsultasi {{ $group['title'] }}">Konsultasikan kebutuhan hukum Anda <span aria-hidden="true">&#8599;</span></a></footer>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        <p class="services-image-note">Gambar digunakan sebagai ilustrasi bidang layanan.</p>
        @else
        <p class="content-notice">Informasi bidang praktik sedang diperbarui. Hubungi kami untuk mendiskusikan kebutuhan hukum Anda.</p>
        @endif
    </div>
</section>
