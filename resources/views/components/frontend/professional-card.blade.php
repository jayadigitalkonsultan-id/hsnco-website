@props(['professional', 'featured' => false])
@php
    $practiceNames = $professional->publishedPracticeAreas->pluck('name');
    $searchText = collect([$professional->name, $professional->position, $professional->level, $professional->summary, $practiceNames->implode(' ')])->filter()->implode(' ');
@endphp
<article class="{{ $featured ? 'col-12 featured-profile' : 'col-md-6' }}" data-profile-card data-level="{{ $professional->level }}" data-search="{{ $searchText }}">
    @if ($featured)<div class="row g-5 align-items-center"><div class="col-md-7">@endif
    @if ($professional->public_photo_url)
        <img class="person-photo" src="{{ $professional->public_photo_url }}" alt="{{ $professional->publicPhoto?->alt_text ?: 'Potret '.$professional->name }}" width="900" height="600" loading="lazy" decoding="async">
    @else
        <div class="person-photo d-flex align-items-center justify-content-center text-center p-4 bg-dark text-white" role="img" aria-label="Potret {{ $professional->name }} belum tersedia">Potret profesional belum tersedia</div>
    @endif
    @if ($featured)</div><div class="person-info col-md-5">@else<div class="person-info">@endif
        @if ($featured)<span class="content-status">Profil pimpinan</span>@endif
        <h3>{{ $professional->name }}</h3>
        <p>{{ $professional->position }}</p>
        @if ($practiceNames->isNotEmpty())<p class="professional-practice-areas">@foreach ($professional->publishedPracticeAreas as $index => $practice)<a href="{{ route('practice-areas') }}#{{ $practice->slug }}">{{ $practice->name }}</a>@if (!$loop->last) <span aria-hidden="true">·</span> @endif @endforeach</p>@endif
        @if (filled($professional->summary))<p class="person-summary">{{ $professional->summary }}</p>@endif
        <button class="text-link" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-profile="{{ $professional->slug }}" aria-label="Lihat profil {{ $professional->name }}">Lihat profil <span aria-hidden="true">→</span></button>
    </div>@if ($featured)</div>@endif
</article>
