@props(['industry','index'=>1])
<a class="industry-card" href="{{ route('industries.show', $industry['slug']) }}" aria-label="Explore {{ $industry['name'] }}">
    <img src="{{ $industry['image_url'] }}" alt="" width="900" height="1000" loading="lazy" decoding="async">
    <span class="industry-card-number" aria-hidden="true">{{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span>
    <div class="industry-card-copy"><span class="industry-card-eyebrow">Industries</span><h3>{{ $industry['name'] }}</h3><p>{{ $industry['summary'] }}</p><span class="industry-card-action">Explore industry <span aria-hidden="true">&#8599;</span></span></div>
</a>