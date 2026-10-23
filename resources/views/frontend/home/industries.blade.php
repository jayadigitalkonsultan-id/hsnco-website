@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/industries.css') }}?v={{ filemtime(public_path('assets/css/industries.css')) }}">
@endpush
@php
    $industryCount = $homeIndustries->count();
    $lastRowCount = $industryCount % 4;
@endphp
<section class="home-industries home-industries-compact surface-dark" aria-label="Industries" data-home-industries>
    <div class="container"><div class="row g-3">
        @foreach($homeIndustries as $industry)
        @php
            $desktopSpan = $lastRowCount && $loop->iteration > $industryCount - $lastRowCount ? 12 / $lastRowCount : 3;
            $mobileSpan = $loop->last && $industryCount % 2 ? 12 : 6;
        @endphp
        <div class="col-{{ $mobileSpan }} col-lg-{{ $desktopSpan }}"><x-frontend.industry-card :industry="$industry" :index="$loop->iteration" :compact="true" /></div>
        @endforeach
    </div></div>
</section>