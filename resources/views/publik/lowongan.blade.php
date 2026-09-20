@extends('layouts.app')

@section('title', 'Cari Lowongan')

@section('intro')
<section class="catalog-intro wrap" aria-labelledby="catalog-title">
    <div><span class="eyebrow">TEMUKAN LANGKAH BERIKUTNYA</span><h1 id="catalog-title">Peluang yang sejalan<br>dengan <span>keahlianmu.</span></h1><p>Jelajahi lowongan, kenali persyaratannya, dan temukan tempat untuk berkembang.</p></div>
    <div class="catalog-note"><i class="bi bi-patch-check-fill" aria-hidden="true"></i><div><strong>Keahlianmu punya tempat.</strong><p>Sertifikat terverifikasi memberi perusahaan alasan lebih untuk percaya.</p></div></div>
</section>
@endsection

@section('searchbar')
    @include('partials.searchbar')
@endsection

@section('content')
<div class="cols cols-filter">
    <form method="GET" action="{{ route('lowongan.index') }}" class="filter" id="form-filter">
        <input type="hidden" name="q" value="{{ request('q') }}">
        <input type="hidden" name="lokasi" value="{{ request('lokasi') }}">
        <h2>Filter</h2>

        <details class="filter-group" open>
            <summary>Tipe pekerjaan <i class="bi bi-chevron-up" aria-hidden="true"></i></summary>
            <div class="checks">
                @foreach (\App\Support\Format::TIPE_PEKERJAAN as $nilai => $teks)
                    <label class="check">
                        <input type="checkbox" name="tipe[]" value="{{ $nilai }}" @checked(in_array($nilai, $tipe)) onchange="this.form.submit()">
                        {{ $teks }}
                        <span class="count">{{ $jumlahTipe[$nilai] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>
        </details>

        <details class="filter-group" open>
            <summary>Keahlian <i class="bi bi-chevron-up" aria-hidden="true"></i></summary>
            <select name="keahlian" class="select" onchange="this.form.submit()" aria-label="Keahlian yang disyaratkan">
                <option value="">Semua keahlian</option>
                @foreach ($keahlian as $k)
                    <option value="{{ $k->id }}" @selected(request('keahlian') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
        </details>

        <details class="filter-group" open>
            <summary>Centang biru <i class="bi bi-chevron-up" aria-hidden="true"></i></summary>
            <label class="check">
                <input type="checkbox" name="verifikasi" value="tidak_wajib" @checked(request('verifikasi') === 'tidak_wajib') onchange="this.form.submit()">
                Tidak mewajibkan keahlian terverifikasi
            </label>
            <p class="hint" style="margin-top:8px">Cocok kalau kamu belum punya sertifikat.</p>
        </details>

        <noscript><button class="btn btn-primary btn-block">Terapkan</button></noscript>
        @if (request()->hasAny(['q', 'lokasi', 'tipe', 'keahlian', 'verifikasi']))
            <a href="{{ route('lowongan.index') }}" class="btn btn-ghost btn-sm">Hapus semua filter</a>
        @endif
    </form>

    <div>
        <div class="toolbar">
            <div>
                <h2>Lowongan tersedia</h2>
                <div class="count">
                    {{ $lowongan->total() }} lowongan dibuka
                    @if (request('q')) untuk &ldquo;{{ request('q') }}&rdquo; @endif
                </div>
            </div>
            @auth
                @if (auth()->user()->pencariKerja)
                    <span class="pill t-indigo">Skor dihitung dari keahlianmu</span>
                @endif
            @endauth
        </div>

        @if ($lowongan->isEmpty())
            <div class="panel empty">
                <i class="bi bi-search"></i>
                <p>Tidak ada lowongan yang cocok dengan filter ini. Coba kurangi filternya.</p>
                <a href="{{ route('lowongan.index') }}" class="btn btn-line btn-sm">Tampilkan semua</a>
            </div>
        @else
            @php
                // Kartu indigo: skor tertinggi bila pencari login, selain itu lowongan paling baru di halaman pertama.
                $unggulanId = $cocok
                    ? collect($cocok)->sortByDesc('skor')->keys()->first()
                    : ($lowongan->onFirstPage() ? $lowongan->first()->id : null);
            @endphp
            <div class="job-grid" style="padding-top:12px">
                @foreach ($lowongan as $l)
                    @include('partials.job-card', [
                        'l' => $l,
                        'unggulan' => $l->id === $unggulanId,
                        'label' => $cocok ? 'Paling cocok' : 'Terbaru',
                        'cocok' => $cocok[$l->id] ?? null,
                    ])
                @endforeach
            </div>
            {{ $lowongan->links('partials.paginasi') }}
        @endif
    </div>
</div>
@endsection
