@foreach ($aboutPage->sections as $section)
    @continue(! $section->is_visible)
    @php($content = $section->content ?? [])
    @switch($section->key)
        @case('banner')
            <x-frontend.page-banner :current="$content['breadcrumb_label'] ?? 'About the Firm'" :eyebrow="$content['eyebrow'] ?? ''">
                <x-slot:heading>{{ $section->heading }}</x-slot:heading>
                <x-slot:intro>{{ strip_tags($content['description'] ?? '') }}</x-slot:intro>
            </x-frontend.page-banner>
            @break

        @case('introduction')
            @include('frontend.about.introduction', [
                'introEyebrow' => $content['eyebrow'] ?? 'About us',
                'introHeading' => $section->heading,
                'introColumnOne' => $content['column_one'] ?? '',
                'introColumnTwo' => $content['column_two'] ?? '',
            ])
            @break

        @case('quote')
            @if (in_array($content['approved'] ?? false, [true, 1, '1'], true) && filled($content['quote'] ?? null) && filled($content['attribution'] ?? null) && filled($content['evidence'] ?? null))
                <section class="section-space surface-dark">
                    <div class="container"><div class="row g-4">
                        <div class="col-lg-3 quote-mark" aria-hidden="true">&ldquo;</div>
                        <div class="col-lg-9"><blockquote class="firm-quote">
                            <x-frontend.rich-text :html="$content['quote']" />
                            <cite>{{ $content['attribution'] }}</cite>
                        </blockquote></div>
                    </div></div>
                </section>
            @endif
            @break

        @case('history')
            @if ($aboutMilestones->isNotEmpty())
                <section class="section-space surface-paper">
                    <div class="container">
                        <p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p>
                        <x-frontend.section-heading class="mb-4">{{ $section->heading }}</x-frontend.section-heading>
                        <div class="timeline">
                            @foreach ($aboutMilestones as $item)
                                <article class="row g-3 timeline-item">
                                    <div class="col-md-2"><span class="row-number">{{ $item['year'] }}</span></div>
                                    <div class="col-md-5"><h3>{{ $item['title'] }}</h3></div>
                                    <div class="col-md-5"><x-frontend.rich-text :html="$item['description']" /></div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('vision-mission')
            <section class="section-space surface-dark">
                <div class="container"><div class="row g-5">
                    <article class="col-md-6">
                        <p class="eyebrow">Our vision</p>
                        <h2 class="mission-title">{{ $content['vision_title'] ?? '' }}</h2>
                        <x-frontend.rich-text :html="$content['vision'] ?? ''" />
                    </article>
                    <article class="col-md-6">
                        <p class="eyebrow">Our mission</p>
                        <h2 class="mission-title">{{ $content['mission_title'] ?? '' }}</h2>
                        <x-frontend.rich-text :html="$content['mission'] ?? ''" />
                    </article>
                </div></div>
            </section>
            @break

        @case('statistics')
            @if ($aboutFigures->isNotEmpty())
                <section class="section-space surface-charcoal">
                    <div class="container">
                        <h2 class="eyebrow">{{ $section->heading }}</h2>
                        <div class="row g-0 stats-grid">
                            @foreach ($aboutFigures as $figure)
                                <div class="col-6 col-lg-3 stat">
                                    <strong>{{ $figure['value'] }}{{ filled($figure['unit'] ?? null) ? ' '.$figure['unit'] : '' }}</strong>
                                    <span>{{ $figure['title'] }}</span>
                                    <small>Diverifikasi <time datetime="{{ $figure['verified_at'] }}">{{ \Illuminate\Support\Carbon::parse($figure['verified_at'])->format('d M Y') }}</time></small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
            @break

        @case('clients')
            @if ($aboutClients->isNotEmpty())
                <section class="section-space surface-dark">
                    <div class="container">
                        <p class="eyebrow">{{ $content['eyebrow'] ?? '' }}</p>
                        <x-frontend.section-heading>{{ $section->heading }}</x-frontend.section-heading>
                        @if ($aboutClients->whereNotNull('media_url')->isNotEmpty())
                            <div class="row g-3 mt-4">
                                @foreach ($aboutClients->whereNotNull('media_url') as $client)
                                    <div class="col-6 col-lg"><div class="client-slot">
                                        <img class="client-logo" src="{{ $client['media_url'] }}" alt="{{ ($client['alt'] ?? null) ?: $client['client_name'] }}" loading="lazy" decoding="async">
                                    </div></div>
                                @endforeach
                            </div>
                        @endif
                        @php($feedback = $aboutClients->filter(fn ($item) => filled($item['feedback'] ?? null) || filled($item['rating'] ?? null)))
                        @if ($feedback->isNotEmpty())
                            <div class="row g-4 mt-4 rating-placeholders">
                                @foreach ($feedback as $client)
                                    <div class="col-md-6">
                                        <p>{{ $client['client_name'] }} @if(filled($client['rating'] ?? null))<span>{{ $client['rating'] }}</span>@endif</p>
                                        @if(filled($client['feedback'] ?? null))<blockquote>{{ $client['feedback'] }}</blockquote>@endif
                                        <div class="feedback-rule"></div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
            @break

        @case('leadership')
            @if ($aboutLeadership->isNotEmpty())
                <section class="section-space surface-paper">
                    <div class="container">
                        @foreach ($aboutLeadership as $leader)
                            <div class="row g-5 align-items-center">
                                @if ($leader['photo_url'])
                                    <div class="col-md-6"><img class="leadership-photo" src="{{ $leader['photo_url'] }}" alt="{{ $leader['photo_alt'] }}" width="900" height="600" loading="lazy" decoding="async"></div>
                                @endif
                                <div class="{{ $leader['photo_url'] ? 'col-md-6' : 'col-12' }}">
                                    <p class="eyebrow">{{ $content['eyebrow'] ?? 'Leadership' }}</p>
                                    <x-frontend.section-heading>{{ $section->heading }}</x-frontend.section-heading>
                                    <h3>{{ $leader['name'] }}</h3><p>{{ $leader['position'] }}</p>
                                    <x-frontend.rich-text :html="$leader['biography'] ?: $leader['summary']" />
                                    <a class="text-link" href="{{ route('team') }}">Meet our professionals <span aria-hidden="true">&rarr;</span></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
            @break

        @case('closing-cta')
            @section('about-closing-cta')
            @include('frontend.about.contact-cta', [
                'ctaEyebrow' => $content['eyebrow'] ?? 'Legal consultation',
                'ctaHeading' => $section->heading,
                'ctaLabel' => $content['button_label'] ?? 'Contact our team',
                'ctaUrl' => ($content['button_url'] ?? null) ?: route('contact').'#enquiry',
            ])
            @endsection
            @break
    @endswitch
@endforeach
