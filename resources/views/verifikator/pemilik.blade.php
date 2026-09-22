@extends('layouts.app')

@section('title', 'Sertifikat ' . $pengguna->nama)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('verifikator.dashboard') }}" class="back"><i class="bi bi-arrow-return-left" aria-hidden="true"></i> Antrean pemilik</a>

<div class="page-head workspace-intro">
    <div class="row" style="gap:18px;align-items:flex-start">
        @include('partials.avatar', ['orang' => $pengguna, 'ukuran' => 'lg'])
        <div>
            <span class="eyebrow">SERTIFIKAT PEMILIK</span>
            <h1>{{ $pengguna->nama }}</h1>
            <p class="sub">{{ $pencari->headline ?: 'Pencari kerja' }}@if($pencari->lokasi) &middot; {{ $pencari->lokasi }}@endif</p>
        </div>
    </div>
    <div class="page-actions"><span class="pill t-indigo">{{ $jumlahMenunggu }} menunggu pemeriksaan</span></div>
</div>

<div class="panel" style="margin-bottom:24px">
    <div class="panel-head"><h2>Keahlian aktif</h2><span class="muted small">{{ $klaim->count() }} keahlian</span></div>
    <div class="panel-body">
        <div class="wrap-gap">
            @foreach ($klaim as $k)
                @include('partials.keahlian', ['k' => $k])
            @endforeach
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div><h2>Semua sertifikat</h2><p class="muted small" style="margin-top:4px">Sertifikat yang masih menunggu ditampilkan paling atas.</p></div>
        <span class="muted small">{{ $sertifikat->count() }} sertifikat</span>
    </div>
    @if ($sertifikat->isEmpty())
        <div class="empty"><i class="bi bi-file-earmark-text"></i><p>Belum ada sertifikat yang diunggah.</p></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Sertifikat</th><th>Jenis</th><th>Terbit</th><th>Diunggah</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($sertifikat as $b)
                    <tr>
                        <td><div class="utama"><i class="bi bi-file-earmark-pdf" style="color:var(--rose)" aria-hidden="true"></i> {{ $b->judul }}</div><div class="sekunder">{{ $b->penerbit ?: 'Penerbit tidak diisi' }}</div></td>
                        <td>{{ $b->jenisBukti->nama }}</td>
                        <td class="muted">{{ Format::tanggal($b->tanggal_terbit) }}</td>
                        <td class="muted">{{ Format::tanggal($b->diunggah_pada, true) }}</td>
                        <td>@include('partials.status', ['status' => $b->status_terakhir, 'kosong' => 'Baru'])</td>
                        <td class="aksi"><a href="{{ route('verifikator.periksa.create', $b) }}" @class(['btn', 'btn-sm', 'btn-primary' => $b->perluDiperiksa(), 'btn-line' => ! $b->perluDiperiksa()])>{{ $b->perluDiperiksa() ? 'Periksa' : 'Lihat detail' }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
