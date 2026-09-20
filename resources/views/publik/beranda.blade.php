@extends('layouts.landing')

@section('content')
@php $mulai = auth()->check() ? route(auth()->user()->ruteDashboard()) : route('daftar'); @endphp
<div class="container">
    @if (session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="notice notice-error" role="alert">{{ session('error') }}</div>@endif
    <section class="career-hero" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="live-dot"></span> PELUANG BARU, DIMULAI DARI KAMU</p>
            <h1 id="hero-title">Bawa keahlianmu.<br>Temukan <span>peluangmu.</span></h1>
            <p class="hero-description">Karier yang tepat dimulai dari apa yang kamu bisa. Buktikan keahlianmu dan temukan perusahaan yang menghargainya.</p>
            <div class="hero-actions"><a href="{{ $mulai }}" class="button button-blue">{{ auth()->check() ? 'Buka dashboard' : 'Mulai perjalananmu' }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a><a href="{{ route('lowongan.index') }}" class="text-link">Jelajahi lowongan <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
            <p class="hero-note"><i class="bi bi-check-circle" aria-hidden="true"></i> Punya keahlian? Kamu punya kesempatan.</p>
        </div>
        <div class="hero-art">
            <span class="art-orbit" aria-hidden="true"></span><span class="art-spark" aria-hidden="true">✳</span>
            <figure class="bag-frame"><img src="{{ asset('images/landing/briefcase.png') }}" width="1672" height="941" fetchpriority="high" alt="Tas kerja kulit cokelat, siap menemani langkah karier berikutnya"></figure>
            <div class="proof-card"><span class="proof-icon"><i class="bi bi-patch-check-fill" aria-hidden="true"></i></span><div><small>CONTOH KEAHLIAN</small><strong>SQL · Terverifikasi</strong><span>Kemampuan yang bisa dibuktikan.</span></div></div>
            <div class="career-sticker"><i class="bi bi-arrow-up-right" aria-hidden="true"></i><span>Langkah baru.<br><b>Versi terbaikmu.</b></span></div>
            <span class="art-caption">YOUR SKILLS. YOUR NEXT CHAPTER.</span>
        </div>
    </section>
</div>
<div class="principles" aria-label="Prinsip SIBUKER-PT"><div class="container principles-inner"><span>Keahlian jadi pembeda</span><i class="bi bi-asterisk" aria-hidden="true"></i><span>Verifikasi yang transparan</span><i class="bi bi-asterisk" aria-hidden="true"></i><span>Peluang untuk berkembang</span><i class="bi bi-asterisk" aria-hidden="true"></i></div></div>

<section class="container section benefits" id="cara-kerja" aria-labelledby="benefits-title">
    <div class="section-heading"><div><p class="eyebrow">LEBIH DARI SEKADAR CV</p><h2 id="benefits-title">Kemampuanmu punya cerita.<br>Biar peluang yang menemukannya.</h2></div><p>Kamu membawa keahlian.<br>Kami membantu membuatnya terlihat.</p></div>
    <div class="benefit-grid">
        <article class="benefit benefit-profile"><div class="benefit-top"><span class="icon-square"><i class="bi bi-person-badge" aria-hidden="true"></i></span><span class="step-number">01 / KENALI</span></div><h3>Mulai dari yang<br>kamu kuasai.</h3><p>Bangun profil dengan keahlian dan levelmu. Sertifikat opsional, kesempatan tetap terbuka.</p><div class="skill-preview" aria-label="Contoh profil keahlian"><div class="preview-person"><span>DP</span><div><strong>Dimas Prasetyo</strong><small>Contoh profil pencari kerja</small></div></div><div class="skill-tags"><span>SQL <i class="bi bi-patch-check-fill" aria-hidden="true"></i></span><span>Python</span><span>Visualisasi data</span></div></div></article>
        <article class="benefit benefit-proof"><div class="benefit-top"><span class="icon-square"><i class="bi bi-patch-check" aria-hidden="true"></i></span><span class="step-number">02 / BUKTIKAN</span></div><h3>Bukan hanya klaim.<br>Ada buktinya.</h3><p>Unggah sertifikat PDF. Verifikator sesuai bidang memeriksanya untuk memberi centang biru pada keahlianmu.</p><div class="verification-visual" aria-hidden="true"><span class="verification-ring"><i class="bi bi-patch-check-fill"></i></span><span>Keahlian terverifikasi</span></div></article>
        <article class="benefit benefit-match"><div class="benefit-top"><span class="icon-square"><i class="bi bi-crosshair" aria-hidden="true"></i></span><span class="step-number">03 / TEMUKAN</span></div><h3>Peluang tepat.<br>Alasan yang jelas.</h3><p>Lihat kecocokan keahlian dengan syarat lowongan, termasuk apa yang masih perlu kamu lengkapi.</p><div class="match-preview"><div><span>Contoh kecocokan</span><strong>83<small>%</small></strong></div><div class="match-track" aria-hidden="true"><span></span></div><small>Setiap syarat punya penjelasan.</small></div></article>
    </div>
</section>

<section class="jobs-section" id="lowongan" aria-labelledby="jobs-title"><div class="container section">
    <div class="section-heading"><div><p class="eyebrow">LANGKAH BERIKUTNYA ADA DI SINI</p><h2 id="jobs-title">Peluang baru, untuk kamu.</h2></div><a href="{{ route('lowongan.index') }}" class="text-link">Lihat semua lowongan <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
    @if ($keahlianPopuler->isNotEmpty())<div class="explore-skills"><span>Jelajahi keahlian</span>@foreach ($keahlianPopuler->take(5) as $k)<a href="{{ route('lowongan.index', ['keahlian' => $k->id]) }}">{{ $k->nama }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>@endforeach</div>@endif
    <div class="landing-jobs">
        @forelse ($lowongan->take(3) as $l)
            <article class="opportunity">
                <div class="opportunity-top">@include('partials.logo', ['perusahaan' => $l->perusahaan])<span class="employment">{{ \App\Support\Format::tipe($l->tipe_pekerjaan) }}</span></div>
                <p class="company-name">{{ $l->perusahaan->nama }}</p><h3><a href="{{ route('lowongan.show', $l) }}">{{ $l->posisi }}</a></h3>
                <p class="job-location"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $l->lokasi ?: 'Lokasi belum diisi' }}</p>
                <div class="job-skills">@foreach ($l->syarat->take(3) as $s)<span>{{ $s->keahlian->nama }}</span>@endforeach</div>
                <a class="job-detail" href="{{ route('lowongan.show', $l) }}" aria-label="Lihat lowongan {{ $l->posisi }} di {{ $l->perusahaan->nama }}">Lihat peluang <span><i class="bi bi-arrow-up-right" aria-hidden="true"></i></span></a>
            </article>
        @empty
            <div class="jobs-empty"><i class="bi bi-briefcase" aria-hidden="true"></i><h3>Peluang berikutnya sedang disiapkan.</h3><p>Lengkapi profilmu sambil menunggu lowongan baru.</p><a class="text-link" href="{{ $mulai }}">Siapkan profilmu <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
        @endforelse
    </div>
    <p class="jobs-note"><i class="bi bi-info-circle" aria-hidden="true"></i> Jelajahi lowongan tanpa akun. Masuk untuk melihat kecocokanmu dan mulai melamar.</p>
</div></section>

<section class="container section" id="perusahaan" aria-labelledby="company-title"><div class="company-banner"><div><p class="eyebrow">UNTUK PERUSAHAAN</p><h2 id="company-title">Tim hebat dimulai dari<br>keahlian yang tepat.</h2><p>Temukan kandidat berdasarkan kemampuan yang dibutuhkan timmu, dengan bukti keahlian yang dapat diperiksa.</p><a href="{{ auth()->check() ? route(auth()->user()->ruteDashboard()) : route('daftar') }}" class="button button-navy">{{ auth()->check() ? 'Buka dashboard' : 'Mulai sebagai perusahaan' }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div><div class="company-stats"><div><strong>{{ $statistik['perusahaan'] }}</strong><span>perusahaan aktif</span></div><div><strong>{{ $statistik['lowongan'] }}</strong><span>lowongan dibuka</span></div><div><strong>{{ $statistik['keahlian'] }}</strong><span>jenis keahlian</span></div><div><strong>{{ $statistik['terverifikasi'] }}</strong><span>keahlian terverifikasi</span></div></div></div></section>
<section class="container closing" aria-labelledby="closing-title"><div><p class="eyebrow">MASA DEPANMU, MULAI SEKARANG</p><h2 id="closing-title">Keahlian sudah ada.<br>Tinggal langkah pertamanya.</h2></div><a href="{{ $mulai }}" class="button button-blue">{{ auth()->check() ? 'Lanjut ke dashboard' : 'Buat profilmu' }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></section>
@endsection
