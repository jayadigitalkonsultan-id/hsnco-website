@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/industries.css') }}?v={{ filemtime(public_path('assets/css/industries.css')) }}">
@endpush
<section class="home-industries surface-dark" aria-label="Industries" data-home-industries>
    <div class="container"><div class="row g-4">
        @foreach($homeIndustries as $industry)<div class="col-md-6 col-xl-4"><x-frontend.industry-card :industry="$industry" :index="$loop->iteration" /></div>@endforeach
    </div></div>
</section>