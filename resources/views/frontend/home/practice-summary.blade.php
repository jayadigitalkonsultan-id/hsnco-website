<section class="section-space surface-paper home-practice-summary" aria-labelledby="home-practice-title" data-practice-summary>
    <div class="container">
        <div class="row g-4 align-items-end mb-4 mb-lg-5">
            <div class="col-lg-8"><p class="eyebrow">Practice areas</p><h2 class="section-title" id="home-practice-title">Legal expertise.<br><em>Focused on your needs.</em></h2></div>
            <div class="col-lg-4 text-lg-end"><a class="text-link" href="{{ route('practice-areas') }}">All practice areas <span aria-hidden="true">&rarr;</span></a></div>
        </div>
        <div class="row gx-lg-5">
            @forelse($summaryPractices as $practice)
            <div class="col-md-6"><a class="home-practice-summary-link" href="{{ route('practice-areas') }}#{{ $practice->slug }}"><span class="home-practice-summary-number" aria-hidden="true">{{ str_pad((string)$loop->iteration,2,'0',STR_PAD_LEFT) }}</span><h3>{{ $practice->name }}</h3><span class="home-practice-summary-arrow" aria-hidden="true">&nearr;</span></a></div>
            @empty
            <div class="col-12"><p class="content-notice">Our practice area information is being updated.</p></div>
            @endforelse
        </div>
    </div>
</section>