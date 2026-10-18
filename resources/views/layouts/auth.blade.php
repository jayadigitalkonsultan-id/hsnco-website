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
</head>
<body class="access-page">
    <main class="access-card">
        <a href="{{ route('home') }}"><img src="{{ asset('admin-assets/images/logo-dark.png') }}" alt="HSNCO" width="172" height="62"></a>
        <p class="overline mt-4">HSNCO / Ruang admin</p>
        <h1>@yield('title')</h1>
        <p>@yield('intro')</p>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
