<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Temukan peluang kerja berdasarkan keahlian. Bangun profil, verifikasi sertifikat, dan lihat kecocokanmu bersama SIBUKER.">
    <title>SIBUKER · Keahlian nyata, peluang bermakna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%230a66c2'/%3E%3Cpath d='M9 16l5 5 9-11' fill='none' stroke='white' stroke-width='3'/%3E%3C/svg%3E">
</head>
<body class="landing">
    <a class="skip-link" href="#konten">Langsung ke konten</a>
    <header class="site-header">
        <div class="container header-inner">
            <a class="wordmark" href="{{ route('beranda') }}" aria-label="SIBUKER, beranda"><span class="brand-mark" aria-hidden="true"><i class="bi bi-check-lg"></i></span>SIBUKER</a>
            <nav class="desktop-nav" aria-label="Navigasi utama"><a href="#cara-kerja">Cara kerja</a><a href="#lowongan">Pilihan lowongan</a><a href="#perusahaan">Untuk perusahaan</a></nav>
            <div class="header-actions">
                @auth
                    <a class="button button-blue" href="{{ route(auth()->user()->ruteDashboard()) }}">Ke dashboard <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                @else
                    <a class="login-link" href="{{ route('masuk') }}">Masuk</a>
                    <a class="button button-blue" href="{{ route('daftar') }}">Daftar <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                @endauth
            </div>
            <details class="mobile-nav"><summary aria-label="Buka navigasi"><i class="bi bi-list" aria-hidden="true"></i></summary><nav aria-label="Navigasi ponsel"><a href="#cara-kerja">Cara kerja</a><a href="#lowongan">Pilihan lowongan</a><a href="#perusahaan">Untuk perusahaan</a><a href="{{ route('lowongan.index') }}">Semua lowongan</a></nav></details>
        </div>
    </header>
    <main id="konten">@yield('content')</main>
    <footer class="container site-footer"><div><a class="wordmark" href="{{ route('beranda') }}">SIBUKER</a><p>Keahlian nyata. Peluang bermakna.</p></div><a href="#konten">Kembali ke atas <i class="bi bi-arrow-up" aria-hidden="true"></i></a></footer>
    <script>
        const mobileMenu = document.querySelector('.mobile-nav');
        mobileMenu.addEventListener('click', (event) => {
            if (event.target.closest('a')) mobileMenu.open = false;
        });
        document.addEventListener('click', (event) => {
            if (!mobileMenu.contains(event.target)) mobileMenu.open = false;
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mobileMenu.open) {
                mobileMenu.open = false;
                mobileMenu.querySelector('summary').focus();
            }
        });
    </script>
</body>
</html>
