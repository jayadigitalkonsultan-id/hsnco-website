<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ruang pengelolaan website HSNCO."><meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#151718"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — HSNCO Admin</title>
    <link rel="icon" href="{{ asset('admin-assets/images/favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('admin-assets/fonts/manrope.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/laravel.css') }}">
    <script defer src="{{ asset('admin-assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script defer src="{{ asset('admin-assets/plugins-init/bootstrap-init.js') }}"></script>
    <script defer src="{{ asset('admin-assets/js/app.js') }}"></script>
    @stack('styles')
    @stack('scripts')
</head>
<body>
    @php($preview = request()->attributes->get('admin_preview', false))
    <a class="skip-link" href="#main">Langsung ke konten</a>
    @include('admin.partials.sidebar')
    <div class="admin-shell">
        @include('admin.partials.topbar')
        <main id="main" class="admin-main" tabindex="-1">
            @if($preview)<div class="preview-notice" role="status"><x-admin.icon name="lock" /><span><strong>Pratinjau lokal</strong> — Anda dapat meninjau konten. Perubahan memerlukan akun dan verifikasi OTP email.</span></div>@endif
            @include('admin.partials.notifications')
            <div class="page-heading">
                <div><nav aria-label="Breadcrumb"><ol class="admin-breadcrumb"><li><a href="{{ route('admin.dashboard') }}">HSNCO Admin</a></li><li aria-current="page">@yield('title', 'Dashboard')</li></ol></nav>
                <h1>@yield('title', 'Dashboard')</h1><p class="page-intro">@yield('intro')</p></div>
                <div class="heading-actions">@yield('actions')</div>
            </div>
            @yield('content')
        </main>
        @include('admin.partials.footer')
    </div>
    <div class="modal fade" id="deleteConfirmation" tabindex="-1" aria-labelledby="deleteConfirmationTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="deleteConfirmationTitle">Arsipkan konten?</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><p id="deleteConfirmationText"></p><p class="text-muted mb-0">Konten dipindahkan ke arsip penghapusan dan tidak dihapus permanen.</p></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger" id="confirmDeleteButton" type="button">Ya, arsipkan</button></div></div></div>
    </div>
</body>
</html>

