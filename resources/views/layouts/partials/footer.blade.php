<footer class="site-footer footer-editorial">
    <div class="container">
        <div class="row g-5 align-items-start footer-main">
            <div class="col-lg-5">
                <a class="footer-brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ $logo }}" alt="{{ $name }} ? Home" width="270" height="118" loading="lazy"></a>
                <p class="footer-tagline">{{ $tagline }}</p>
            </div>
            <div class="col-lg-6 offset-lg-1 footer-office">
                <h2>Our office</h2>
                @if ($footerContactDetails['address'])<p class="footer-address">{{ $footerContactDetails['address'] }}</p>@endif
                <div class="footer-contact-links">
                    @if ($footerContactDetails['phone'])<a href="tel:{{ $footerContactDetails['phone_href'] }}">{{ $footerContactDetails['phone'] }}</a>@endif
                    @if ($footerContactDetails['email'])<a href="mailto:{{ $footerContactDetails['email'] }}">{{ $footerContactDetails['email'] }}</a>@endif
                </div>
                <a class="footer-contact-action" href="{{ route('contact') }}">Contact our team <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>
        <nav class="footer-navigation" aria-label="Footer navigation">
            <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            @foreach ($footerFirmLinks as $item)
                <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('practice-areas') }}" @if(request()->routeIs('practice-areas')) aria-current="page" @endif>Practice Areas</a>
            <a href="{{ route('industries') }}" @if(request()->routeIs('industries*')) aria-current="page" @endif>Industries</a>
            <a href="{{ route('clients') }}" @if(request()->routeIs('clients')) aria-current="page" @endif>Our Clients</a>
        </nav>
        <div class="footer-bottom">
            <div class="footer-access"><span>&copy; {{ now()->year }} {{ $name }}. All rights reserved.</span>
                @guest
                    <a class="footer-admin-link" href="{{ route('login') }}">Admin Portal</a>
                @else
                    <a class="footer-admin-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                @endguest
            </div>
            <p class="footer-disclaimer">The information on this website is general in nature and does not constitute legal advice.</p>
        </div>
    </div>
</footer>
