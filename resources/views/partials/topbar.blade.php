@php
    use App\Models\Pengguna;

    $pengguna = auth()->user();
    $peranAktif = request()->routeIs('pencari.*') ? 'pencari'
        : (request()->routeIs('perusahaan.*') ? 'perusahaan'
        : (request()->routeIs('verifikator.*') ? 'verifikator'
        : (request()->routeIs('admin.*') ? 'admin' : null)));

    // [nama route, pola route aktif, ikon, label]
    $menu = match ($peranAktif) {
        'pencari' => [
            ['pencari.dashboard', 'pencari.dashboard', 'bi-grid', 'Ringkasan'],
            ['pencari.lowongan.index', 'pencari.lowongan.*', 'bi-search', 'Cari Lowongan'],
            ['pencari.lamaran.index', 'pencari.lamaran.*', 'bi-send', 'Lamaran'],
            ['pencari.keahlian.index', 'pencari.keahlian.*', 'bi-patch-check', 'Keahlian'],
            ['pencari.sertifikat.index', 'pencari.sertifikat.*', 'bi-file-earmark-text', 'Sertifikat'],
        ],
        'perusahaan' => [
            ['perusahaan.dashboard', 'perusahaan.dashboard', 'bi-grid', 'Ringkasan'],
            ['perusahaan.lowongan.index', 'perusahaan.lowongan.*|perusahaan.pelamar.*', 'bi-briefcase', 'Lowongan & Pelamar'],
            ['perusahaan.profil.edit', 'perusahaan.profil.*', 'bi-building', 'Profil Perusahaan'],
        ],
        'verifikator' => [
            ['verifikator.dashboard', 'verifikator.dashboard|verifikator.periksa.*', 'bi-inbox', 'Antrean'],
            ['verifikator.riwayat.index', 'verifikator.riwayat.*', 'bi-clock-history', 'Riwayat'],
            ['verifikator.kewenangan', 'verifikator.kewenangan', 'bi-shield-check', 'Kewenangan'],
        ],
        'admin' => [
            ['admin.dashboard', 'admin.dashboard', 'bi-grid', 'Ringkasan'],
            ['admin.pengguna.index', 'admin.pengguna.*', 'bi-people', 'Pengguna'],
            ['admin.verifikator.index', 'admin.verifikator.*', 'bi-person-check', 'Verifikator'],
            ['admin.perusahaan.index', 'admin.perusahaan.*', 'bi-building', 'Perusahaan'],
            ['admin.keahlian.index', 'admin.keahlian.*', 'bi-patch-check', 'Keahlian'],
            ['admin.jenis-bukti.index', 'admin.jenis-bukti.*', 'bi-tags', 'Jenis Bukti'],
        ],
        default => [
            ['beranda', 'beranda', 'bi-house', 'Beranda'],
            ['lowongan.index', 'lowongan.*', 'bi-search', 'Cari Lowongan'],
        ],
    };
@endphp
<header class="topbar">
    <div class="wrap">
        <a class="brand" href="{{ $pengguna ? route($pengguna->ruteDashboard($peranAktif)) : route('beranda') }}" aria-label="SIBUKER, halaman utama">
            <span class="brand-mark"><i class="bi bi-check2" aria-hidden="true"></i></span>
            <span>SIBUKER</span>
        </a>

        <nav class="nav" aria-label="Navigasi utama">
            @foreach ($menu as [$rute, $pola, $ikon, $label])
                <a href="{{ route($rute) }}" @class(['aktif' => request()->routeIs(...explode('|', $pola))]) @if(request()->routeIs(...explode('|', $pola))) aria-current="page" @endif>
                    <i class="bi {{ $ikon }}" aria-hidden="true"></i>{{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="top-actions">
            @auth
                @php $daftarPeran = $pengguna->daftarPeran(); @endphp
                @if (! $peranAktif && $daftarPeran)
                    <a href="{{ route($pengguna->ruteDashboard()) }}" class="btn btn-sm btn-line">Ke panel</a>
                @endif
                <details class="menu">
                    <summary>
                        @include('partials.avatar', ['orang' => $pengguna])
                        <span class="nama">{{ $peranAktif ? Pengguna::PERAN[$peranAktif] : \Illuminate\Support\Str::words($pengguna->nama, 1, '') }}</span>
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <div class="menu-panel">
                        <div class="menu-head">
                            <div style="font-weight:500">{{ $pengguna->nama }}</div>
                            <div class="muted small">{{ $pengguna->email }}</div>
                        </div>
                        @if (count($daftarPeran) > 1)
                            <div class="menu-label">Masuk sebagai</div>
                            @foreach ($daftarPeran as $p)
                                <a href="{{ route($p . '.dashboard') }}" @class(['aktif' => $p === $peranAktif])>
                                    <i class="bi {{ $p === $peranAktif ? 'bi-record-circle' : 'bi-circle' }}" aria-hidden="true"></i>{{ Pengguna::PERAN[$p] }}
                                </a>
                            @endforeach
                            <hr>
                        @endif
                        @if (in_array('pencari', $daftarPeran))
                            <a href="{{ route('pencari.profil.edit') }}"><i class="bi bi-person" aria-hidden="true"></i>Profil saya</a>
                        @endif
                        <a href="{{ route('lowongan.index') }}"><i class="bi bi-globe2" aria-hidden="true"></i>Halaman publik</a>
                        <hr>
                        <form action="{{ route('keluar') }}" method="POST">
                            @csrf
                            <button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Keluar</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('masuk') }}" class="btn btn-sm btn-ghost">Masuk</a>
                <a href="{{ route('daftar') }}" class="btn btn-sm btn-primary">Daftar</a>
            @endauth
        </div>
    </div>
</header>
