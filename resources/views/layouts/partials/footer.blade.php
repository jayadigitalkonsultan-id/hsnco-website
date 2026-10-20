<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-12 col-lg-4">
                <a href="{{ route('home') }}"><img src="{{ $logo }}" alt="Beranda {{ $name }}" width="130" height="80"></a>
                <p class="footer-tagline">{{ $tagline }}</p>
            </div>
            <div class="col-6 col-lg-2 footer-column">
                <h2>Firma</h2>
                @foreach ($footerFirmLinks as $item)
                    <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
                <a href="{{ route('billing-fees') }}" @if(request()->routeIs('billing-fees')) aria-current="page" @endif>Biaya &amp; Kerja Sama</a>
            </div>
            <div class="col-6 col-lg-3 footer-column">
                <h2>Keahlian</h2>
                @forelse ($practiceAreas->take(3) as $practice)
                    <a href="{{ route('practice-areas') }}#{{ $practice->slug }}">{{ $practice->name }}</a>
                @empty
                    <a href="{{ route('practice-areas') }}">Bidang Praktik</a>
                @endforelse
            </div>
            <div class="col-12 col-lg-3 footer-column">
                <h2>Mulai percakapan</h2>
                <a href="{{ route('contact') }}">Hubungi {{ $name }} <span aria-hidden="true">↗</span></a>
                @if ($footerContactDetails['address'])<p>{{ $footerContactDetails['address'] }}</p>@endif
                @if ($footerContactDetails['phone'])<a href="tel:{{ $footerContactDetails['phone_href'] }}">{{ $footerContactDetails['phone'] }}</a>@endif
                @if ($footerContactDetails['email'])<a href="mailto:{{ $footerContactDetails['email'] }}">{{ $footerContactDetails['email'] }}</a>@endif
                @if (! $footerContactDetails['address'] && ! $footerContactDetails['phone'] && ! $footerContactDetails['email'])
                    <p>Alamat dan kontak langsung akan ditampilkan setelah diverifikasi.</p>
                @endif
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-access"><span>&copy; {{ now()->year }} {{ $name }}.</span>
                @guest
                    <a class="footer-admin-link" href="{{ route('login') }}">Portal Admin</a>
                @else
                    <a class="footer-admin-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                @endguest
            </div>
            <span>Informasi pada situs ini bersifat umum dan bukan nasihat hukum.</span>
        </div>
    </div>
</footer>
