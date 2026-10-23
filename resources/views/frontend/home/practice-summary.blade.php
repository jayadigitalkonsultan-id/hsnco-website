@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/practice-areas.css') }}?v={{ filemtime(public_path('assets/css/practice-areas.css')) }}">
@endpush
<section class="section-space surface-dark home-practice-showcase" aria-labelledby="home-practice-title" data-practice-summary>
    <div class="container">
        <div class="row g-4 align-items-end mb-4 mb-lg-5">
            <div class="col-lg-8"><p class="eyebrow">Practice Areas</p><h2 class="section-title" id="home-practice-title">Legal expertise.<br><em>Focused on your needs.</em></h2></div>
            <div class="col-lg-4 text-lg-end"><a class="text-link" href="{{ route('practice-areas') }}">All practice areas <span aria-hidden="true">&rarr;</span></a></div>
        </div>
        <div class="practice-directory">
            @forelse($summaryPractices as $practice)
            <a class="practice-directory-row" href="{{ route('practice-areas') }}#{{ $practice->slug }}" aria-labelledby="home-practice-name-{{ $loop->iteration }}">
                <img class="practice-row-image" src="{{ $practice->public_image_url ?: asset('assets/images/office.jpg') }}" alt="" width="1200" height="600" loading="lazy" decoding="async">
                <span class="practice-row-number" aria-hidden="true">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</span>
                <h3 id="home-practice-name-{{ $loop->iteration }}">{{ $practice->name }}</h3>
                <p>{{ $practice->summary }}</p>
                <span class="practice-row-arrow" aria-hidden="true">&rarr;</span>
            </a>
            @empty
            <p class="content-notice py-4">Our practice area information is being updated.</p>
            @endforelse
        </div>
    </div>
</section>