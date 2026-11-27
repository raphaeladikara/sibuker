{{-- Kerangka panel dengan sidebar untuk halaman analitik. Pola tampilannya mengikuti dashboard
     HRMS TelkoNusa (repo hrm-iak): sidebar yang bisa diciutkan, bar atas, dan kartu. --}}
@php
    $pengguna = auth()->user();
    $inisial = collect(explode(' ', $pengguna->nama))->filter()->take(2)->map(fn ($k) => mb_substr($k, 0, 1))->implode('');
    $grup = [
        'Analitik' => [
            ['admin.analitik', 'bi-bar-chart-line', 'Pasar kerja'],
        ],
        'Kelola' => [
            ['admin.dashboard', 'bi-grid', 'Ringkasan sistem'],
            ['admin.pengguna.index', 'bi-people', 'Pengguna'],
            ['admin.verifikator.index', 'bi-person-check', 'Verifikator'],
            ['admin.perusahaan.index', 'bi-building', 'Perusahaan'],
            ['admin.keahlian.index', 'bi-patch-check', 'Keahlian'],
            ['admin.jenis-bukti.index', 'bi-tags', 'Jenis bukti'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · SIBUKER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}?v={{ filemtime(public_path('css/panel.css')) }}">
    <link rel="icon" type="image/png" href="{{ asset('images/sibuker-logo.png') }}">
    <script>if (localStorage.getItem('sibuker-sidebar') === 'ciut') document.documentElement.classList.add('ciut-awal');</script>
</head>
<body>
<a class="sr lompat" href="#konten">Langsung ke konten</a>
<div class="app" id="app">
    <aside class="side" aria-label="Navigasi panel">
        <div class="brand">
            <span class="logo"><img src="{{ asset('images/sibuker-logo.png') }}" alt=""></span>
            <span class="brand-t"><b>SIBUKER</b><span>Panel administrator</span></span>
            <button class="collapse" type="button" id="tombol-ciut" aria-label="Ciutkan sidebar" aria-expanded="true">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>
        </div>
        <nav class="nav">
            @foreach ($grup as $judul => $menu)
                <div class="grp-h"><span>{{ $judul }}</span></div>
                @foreach ($menu as [$rute, $ikon, $label])
                    <a class="item" href="{{ route($rute) }}" title="{{ $label }}" @if (request()->routeIs($rute)) aria-current="page" @endif>
                        <i class="bi {{ $ikon }}"></i><span class="lbl">{{ $label }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="side-foot">
            <div class="me">
                <span class="av">{{ $inisial }}</span>
                <span class="me-t"><b>{{ $pengguna->nama }}</b><span>{{ $pengguna->email }}</span></span>
            </div>
            <form method="POST" action="{{ route('keluar') }}">
                @csrf
                <button class="item" type="submit" title="Keluar"><i class="bi bi-box-arrow-left"></i><span class="lbl">Keluar</span></button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="top">
            <div class="top-t">
                <h1>@yield('judul')</h1>
                <div class="sub">@yield('subjudul')</div>
            </div>
            <span class="sp"></span>
            @yield('aksi-atas')
        </header>
        <main class="content" id="konten" tabindex="-1">
            @yield('content')
        </main>
    </div>
</div>
<div class="tip" role="tooltip" hidden></div>
<script src="{{ asset('js/panel.js') }}?v={{ filemtime(public_path('js/panel.js')) }}" defer></script>
</body>
</html>
