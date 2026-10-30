<footer class="site-footer footer-editorial">
    <div class="container">
        <div class="row g-5 align-items-start footer-main">
            <div class="col-lg-5">
                <a class="footer-brand" href="{{ route('home') }}"><img class="brand-logo" src="{{ $logo }}" alt="{{ $name }} ? Home" width="270" height="118" loading="lazy"></a>
                <p class="footer-tagline">{{ $tagline }}</p>
                <a class="footer-contact-action footer-linkedin" href="https://www.linkedin.com/company/hsncoatlaw/" target="_blank" rel="noopener noreferrer" aria-label="HSNCO on LinkedIn (opens in a new tab)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M20.45 2H3.55C2.69 2 2 2.68 2 3.52v16.96c0 .84.69 1.52 1.55 1.52h16.9c.86 0 1.55-.68 1.55-1.52V3.52c0-.84-.69-1.52-1.55-1.52ZM7.93 18.75H4.98V9.2h2.95v9.55ZM6.46 7.9a1.71 1.71 0 1 1 0-3.42 1.71 1.71 0 0 1 0 3.42ZM19 18.75h-2.94V14.1c0-1.11-.02-2.54-1.55-2.54-1.55 0-1.79 1.21-1.79 2.46v4.73H9.78V9.2h2.82v1.3h.04c.39-.74 1.35-1.52 2.78-1.52 2.97 0 3.58 1.95 3.58 4.49v5.28Z"/></svg>
                    <span>LinkedIn</span>
                </a>
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
            <div class="footer-access"><a class="footer-admin-link" href="{{ route('privacy-policy') }}">Privacy Policy</a><span>&copy; {{ now()->year }} {{ $name }}. All rights reserved.</span>
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
