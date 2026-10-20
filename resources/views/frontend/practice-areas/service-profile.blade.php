@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/practice-areas.css') }}?v={{ filemtime(public_path('assets/css/practice-areas.css')) }}">
@endpush
@push('scripts')
<script src="{{ asset('assets/js/practice-directory.js') }}?v={{ filemtime(public_path('assets/js/practice-directory.js')) }}" defer></script>
@endpush
<section class="section-space surface-paper services-introduction" aria-labelledby="services-intro-title">
    <div class="container"><div class="row g-4 g-lg-5">
        <div class="col-lg-6"><p class="eyebrow">Layanan kami</p><h2 id="services-intro-title">Ketelitian hukum.<br>Solusi yang <em>menyeluruh.</em></h2></div>
        <div class="col-lg-6 services-intro-copy">@foreach(config('services.introduction', []) as $paragraph)<p>{{ $paragraph }}</p>@endforeach<a class="text-link" href="#lingkup-layanan">Jelajahi keahlian kami <span aria-hidden="true">&darr;</span></a></div>
    </div></div>
</section>
<section class="section-space services-scope surface-charcoal" id="lingkup-layanan" aria-labelledby="services-scope-title">
    <div class="container">
        <div class="row g-4 align-items-end services-directory-heading"><div class="col-lg-7"><p class="eyebrow">Bidang praktik</p><h2 id="services-scope-title">Keahlian hukum,<br><em>terintegrasi.</em></h2></div><div class="col-lg-5"><p>Pilih bidang praktik untuk melihat lingkup layanan dan mendiskusikan kebutuhan hukum Anda bersama tim kami.</p></div></div>
        <div class="practice-directory">
            @forelse($practiceAreas as $practice)
            @php($items = collect(preg_split('/\r\n|\r|\n/', $practice->body ?? ''))->map(fn ($line) => trim($line))->filter()->values())
            <article class="practice-directory-row" id="{{ $practice->slug }}" data-practice-entry>
                <img class="practice-row-image" src="{{ $practice->public_image_url ?: asset('assets/images/office.jpg') }}" alt="" width="1200" height="600" loading="lazy" decoding="async">
                <span class="practice-row-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <h3 id="practice-heading-{{ $loop->iteration }}">{{ $practice->name }}</h3>
                <p id="practice-summary-{{ $loop->iteration }}">{{ $practice->summary }}</p>
                <span class="practice-row-arrow" aria-hidden="true">&rarr;</span>
                <button type="button" class="practice-row-trigger" data-bs-toggle="modal" data-bs-target="#practiceModal" data-practice-template="practice-content-{{ $loop->iteration }}" aria-labelledby="practice-heading-{{ $loop->iteration }}" aria-describedby="practice-summary-{{ $loop->iteration }}"><span class="visually-hidden">Lihat lingkup layanan</span></button>
            </article>
            <template id="practice-content-{{ $loop->iteration }}">
                <span data-practice-name>{{ $practice->name }}</span>
                <span data-practice-number>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }} / BIDANG PRAKTIK</span>
                <span data-practice-image>{{ $practice->public_image_url ?: asset('assets/images/office.jpg') }}</span>
                <p data-practice-overview>{{ $practice->summary }}</p>
                <ul data-practice-items>@foreach($items as $item)<li>{{ $item }}</li>@endforeach</ul>
            </template>
            @empty
                <p class="content-notice py-4">Informasi bidang praktik sedang diperbarui. Hubungi kami untuk mendiskusikan kebutuhan hukum Anda.</p>
            @endforelse
        </div>
        @if($practiceAreas->isNotEmpty())<p class="practice-image-note">Gambar merupakan ilustrasi bidang layanan.</p>@endif
    </div>
</section>