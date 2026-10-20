@php
    $hero = $homeSections->get('hero');
    $intro = $homeSections->get('introduction');
    $office = $homeSections->get('office-image');
    $services = $homeSections->get('services-intro');
    $practiceSelection = $homeSections->get('featured-practices');
    $values = $homeSections->get('values');
    $sectors = $homeSections->get('sectors');
    $approach = $homeSections->get('approach');
    $recognitions = $homeSections->get('recognitions');
    $consultation = $homeSections->get('consultation');
    $latestInsights = $homeSections->get('latest-insights');
@endphp

@if ($hero?->is_visible)
    @php($content = $hero->content ?? [])
    <section class="home-hero" id="home">
        <x-frontend.hero-visual :primary="$homeImageUrls['hero'] ?? null" :alt="$content['image_alt'] ?? ''" />
        <div class="container hero-content">
            @if (!empty($content['eyebrow']))<p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif
            <h1>{{ $hero->heading }}</h1>
            <div class="row g-4 align-items-end mt-3">
                <div class="col-lg-7">@if (!empty($content['description']))<p>{{ $content['description'] }}</p>@endif</div>
                <div class="col-lg-5 text-lg-end">@if (!empty($content['button_label']))<a class="text-link" href="{{ $content['button_url'] ?: route('about') }}">{{ $content['button_label'] }} <span aria-hidden="true">→</span></a>@endif</div>
            </div>
        </div>
    </section>
@endif

@if ($intro?->is_visible)
    @php($content = $intro->content ?? [])
    <section class="section-space surface-paper">
        <div class="container"><div class="row g-4 g-lg-5">
            <div class="col-lg-3"><p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p></div>
            <div class="col-lg-9">
                <x-frontend.section-heading>{{ $intro->heading }}</x-frontend.section-heading>
                <div class="row g-4 border-top-custom mt-4 pt-4">
                    <div class="col-md-8"><x-frontend.rich-text :html="$content['body'] ?? ''" /></div>
                    <div class="col-md-4">
                        @if (!empty($content['subheading']))<h3 class="h4">{{ $content['subheading'] }}</h3>@endif
                        @if (!empty($content['button_label']))<a class="text-link" href="{{ $content['button_url'] ?: route('about') }}">{{ $content['button_label'] }} <span aria-hidden="true">→</span></a>@endif
                    </div>
                </div>
            </div>
        </div></div>
    </section>
@endif

@if ($office?->is_visible)
    @php($content = $office->content ?? [])
    <section class="home-image-break surface-paper" aria-label="{{ $office->heading ?: 'Pendekatan kami' }}">
        <div class="image-frame">
            <img src="{{ $homeImageUrls['office-image'] ?? asset('assets/images/office.jpg') }}" alt="{{ $content['alt'] ?? '' }}" width="1920" height="1280" loading="lazy" decoding="async">
            @if (!empty($content['caption']))<p class="image-caption">{{ $content['caption'] }}</p>@endif
        </div>
    </section>
@endif

@if ($services?->is_visible)
    @php($content = $services->content ?? [])
    <section class="section-space surface-dark">
        <div class="container"><div class="row g-4 g-lg-5 align-items-end">
            <div class="col-lg-7"><x-frontend.section-heading>{{ $services->heading }}</x-frontend.section-heading></div>
            <div class="col-lg-5">
                <x-frontend.rich-text :html="$content['description'] ?? ''" />
                @if (!empty($content['button_label']))<a class="text-link" href="{{ $content['button_url'] ?: route('practice-areas') }}">{{ $content['button_label'] }} <span aria-hidden="true">→</span></a>@endif
            </div>
        </div></div>
    </section>
@endif

@if ($practiceSelection?->is_visible)
    @include('frontend.home.practice-directory')
@endif

@if ($values?->is_visible)
    @php($content = $values->content ?? [])
    <section class="home-values" aria-labelledby="value-title">
        <img src="{{ $homeImageUrls['values'] ?? asset('assets/images/values.jpg') }}" alt="" width="1920" height="1280" loading="lazy" decoding="async">
        <div class="value-copy">
            @if (!empty($content['eyebrow']))<p class="eyebrow">{{ $content['eyebrow'] }}</p>@endif
            <h2 id="value-title">{{ $values->heading }}</h2>
            <p id="value-description">{{ strip_tags((string) data_get($content, 'items.0.description', '')) }}</p>
            <div class="value-controls" role="group" aria-label="Nilai firma">
                @foreach (($content['items'] ?? []) as $index => $item)
                    <button type="button" data-value="{{ $index }}" data-value-title="{{ $item['title'] ?? '' }}" data-value-description="{{ strip_tags($item['description'] ?? '') }}" aria-label="Tampilkan nilai: {{ $item['title'] ?? 'Nilai firma' }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"><span></span></button>
                @endforeach
            </div>
        </div>
    </section>
@endif

@if ($sectors?->is_visible)
    @include('frontend.home.service-formats', ['formatsSection' => $sectors])
@endif

@if ($approach?->is_visible)
    @php($content = $approach->content ?? [])
    <section class="section-space surface-dark">
        <div class="container"><div class="row g-4 align-items-end mb-5">
            <div class="col-lg-7"><p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p><x-frontend.section-heading>{{ $approach->heading }}</x-frontend.section-heading></div>
            <div class="col-lg-5"><x-frontend.rich-text :html="$content['description'] ?? ''" /></div>
        </div><div class="row g-0 method-steps">
            @foreach (($content['items'] ?? []) as $index => $item)
                <article class="col-md-4 method-step"><span class="row-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $item['title'] ?? '' }}</h3><p>{{ strip_tags((string) ($item['description'] ?? '')) }}</p></article>
            @endforeach
        </div></div>
    </section>
@endif

@if ($recognitions?->is_visible)
    @php($content = $recognitions->content ?? [])
    <section class="section-space surface-light">
        <div class="container"><div class="row g-4 mb-5">
            <div class="col-lg-6"><p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p><x-frontend.section-heading>{{ $recognitions->heading }}</x-frontend.section-heading></div>
            <div class="col-lg-6"><x-frontend.rich-text :html="$content['description'] ?? ''" /></div>
        </div>
        @if ($homeRecognitions->isNotEmpty())
            <div class="editorial-list">@foreach ($homeRecognitions as $item)<article class="editorial-row"><span class="row-number">{{ $item['year'] }}</span><h3>{{ $item['title'] }}</h3><p>{{ $item['rank'] }} · Bukti telah diverifikasi</p></article>@endforeach</div>
        @else
            <p class="content-notice">Belum ada pengakuan yang diverifikasi dan disetujui untuk dipublikasikan.</p>
        @endif
        </div>
    </section>
@endif

@section('home-consultation')
@if ($consultation?->is_visible)
    @php($content = $consultation->content ?? [])
    <section class="section-space surface-paper" id="contact">
        <div class="container"><div class="row g-5"><div class="col-lg-6">
            <p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p><x-frontend.section-heading>{{ $consultation->heading }}</x-frontend.section-heading>
            <x-frontend.rich-text :html="$content['description'] ?? ''" />
            @if (filter_var($homeContactDetails['verified'] ?? false, FILTER_VALIDATE_BOOLEAN))
                @if (!empty($homeContactDetails['office_address']))<p>{{ $homeContactDetails['office_address'] }}</p>@endif
                @if (!empty($homeContactDetails['phone']))<p><a href="tel:{{ preg_replace('/[^0-9+]/', '', $homeContactDetails['phone']) }}">{{ $homeContactDetails['phone'] }}</a></p>@endif
                @if (!empty($homeContactDetails['email']))<p><a href="mailto:{{ $homeContactDetails['email'] }}">{{ $homeContactDetails['email'] }}</a></p>@endif
            @else
                <p class="content-notice">Informasi kontak langsung akan ditampilkan setelah diverifikasi.</p>
            @endif
            @if (!empty($content['button_label']))<a class="text-link" href="{{ $content['button_url'] ?: route('contact') }}">{{ $content['button_label'] }} <span aria-hidden="true">→</span></a>@endif
        </div><div class="col-lg-6"><x-frontend.enquiry-form prefix="home" /></div></div></div>
    </section>
@endif
@endsection

@if ($latestInsights?->is_visible)
    @php($content = $latestInsights->content ?? [])
    <section class="section-space surface-light" id="news">
        <div class="container"><div class="row g-4 align-items-end mb-5"><div class="col-lg-8"><p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p><x-frontend.section-heading>{{ $latestInsights->heading }}</x-frontend.section-heading></div><div class="col-lg-4 text-lg-end"><a class="text-link" href="{{ route('insights') }}">Lihat publikasi kami <span aria-hidden="true">→</span></a></div></div>
        @if ($homeInsights->isNotEmpty())
            <div class="row g-4">@foreach ($homeInsights as $insight)<article class="col-md-4 insight-card">
                @if ($insight->home_cover_url)<img src="{{ $insight->home_cover_url }}" alt="{{ $insight->title }}" width="900" height="600" loading="lazy" decoding="async">@endif
                <p class="eyebrow">{{ $insight->published_at?->format('d M Y') }}</p><h3>{{ $insight->title }}</h3><p>{{ $insight->excerpt }}</p>
                <a class="text-link" href="{{ route('insights') }}#{{ $insight->slug }}">Baca publikasi <span aria-hidden="true">→</span></a>
            </article>@endforeach</div>
        @else
            <p class="content-notice">Belum ada publikasi yang telah diterbitkan.</p>
        @endif
        </div>
    </section>
@endif
