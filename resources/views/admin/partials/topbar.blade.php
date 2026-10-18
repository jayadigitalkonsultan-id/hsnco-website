<header class="admin-topbar">
    <div class="topbar-start"><button class="icon-button d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-expanded="false" aria-label="Buka navigasi" type="button"><x-admin.icon name="menu" /></button><span class="topbar-workspace">Workspace <span>/</span> <strong>HSNCO</strong></span></div>
    <div class="topbar-actions">
        <a class="topbar-website" href="{{ route('home') }}" target="_blank" rel="noopener">Lihat website <x-admin.icon name="external" /></a>
        <div class="account-button"><span class="avatar">{{ $preview ? 'H' : \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'H', 0, 2)) }}</span><span class="account-copy"><strong>{{ $preview ? 'Pratinjau lokal' : auth()->user()->name }}</strong><small>{{ $preview ? 'Hanya baca' : (['super_admin' => 'Super Administrator', 'admin' => 'Administrator', 'editor' => 'Editor'][auth()->user()->role->value] ?? 'Pengguna') }}</small></span></div>@unless($preview)<form method="post" action="{{ route('logout') }}">@csrf<button class="btn btn-link btn-sm">Keluar</button></form>@endunless
    </div>
</header>

