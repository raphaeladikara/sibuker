@extends('layouts.app')

@section('title', 'Ringkasan')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head workspace-intro">
    <div class="row" style="gap:16px;align-items:flex-start">
        @include('partials.logo', ['perusahaan' => $perusahaan, 'ukuran' => 'lg'])
        <div>
            <span class="eyebrow">RUANG REKRUTMEN</span>
            <h1>{{ $perusahaan->nama }}</h1>
            <p class="sub">
                @if ($perusahaan->nib) NIB {{ $perusahaan->nib }} &middot; @endif
                {{ $perusahaan->alamat ?: 'Alamat belum diisi' }}
            </p>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('perusahaan.lowongan.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat lowongan</a>
    </div>
</div>

@if ($perusahaan->status_perusahaan !== 'aktif')
    <div class="flash gagal"><i class="bi bi-slash-circle"></i><div>Perusahaan ini dinonaktifkan administrator. Lowonganmu tidak tampil untuk publik dan tidak bisa dipublikasikan.</div></div>
@endif

<dl class="strip">
    <div><dt>Lowongan tayang</dt><dd>{{ $ringkasan['dipublikasikan'] }}</dd></div>
    <div><dt>Draft</dt><dd>{{ $ringkasan['draft'] }}</dd></div>
    <div><dt>Total pelamar</dt><dd>{{ $ringkasan['pelamar'] }}</dd></div>
    <div><dt>Belum dibuka</dt><dd @class(['perlu' => $ringkasan['perlu_ditinjau']])>{{ $ringkasan['perlu_ditinjau'] }}</dd></div>
</dl>

<div class="cols cols-side">
    <div class="panel">
        <div class="panel-head"><h2>Lowongan</h2><a href="{{ route('perusahaan.lowongan.index') }}">Kelola semua</a></div>
        @if ($lowongan->isEmpty())
            <div class="empty">
                <i class="bi bi-briefcase"></i>
                <p>Belum ada lowongan. Buat draft dulu, tambahkan syarat keahlian, lalu publikasikan.</p>
                <a href="{{ route('perusahaan.lowongan.create') }}" class="btn btn-primary btn-sm">Buat lowongan</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Posisi</th><th>Status</th><th class="kanan">Syarat</th><th class="kanan">Pelamar</th></tr></thead>
                    <tbody>
                    @foreach ($lowongan as $l)
                        <tr>
                            <td>
                                <div class="utama"><a href="{{ route('perusahaan.lowongan.show', $l) }}">{{ $l->posisi }}</a></div>
                                <div class="sekunder">{{ $l->kode }} &middot; {{ Format::tipe($l->tipe_pekerjaan) }}</div>
                            </td>
                            <td>@include('partials.status', ['status' => $l->status])</td>
                            <td class="kanan">{{ $l->syarat_count }}</td>
                            <td class="kanan">
                                @if ($l->lamaran_count)
                                    <a href="{{ route('perusahaan.pelamar.index', $l) }}">{{ $l->lamaran_count }}</a>
                                @else
                                    <span class="muted">0</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <aside class="panel">
        <div class="panel-head"><h2>Pelamar terbaru</h2></div>
        @if ($lamaran->isEmpty())
            <div class="panel-body muted">Belum ada lamaran masuk.</div>
        @else
            <ul class="list">
                @foreach ($lamaran as $l)
                    <li>
                        @include('partials.avatar', ['orang' => $l->pencariKerja->pengguna])
                        <div class="isi">
                            <a href="{{ route('perusahaan.pelamar.show', $l) }}" class="judul">{{ $l->pencariKerja->pengguna->nama }}</a>
                            <div class="ket">{{ $l->lowongan->posisi }} &middot; {{ Format::skor($l->skor_kecocokan) }}%</div>
                        </div>
                        @include('partials.status', ['status' => $l->status])
                    </li>
                @endforeach
            </ul>
        @endif
    </aside>
</div>
@endsection
