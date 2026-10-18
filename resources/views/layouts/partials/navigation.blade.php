<ul class="navbar-nav ms-auto align-items-lg-center">
    @foreach ($navigationItems as $item)
        @php($isActive = request()->routeIs($item['route']))
        <li class="nav-item">
            <a class="nav-link {{ $isActive ? 'active' : '' }}" @if ($isActive) aria-current="page" @endif href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
        </li>
    @endforeach
    <li class="nav-item ms-lg-2">
        @guest
            <a class="btn btn-outline-brand" href="{{ route('login') }}">Masuk</a>
        @else
            <a class="btn btn-outline-brand" href="{{ route('admin.dashboard') }}">Dashboard</a>
        @endguest
    </li>
    <li class="nav-item ms-lg-2">
        <a class="btn btn-outline-brand" href="{{ route('contact') }}#enquiry">Jadwalkan konsultasi <span aria-hidden="true">↗</span></a>
    </li>
</ul>
