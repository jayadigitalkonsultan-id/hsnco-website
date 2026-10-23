<section class="home-practice-showcase surface-dark" aria-labelledby="home-practice-title" data-practice-summary>
    <div class="container section-space">
        <div class="row g-4 align-items-end">
            <div class="col-lg-7"><p class="eyebrow">Practice Areas</p><h2 class="section-title mb-0" id="home-practice-title">Legal expertise.<br><em>Focused on your needs.</em></h2></div>
            <div class="col-lg-5 text-lg-end"><a class="text-link" href="{{ route('practice-areas') }}">Explore our practice areas <span aria-hidden="true">&rarr;</span></a></div>
        </div>
    </div>
    @if($summaryPractices->isNotEmpty())
    <div class="home-practice-panorama" data-practice-panorama>
        <img class="home-practice-panorama-image" src="{{ asset('assets/images/hsnco-lawfirm2.jpg') }}" alt="" width="1920" height="1280" loading="lazy" decoding="async">
        <div class="row g-0 position-relative">
            @foreach($summaryPractices as $practice)
            <a class="col-6 col-lg-3 home-practice-panel" href="{{ route('practice-areas') }}#{{ $practice->slug }}" aria-labelledby="home-practice-name-{{ $loop->iteration }}">
                <div class="home-practice-panel-copy"><h3 id="home-practice-name-{{ $loop->iteration }}">{{ $practice->name }}</h3><span class="home-practice-panel-action">Learn more <span aria-hidden="true">&rarr;</span></span></div>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <div class="container pb-5"><p class="content-notice">Our practice area information is being updated.</p></div>
    @endif
</section>