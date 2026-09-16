@extends('layouts.app')

@section('title', $lowongan->posisi)

@section('searchbar')
    @include('partials.searchbar')
@endsection

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('lowongan.index') }}" class="back"><i class="bi bi-arrow-return-left" aria-hidden="true"></i> Kembali</a>

<div class="cols cols-3">
    <aside class="stack kiri">
        @if ($cocok)
            <div class="panel cta">
                <h2>Kecocokan keahlianmu</h2>
                <div class="skor-besar">{{ Format::skor($cocok['skor']) }}<small>%</small></div>
                <div @class(['meter', 'hijau' => $cocok['memenuhi_wajib']]) style="margin:12px 0 18px"><span style="width:{{ $cocok['skor'] }}%"></span></div>
                <ul class="cek-list">
                    @foreach ($cocok['rincian'] as $r)
                        <li>
                            <i class="bi {{ $r->ok ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i>
                            <span>{{ $r->syarat->keahlian->nama }}</span>
                            <span class="ket">{{ $r->ok ? '' : $r->alasan }}</span>
                        </li>
                    @endforeach
                </ul>
                <div style="margin-top:20px">
                    @if ($lamaran)
                        <a href="{{ route('pencari.lamaran.show', $lamaran) }}" class="btn btn-line btn-block">Sudah dilamar &middot; lihat status</a>
                    @elseif ($lowongan->status === 'dipublikasikan')
                        <form action="{{ route('pencari.lowongan.lamar', $lowongan) }}" method="POST" data-confirm="Kirim lamaran dengan skor {{ Format::skor($cocok['skor']) }}%? Skor ini disimpan dan tidak berubah walau keahlianmu bertambah nanti.">
                            @csrf
                            <button class="btn btn-primary btn-block">Lamar sekarang</button>
                        </form>
                        @unless ($cocok['memenuhi_wajib'])
                            <p class="hint" style="margin-top:10px">Masih ada syarat wajib yang belum terpenuhi. Kamu tetap boleh melamar.</p>
                        @endunless
                    @endif
                </div>
            </div>
        @elseauth
            <div class="panel cta">
                <h2>Melamar butuh profil pencari kerja</h2>
                <p>Akun yang kamu pakai sekarang tidak punya profil pencari kerja.</p>
            </div>
        @else
            <div class="panel cta">
                <h2>Lihat seberapa cocok kamu</h2>
                <p>Masuk sebagai pencari kerja untuk melihat skor kecocokan dan melamar lowongan ini.</p>
                <a href="{{ route('masuk') }}" class="btn btn-primary btn-block">Masuk untuk melamar</a>
                <p class="hint" style="margin-top:12px">Belum punya akun? <a href="{{ route('daftar') }}">Daftar</a></p>
            </div>
        @endif
    </aside>

    <article class="panel">
        <div class="banner">@include('partials.logo', ['perusahaan' => $lowongan->perusahaan])</div>
        <div class="article">
            <div class="article-head">
                <div>
                    <h1>{{ $lowongan->posisi }}</h1>
                    <div class="by"><span style="color:var(--indigo);font-weight:500">{{ $lowongan->perusahaan->nama }}</span> &middot; {{ $lowongan->lokasi ?: 'Lokasi belum diisi' }}</div>
                </div>
                <div class="article-side">
                    <div class="row" style="justify-content:flex-end;margin-bottom:10px">
                        <button type="button" class="icon-btn" data-salin="{{ route('lowongan.show', $lowongan) }}" title="Salin tautan" aria-label="Salin tautan lowongan"><i class="bi bi-link-45deg"></i></button>
                    </div>
                    @if ($lowongan->dipublikasikan_pada)
                        Dibuka {{ Format::tanggal($lowongan->dipublikasikan_pada) }} &middot;
                    @endif
                    {{ $lowongan->lamaran_count }} pelamar
                </div>
            </div>

            @if ($lowongan->status !== 'dipublikasikan')
                <div class="flash info" style="margin-top:18px"><i class="bi bi-eye-slash"></i><div>Lowongan ini berstatus {{ Format::label($lowongan->status) }} dan tidak tampil untuk publik.</div></div>
            @endif

            <dl class="meta-row">
                <div><dt>Kode</dt><dd>{{ $lowongan->kode }}</dd></div>
                <div><dt>Tipe</dt><dd>{{ Format::tipe($lowongan->tipe_pekerjaan) }}</dd></div>
                <div><dt>Syarat</dt><dd>{{ $lowongan->syarat->count() }} keahlian</dd></div>
                @php $wajibCentang = $lowongan->syarat->where('wajib_terverifikasi', true)->count(); @endphp
                <div><dt>Centang biru</dt><dd>{{ $wajibCentang ? $wajibCentang . ' wajib' : 'Tidak wajib' }}</dd></div>
            </dl>

            <h2 class="section-title">Tentang pekerjaan</h2>
            <p class="prose">{{ $lowongan->deskripsi }}</p>

            <h2 class="section-title" style="margin-top:30px">Syarat keahlian</h2>
            <p class="muted small" style="margin-bottom:8px">Tidak ada syarat ijazah. Bobot menentukan seberapa besar pengaruh tiap syarat ke skor.</p>
            <ul class="syarat-list">
                @foreach ($lowongan->syarat as $s)
                    <li>
                        <span class="kotak" aria-hidden="true"></span>
                        <div>
                            <div class="nama">
                                {{ $s->keahlian->nama }}
                                @if ($s->wajib_terverifikasi)<i class="bi bi-patch-check-fill centang" title="Harus terverifikasi" aria-label="harus terverifikasi"></i>@endif
                            </div>
                            <div class="ket">
                                Minimal {{ Format::level($s->level_minimum) }} &middot; bobot {{ rtrim(rtrim(number_format($s->bobot, 2, ',', '.'), '0'), ',') }}
                                @if ($s->wajib_terverifikasi) &middot; harus terverifikasi @endif
                            </div>
                        </div>
                        <span class="pill {{ $s->sifat === 'wajib' ? 't-rose' : '' }}">{{ Format::label($s->sifat) }}</span>
                    </li>
                @endforeach
            </ul>

            <h2 class="section-title" style="margin-top:30px">Perusahaan</h2>
            <div class="row" style="align-items:flex-start;gap:14px">
                @include('partials.logo', ['perusahaan' => $lowongan->perusahaan])
                <div>
                    <div style="font-weight:600">{{ $lowongan->perusahaan->nama }}</div>
                    @if ($lowongan->perusahaan->deskripsi)<p class="muted" style="margin-top:4px">{{ $lowongan->perusahaan->deskripsi }}</p>@endif
                    <div class="small muted" style="margin-top:6px">
                        @if ($lowongan->perusahaan->alamat)<i class="bi bi-geo-alt"></i> {{ $lowongan->perusahaan->alamat }}@endif
                        @if ($lowongan->perusahaan->situs_web) &middot; <a href="{{ $lowongan->perusahaan->situs_web }}" target="_blank" rel="noopener">{{ parse_url($lowongan->perusahaan->situs_web, PHP_URL_HOST) }}</a>@endif
                    </div>
                </div>
            </div>
        </div>
    </article>

    <aside>
        <h2 class="side-title">Lowongan lain</h2>
        @forelse ($lainnya as $l)
            <a href="{{ route('lowongan.show', $l) }}" class="mini-job">
                @include('partials.logo', ['perusahaan' => $l->perusahaan])
                <div>
                    <div class="t">{{ $l->posisi }}</div>
                    <div class="c">{{ $l->perusahaan->nama }}</div>
                </div>
                <div class="r">{{ Format::tipe($l->tipe_pekerjaan) }}<br>{{ Format::tanggal($l->dipublikasikan_pada) }}</div>
            </a>
        @empty
            <p class="muted">Belum ada lowongan lain.</p>
        @endforelse
    </aside>
</div>
@endsection
