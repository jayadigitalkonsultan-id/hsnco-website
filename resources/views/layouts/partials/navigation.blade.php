<ul class="navbar-nav ms-auto align-items-lg-center">
    @foreach ($navigationItems as $item)
        @php($isActive = (request()->routeIs($item['route']) || ($item['route'] === 'industries' && request()->routeIs('industries.show'))))
        <li class="nav-item">
            <a class="nav-link {{ $isActive ? 'active' : '' }}" @if ($isActive) aria-current="page" @endif href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
        </li>
    @endforeach
</ul>
