@php
    $savedContent = $formatsSection?->content ?? [];
    $legacyTitles = config('service-formats.legacy_titles');
    $useUpdatedDefaults = !$formatsSection || (collect($savedContent['items'] ?? [])->pluck('title')->all() === $legacyTitles);
    $formatsContent = $useUpdatedDefaults ? config('service-formats') : $savedContent;
    $formatsHeading = $useUpdatedDefaults ? config('service-formats.heading') : $formatsSection->heading;
@endphp
<section class="section-space surface-light" aria-labelledby="service-formats-title" data-service-formats>
    <div class="container"><div class="row g-4 g-lg-5 align-items-end mb-5">
        <div class="col-lg-6"><p class="eyebrow">{{ $formatsContent['eyebrow'] ?? '' }}</p><h2 class="section-title" id="service-formats-title">{{ $formatsHeading }}</h2></div>
        <div class="col-lg-6"><x-frontend.rich-text :html="$formatsContent['description'] ?? ''" /></div>
    </div><div class="editorial-list">
        @foreach(($formatsContent['items'] ?? []) as $item)
            <article class="editorial-row"><span class="row-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $item['title'] ?? '' }}</h3><p>{{ strip_tags($item['description'] ?? '') }}</p></article>
        @endforeach
    </div></div>
</section>