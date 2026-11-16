@if($recommended->isNotEmpty())
<section class="section-space publication-recommendations"><div class="container"><div class="publication-section-heading"><div><p class="eyebrow">Further reading</p><h2>Recommended Insights</h2></div><a class="text-link" href="{{ route('insights') }}">View all news &amp; insights <span aria-hidden="true">&rarr;</span></a></div><div class="row g-4">@foreach($recommended as $item)<div class="col-md-6 col-lg-4"><x-frontend.publication-card :item="$item" /></div>@endforeach</div></div></section>
@endif
