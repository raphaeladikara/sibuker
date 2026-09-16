@extends('layouts.app')

@section('searchbar')
    @include('partials.searchbar')
@endsection

@section('content')
<section class="hero">
    <div>
        <h1>Cari kerja pakai keahlian yang bisa dibuktikan.</h1>
        <p class="lead">Cantumkan keahlianmu, dengan atau tanpa sertifikat. Kalau sertifikatnya sudah diperiksa verifikator, keahlian itu dapat centang biru, dan perusahaan bisa langsung melihat mana yang sudah terbukti.</p>
        <div class="row">
            <a href="{{ route('daftar') }}" class="btn btn-primary">Buat akun pencari kerja</a>
            <a href="{{ route('lowongan.index') }}" class="btn btn-line">Lihat {{ $statistik['lowongan'] }} lowongan</a>
        </div>
    </div>

    {{-- Contoh tampilan profil: begini keahlian terlihat oleh perusahaan. --}}
    <div class="panel profil-demo" aria-label="Contoh profil pencari kerja">
        <div class="row">
            <span class="avatar lg rona-1" aria-hidden="true">DP</span>
            <div>
                <div style="font-weight:600;font-size:15px">Dimas Prasetyo</div>
                <div class="muted">Data Analyst &middot; Surabaya</div>
            </div>
        </div>
        <hr>
        <div class="muted small" style="margin-bottom:10px">Keahlian</div>
        <div class="wrap-gap">
            <span class="chip">SQL <span class="lvl">Mahir</span><i class="bi bi-patch-check-fill"></i></span>
            <span class="chip">Python <span class="lvl">Menengah</span><i class="bi bi-patch-check-fill"></i></span>
            <span class="chip">Visualisasi Data <span class="lvl">Mahir</span></span>
        </div>
        <hr>
        <div class="row-between small">
            <span class="muted">Junior Data Analyst, PT Nusantara Data Solusi</span>
            <b class="num">83% cocok</b>
        </div>
        <div class="meter" style="margin-top:8px"><span style="width:83%"></span></div>
        <p class="muted small" style="margin-top:10px">Visualisasi Data belum terverifikasi, padahal lowongan ini memintanya.</p>
    </div>
</section>

<section class="panel">
    <dl class="strip" style="box-shadow:none;margin:0">
        <div><dt>Lowongan dibuka</dt><dd>{{ $statistik['lowongan'] }}</dd></div>
        <div><dt>Perusahaan aktif</dt><dd>{{ $statistik['perusahaan'] }}</dd></div>
        <div><dt>Keahlian bercentang biru</dt><dd>{{ $statistik['terverifikasi'] }}</dd></div>
        <div><dt>Jenis keahlian</dt><dd>{{ $statistik['keahlian'] }}</dd></div>
    </dl>
</section>

<section class="block">
    <div class="block-head">
        <div>
            <h2>Lowongan terbaru</h2>
            @if ($keahlianPopuler->isNotEmpty())
                <div class="wrap-gap" style="margin-top:12px">
                    @foreach ($keahlianPopuler as $k)
                        <a href="{{ route('lowongan.index', ['keahlian' => $k->id]) }}" class="chip">{{ $k->nama }}</a>
                    @endforeach
                </div>
            @endif
        </div>
        <a href="{{ route('lowongan.index') }}">Semua lowongan <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>

    @if ($lowongan->isEmpty())
        <div class="panel empty"><i class="bi bi-briefcase"></i><p>Belum ada lowongan yang dibuka.</p></div>
    @else
        <div class="job-grid" style="padding-top:12px">
            @foreach ($lowongan as $l)
                @include('partials.job-card', ['l' => $l, 'unggulan' => $loop->first, 'label' => 'Terbaru'])
            @endforeach
        </div>
    @endif
</section>

<section class="block">
    <div class="block-head"><h2>Dari klaim sampai centang biru</h2></div>
    <div class="panel langkah">
        <div>
            <div class="no">1</div>
            <h3>Cantumkan keahlian</h3>
            <p>Pilih dari daftar keahlian dan tentukan levelmu sendiri, dari pemula sampai ahli.</p>
        </div>
        <div>
            <div class="no">2</div>
            <h3>Unggah sertifikat PDF</h3>
            <p>Opsional. Satu sertifikat bisa membuktikan beberapa keahlian sekaligus.</p>
        </div>
        <div>
            <div class="no">3</div>
            <h3>Diperiksa verifikator</h3>
            <p>Asesor sesuai bidangnya mengecek keaslian dan menandai keahlian yang terbukti.</p>
        </div>
        <div>
            <div class="no">4</div>
            <h3>Lamar dengan skor</h3>
            <p>Sistem membandingkan keahlianmu dengan syarat lowongan dan menyimpan skornya.</p>
        </div>
    </div>
</section>
@endsection
