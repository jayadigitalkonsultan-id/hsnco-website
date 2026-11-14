<section class="section-space surface-paper home-news" id="recent-news" aria-labelledby="recent-news-title">
    <div class="container">
        <div class="row g-4 align-items-end mb-5">
            <div class="col-lg-8">
                <p class="eyebrow">News &amp; updates</p>
                <h2 class="section-title" id="recent-news-title">Recent news.</h2>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a class="text-link" href="{{ route('news') }}">Explore all news <span aria-hidden="true">→</span></a>
            </div>
        </div>

        @if ($homeNews->isNotEmpty())
            <div class="home-news-grid">
                @foreach ($homeNews as $item)
                    <article class="home-news-card">
                        <a class="home-news-image-link" href="{{ route('news') }}#{{ $item->slug }}" aria-label="Read {{ $item->title }}">
                            @if ($item->home_cover_url)
                                <img src="{{ $item->home_cover_url }}" alt="{{ $item->publicCoverMedia?->alt_text ?: 'Cover image for '.$item->title }}" width="900" height="600" loading="lazy" decoding="async">
                            @else
                                <span class="home-news-image-placeholder" aria-hidden="true">HSNCO <small>News</small></span>
                            @endif
                        </a>
                        <div class="home-news-copy">
                            <p class="home-news-meta"><span>News</span><time datetime="{{ $item->published_at?->toDateString() }}">{{ $item->published_at?->timezone(config('cms.display_timezone'))->format('d M Y') }}</time></p>
                            <h3><a href="{{ route('news') }}#{{ $item->slug }}">{{ $item->title }}</a></h3>
                            @if ($item->excerpt)<p class="home-news-excerpt">{{ $item->excerpt }}</p>@endif
                            <a class="text-link" href="{{ route('news') }}#{{ $item->slug }}">Read news <span aria-hidden="true">→</span></a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p class="home-news-empty">News will appear here when published.</p>
        @endif
    </div>
</section>
