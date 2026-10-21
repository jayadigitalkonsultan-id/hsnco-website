<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-12 col-lg-4">
                <a href="{{ route('home') }}"><img class="brand-logo" src="{{ $logo }}" alt="Home {{ $name }}" width="130" height="80"></a>
                <p class="footer-tagline">{{ $tagline }}</p>
            </div>
            <div class="col-6 col-lg-2 footer-column">
                <h2>The Firm</h2>
                @foreach ($footerFirmLinks as $item)
                    <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('billing-fees') }}" @if(request()->routeIs('billing-fees')) aria-current="page" @endif>Fees &amp; Engagements</a>
                <a href="{{ route('clients') }}" @if(request()->routeIs('clients')) aria-current="page" @endif>Our Clients</a>
                <a href="{{ route('industries') }}" @if(request()->routeIs('industries*')) aria-current="page" @endif>Industries</a>
            </div>
            <div class="col-6 col-lg-3 footer-column">
                <h2>Expertise</h2>
                @forelse ($practiceAreas->take(3) as $practice)
                    <a href="{{ route('practice-areas') }}#{{ $practice->slug }}">{{ $practice->name }}</a>
                @empty
                    <a href="{{ route('practice-areas') }}">Practice Areas</a>
                @endforelse
            </div>
            <div class="col-12 col-lg-3 footer-column">
                <h2>Start a conversation</h2>
                <a href="{{ route('contact') }}">Contact {{ $name }} <span aria-hidden="true">↗</span></a>
                @if ($footerContactDetails['address'])<p>{{ $footerContactDetails['address'] }}</p>@endif
                @if ($footerContactDetails['phone'])<a href="tel:{{ $footerContactDetails['phone_href'] }}">{{ $footerContactDetails['phone'] }}</a>@endif
                @if ($footerContactDetails['email'])<a href="mailto:{{ $footerContactDetails['email'] }}">{{ $footerContactDetails['email'] }}</a>@endif
                @if (! $footerContactDetails['address'] && ! $footerContactDetails['phone'] && ! $footerContactDetails['email'])
                    <p>Our address and direct contact details will be available once verified.</p>
                @endif
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-access"><span>&copy; {{ now()->year }} {{ $name }}.</span>
                @guest
                    <a class="footer-admin-link" href="{{ route('login') }}">Admin Portal</a>
                @else
                    <a class="footer-admin-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                @endguest
            </div>
            <span>The information on this website is general in nature and does not constitute legal advice.</span>
        </div>
    </div>
</footer>
