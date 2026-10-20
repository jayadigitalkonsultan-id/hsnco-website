@props(['professional', 'featured' => false])
@php
    $practiceNames = $professional->publishedPracticeAreas->pluck('name');
    $searchText = collect([$professional->name, $professional->position, $professional->level, $professional->summary, $practiceNames->implode(' ')])->filter()->implode(' ');
    $initials = collect(explode(' ', preg_replace('/^(Assoc\. Prof\. Dr\.|Prof\. Dr\.|Dr\.|dr\.)\s*/', '', $professional->name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp
<article class="col-md-6 col-lg-4" data-profile-card data-level="{{ $professional->level }}" data-search="{{ $searchText }}">
    @if ($professional->public_photo_url)
        <img class="person-photo" src="{{ $professional->public_photo_url }}" alt="{{ $professional->publicPhoto?->alt_text ?: 'Potret '.$professional->name }}" width="720" height="900" loading="lazy" decoding="async">
    @else
        <div class="person-photo team-photo-placeholder" role="img" aria-label="Foto {{ $professional->name }} belum tersedia"><strong aria-hidden="true">{{ $initials }}</strong><span>Foto akan diperbarui</span></div>
    @endif
    <div class="person-info">
        <p>{{ $professional->position }}</p>
        <h4>{{ $professional->name }}</h4>
        @if ($practiceNames->isNotEmpty())<p class="professional-practice-areas">{{ $practiceNames->implode(' · ') }}</p>@endif
        @if (filled($professional->summary))<p class="person-summary">{{ $professional->summary }}</p>@endif
        <button class="text-link" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-profile="{{ $professional->slug }}" aria-label="Lihat profil {{ $professional->name }}">Lihat profil <span aria-hidden="true">&rarr;</span></button>
    </div>
</article>