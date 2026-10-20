<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title') — HSNCO</title>
    <link rel="stylesheet" href="{{ asset('admin-assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/laravel.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/auth.css') }}">
    <script src="{{ asset('admin-assets/js/auth.js') }}" defer></script>
</head>
<body class="auth-page">
    <a class="skip-link" href="#auth-main">Langsung ke formulir</a>
    <div class="auth-shell">
        <aside class="auth-brand" aria-label="HSNCO">
            <a class="auth-logo" href="{{ route('home') }}" aria-label="Beranda HSNCO"><img src="{{ asset('assets/images/logo-white.png') }}" alt="HSNCO" width="172" height="62"></a>
            <div class="auth-brand-copy"><span class="auth-eyebrow">HSNCO / PORTAL ADMINISTRASI</span><h2>Ketelitian dalam <br>setiap <em>langkah.</em></h2><p>Ruang kerja untuk mengelola informasi dan menjaga kualitas kehadiran digital HSNCO.</p><div class="auth-brand-rule" aria-hidden="true"></div><span class="auth-brand-caption">TERSTRUKTUR. KONSISTEN. PROFESIONAL.</span></div>
            <p class="auth-brand-footer">Akses terbatas untuk pengelola yang berwenang.</p>
        </aside>
        <div class="auth-workspace">
            <a class="auth-back" href="{{ route('home') }}"><span aria-hidden="true">←</span> Kembali ke website</a>
            <main class="auth-form-panel" id="auth-main" tabindex="-1">
                <p class="auth-eyebrow">SELAMAT DATANG DI HSNCO</p>
                <h1>@yield('title')</h1>
                <p class="auth-intro">@yield('intro')</p>
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert"><p class="fw-semibold mb-1">Periksa kembali informasi Anda.</p><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
            <footer class="auth-footer"><span>&copy; {{ date('Y') }} HSNCO</span><span>Portal pengelolaan website</span></footer>
        </div>
    </div>
</body>
</html>
