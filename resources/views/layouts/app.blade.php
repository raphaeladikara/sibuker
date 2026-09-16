<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif SIBUKER-PT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/sibuker.css') }}?v={{ filemtime(public_path('css/sibuker.css')) }}">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='4' fill='%232f33c8'/%3E%3Cpath d='M9 16.5l4.5 4.5L23 11' fill='none' stroke='white' stroke-width='3.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
</head>
<body>
<!--
THESIS: bursa kerja tempat keahlian terbukti (centang biru) jadi informasi utama, bukan ijazah. Menolak dashboard sidebar + kartu statistik besar yang biasa dipakai sistem informasi kampus.
OWN-WORLD: referensi FINDIT dari pengguna. Dasar abu dingin #f4f5f9, permukaan putih sudut 3px, indigo #2f33c8 sebagai satu-satunya warna aksi, tag pastel, nav atas dengan garis aktif 2px, strip pencarian bersegmen, satu kartu unggulan indigo pekat. Roboto.
STORY: pencari melihat skor kecocokan dan alasan tiap syarat; perusahaan melihat klaim mana yang terverifikasi; verifikator memutus dari pratinjau PDF.
FIRST VIEWPORT: nav atas, strip pencarian penuh lebar, lalu grid kartu lowongan dengan kartu teratas berwarna indigo.
FORM: pinned by brief (referensi pengguna), tidak ada seed roll.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance
-->
@include('partials.topbar')
@yield('searchbar')
<main class="page">
    @include('partials.flash')
    @yield('content')
</main>
@include('partials.footer')
<script src="{{ asset('js/sibuker.js') }}?v={{ filemtime(public_path('js/sibuker.js')) }}" defer></script>
</body>
</html>
