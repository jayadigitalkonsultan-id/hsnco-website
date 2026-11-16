@props(['item'])
<article class="publication-card">
    <a class="publication-card-image" href="{{ $item->article_url }}" aria-label="Read {{ $item->title }}">
        @if($item->cover_url)<img src="{{ $item->cover_url }}" alt="{{ $item->publicCoverMedia?->alt_text ?: $item->title }}" width="900" height="600" loading="lazy" decoding="async">@else<span class="publication-placeholder" aria-hidden="true">HS &amp; CO<span>COUNSELLORS AT LAW</span></span>@endif
    </a>
    <div class="publication-card-copy">
        <p class="publication-meta"><span>{{ $item->publication_label }}</span><time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->format('d M Y') }}</time></p>
        <h3><a href="{{ $item->article_url }}">{{ $item->title }}</a></h3>
        @if($item->excerpt)<p class="publication-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($item->excerpt), 180) }}</p>@endif
        <a class="text-link" href="{{ $item->article_url }}">Read article <span aria-hidden="true">&rarr;</span></a>
    </div>
</article>
