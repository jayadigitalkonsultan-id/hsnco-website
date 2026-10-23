@props(['professional', 'featured' => false, 'filterLevel' => null])
@php
    $practiceNames = $professional->publishedPracticeAreas->pluck('name');
    $searchText = collect([$professional->name, $professional->position, $professional->level, $professional->summary, $practiceNames->implode(' ')])->filter()->implode(' ');
    $initials = collect(explode(' ', preg_replace('/^(Assoc\. Prof\. Dr\.|Prof\. Dr\.|Dr\.|dr\.)\s*/', '', $professional->name)))->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('');
@endphp
<article class="col-md-6 col-lg-4" data-profile-card data-level="{{ $professional->level }}" data-filter-level="{{ $filterLevel ?? $professional->level }}" data-search="{{ $searchText }}">
    <button class="professional-photo-trigger" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-profile="{{ $professional->slug }}" aria-label="Open portfolio for {{ $professional->name }}" aria-haspopup="dialog">
    @if ($professional->public_photo_url)
        <img class="person-photo" src="{{ $professional->public_photo_url }}" alt="{{ $professional->publicPhoto?->alt_text ?: 'Portrait of '.$professional->name }}" width="720" height="900" loading="lazy" decoding="async">
    @else
        <span class="person-photo team-photo-placeholder" role="img" aria-label="Photograph {{ $professional->name }} is not yet available"><strong aria-hidden="true">{{ $initials }}</strong><span>Photograph forthcoming</span></span>
    @endif
    </button>
    <div class="person-info">
        <p class="professional-position">{{ $professional->position }}</p>
        <h4><button class="text-link professional-name-trigger" type="button" data-bs-toggle="modal" data-bs-target="#detailModal" data-profile="{{ $professional->slug }}" aria-label="View profile for {{ $professional->name }}"><span>{{ $professional->name }}</span><span class="professional-name-arrow" aria-hidden="true">&rarr;</span></button></h4>
    </div>
</article>