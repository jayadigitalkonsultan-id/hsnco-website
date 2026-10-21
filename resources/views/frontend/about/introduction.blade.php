<section class="about-profile section-space surface-light" aria-labelledby="about-profile-title">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-start">
            <div class="col-lg-5">
                <p class="eyebrow">{{ $introEyebrow }}</p>
                <h2 id="about-profile-title" class="about-profile-title">{{ $introHeading }}</h2>
                <figure class="about-profile-figure">
                    <img src="{{ asset('assets/images/office.jpg') }}" alt="" width="900" height="1100" loading="lazy" decoding="async">
                    <figcaption>Legal insight. Considered action.<span>Illustration of a professional setting</span></figcaption>
                </figure>
            </div>
            <div class="col-lg-7">
                <div class="about-profile-copy"><x-frontend.rich-text :html="$introColumnOne" /></div>
                <div class="about-profile-detail"><x-frontend.rich-text :html="$introColumnTwo" /></div>
                <a class="text-link" href="{{ route('practice-areas') }}">Explore our legal services <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>
    </div>
</section>
<section class="about-approach section-space surface-dark" aria-labelledby="about-approach-title">
    <div class="container">
        <div class="row g-4 align-items-end mb-5">
            <div class="col-lg-7"><p class="eyebrow">Our approach</p><h2 id="about-approach-title">Understanding your needs.<br>Defining the way forward.</h2></div>
            <div class="col-lg-5"><p class="about-approach-lead">We prioritise mediation and negotiation, pursuing litigation as a last resort where necessary.</p></div>
        </div>
        <div class="row g-0 about-principles">
            @foreach ([['Ethics & professionalism', 'Legal services grounded in ethics and professionalism in every matter.'], ['Responsive service', 'Attentive support that responds to clients’ needs and enables effective legal service.'], ['Client interests', 'Efficient legal solutions guided by fairness and our clients’ interests.']] as $principle)
                <article class="col-md-4 about-principle"><span class="about-principle-number" aria-hidden="true">0{{ $loop->iteration }}</span><h3>{{ $principle[0] }}</h3><p>{{ $principle[1] }}</p></article>
            @endforeach
        </div>
    </div>
</section>