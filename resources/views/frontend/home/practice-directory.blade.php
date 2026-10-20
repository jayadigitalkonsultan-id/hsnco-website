@if ($homePracticeItems->isNotEmpty())
<section class="home-practice-tiles home-practice-directory" aria-label="Seluruh bidang praktik" data-home-practices>
    <div class="row g-0">
        @foreach ($homePracticeItems as $index => $practice)
            <div class="col-12 col-sm-6 col-lg-3">
                <x-frontend.practice-card :href="route('practice-areas').'#'.$practice['slug']">
                    <img src="{{ $practice['image'] ?: asset('assets/images/office.jpg') }}" alt="{{ $practice['image'] ? $practice['alt'] : '' }}" width="900" height="600" loading="lazy" decoding="async">
                    <div class="tile-copy"><span class="eyebrow">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} / Keahlian</span><h3>{{ $practice['name'] }}</h3><span class="text-link">Selengkapnya <span aria-hidden="true">&rarr;</span></span></div>
                </x-frontend.practice-card>
            </div>
        @endforeach
    </div>
</section>
@endif