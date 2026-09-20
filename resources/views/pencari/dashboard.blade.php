@extends('layouts.app')

@section('title', 'Ringkasan')

@section('content')
@php
    use App\Support\Format;
    $jmlVerif = $klaim->where('terverifikasi', true)->count();
    $jmlAntre = $bukti->filter->perluDiperiksa()->count();
    $lamaranAktif = $lamaran->reject->sudahFinal()->count();
@endphp

<section class="workspace-intro">
    <div><span class="eyebrow">RUANG KARIERMU</span><h1>Langkah kecil hari ini.<br>Peluang baru esok hari.</h1><p>Kelola keahlian, pantau lamaran, dan temukan kesempatan berikutnya.</p></div>
    <a href="{{ route('pencari.lowongan.index') }}" class="btn btn-primary">Temukan lowongan <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
</section>

<div class="cols cols-left">
    <aside class="stack">
        <div class="panel">
            <div class="panel-body">
                <div class="row" style="gap:14px">
                    @include('partials.avatar', ['orang' => $profil->pengguna, 'ukuran' => 'lg'])
                    <div style="min-width:0">
                        <div style="font-weight:600;font-size:16px">{{ $profil->pengguna->nama }}</div>
                        <div class="muted">{{ $profil->headline ?: 'Headline belum diisi' }}</div>
                        @if ($profil->lokasi)<div class="muted small"><i class="bi bi-geo-alt"></i> {{ $profil->lokasi }}</div>@endif
                    </div>
                </div>
                @if (! $profil->headline || ! $profil->ringkasan)
                    <a href="{{ route('pencari.profil.edit') }}" class="btn btn-line btn-sm btn-block" style="margin-top:18px">Lengkapi profil</a>
                @endif
            </div>
            <div class="panel-head" style="border-top:1px solid var(--line)">
                <h2>Keahlian</h2>
                <a href="{{ route('pencari.keahlian.index') }}">Kelola</a>
            </div>
            <div class="panel-body">
                @if ($klaim->isEmpty())
                    <p class="muted">Belum ada keahlian. Lowongan menilai kandidat dari sini, jadi mulai dari keahlian yang paling kamu kuasai.</p>
                    <a href="{{ route('pencari.keahlian.create') }}" class="btn btn-primary btn-sm" style="margin-top:14px">Tambah keahlian</a>
                @else
                    <div class="wrap-gap">
                        @foreach ($klaim->where('aktif', true) as $k)
                            @include('partials.keahlian', ['k' => $k])
                        @endforeach
                    </div>
                    <p class="muted small" style="margin-top:14px">{{ $jmlVerif }} dari {{ $klaim->where('aktif', true)->count() }} keahlian bercentang biru.</p>
                @endif
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h2>Sertifikat</h2>
                <a href="{{ route('pencari.sertifikat.create') }}">Unggah</a>
            </div>
            @if ($bukti->isEmpty())
                <div class="panel-body muted">Belum ada sertifikat. Unggah PDF supaya keahlianmu bisa diverifikasi.</div>
            @else
                <ul class="list">
                    @foreach ($bukti->take(4) as $b)
                        <li>
                            <div class="isi">
                                <a href="{{ route('pencari.sertifikat.show', $b->id) }}" class="judul" title="{{ $b->judul }}">{{ $b->judul }}</a>
                                <div class="ket">{{ $b->penerbit }}</div>
                            </div>
                            @include('partials.status', ['status' => $b->status_terakhir])
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </aside>

    <div>
        <div class="page-head">
            <div>
                <h2>Halo, {{ \Illuminate\Support\Str::words($profil->pengguna->nama, 1, '') }}</h2>
                <p class="sub">Skor di bawah dihitung ulang setiap kali halaman dibuka, dari keahlian dan centang birumu saat ini.</p>
            </div>
            <div class="page-actions"><a href="{{ route('pencari.lowongan.index') }}" class="btn btn-primary"><i class="bi bi-search"></i> Cari lowongan</a></div>
        </div>

        <dl class="strip">
            <div><dt>Keahlian</dt><dd>{{ $klaim->where('aktif', true)->count() }}</dd></div>
            <div><dt>Bercentang biru</dt><dd>{{ $jmlVerif }}</dd></div>
            <div><dt>Sertifikat di antrean</dt><dd @class(['perlu' => $jmlAntre])>{{ $jmlAntre }}</dd></div>
            <div><dt>Lamaran berjalan</dt><dd>{{ $lamaranAktif }}</dd></div>
        </dl>

        <div class="block-head" style="margin-top:8px">
            <h2 style="font-size:17px">Lowongan yang belum kamu lamar</h2>
            <a href="{{ route('pencari.lowongan.index') }}">Lihat semua</a>
        </div>
        @if ($rekomendasi->isEmpty())
            <div class="panel empty"><i class="bi bi-briefcase"></i><p>Semua lowongan yang dibuka sudah kamu lamar.</p></div>
        @else
            <div class="job-grid" style="padding-top:12px">
                @foreach ($rekomendasi as $r)
                    @include('partials.job-card', ['l' => $r->lowongan, 'unggulan' => $loop->first && $r->skor > 0, 'label' => 'Paling cocok', 'cocok' => ['skor' => $r->skor, 'memenuhi_wajib' => $r->memenuhi_wajib]])
                @endforeach
            </div>
        @endif

        <div class="panel" style="margin-top:28px">
            <div class="panel-head"><h2>Lamaran terakhir</h2><a href="{{ route('pencari.lamaran.index') }}">Semua lamaran</a></div>
            @if ($lamaran->isEmpty())
                <div class="panel-body muted">Kamu belum melamar lowongan apa pun.</div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Posisi</th><th>Dilamar</th><th class="kanan">Skor</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($lamaran->take(5) as $l)
                            <tr>
                                <td>
                                    <div class="utama"><a href="{{ route('pencari.lamaran.show', $l) }}">{{ $l->lowongan->posisi }}</a></div>
                                    <div class="sekunder">{{ $l->lowongan->perusahaan->nama }}</div>
                                </td>
                                <td class="muted">{{ Format::tanggal($l->dilamar_pada) }}</td>
                                <td class="kanan">{{ Format::skor($l->skor_kecocokan) }}%</td>
                                <td>@include('partials.status', ['status' => $l->status])</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
