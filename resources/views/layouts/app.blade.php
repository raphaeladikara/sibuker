<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif SIBUKER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/sibuker.css') }}?v={{ filemtime(public_path('css/sibuker.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('images/sibuker-logo.png') }}">
</head>
<body class="app-shell">
<a class="skip-link" href="#konten">Langsung ke konten</a>

@include('partials.topbar')
@yield('intro')
@yield('searchbar')
<main class="page" id="konten" tabindex="-1">
    @include('partials.flash')
    @yield('content')
</main>
@include('partials.footer')
<script src="{{ asset('js/sibuker.js') }}?v={{ filemtime(public_path('js/sibuker.js')) }}" defer></script>
</body>
</html>
