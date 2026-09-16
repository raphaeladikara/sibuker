@extends('layouts.app')

@section('title', 'Antrean')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Antrean pemeriksaan</h1>
        <p class="sub">{{ $verifikator->pengguna->nama }} &middot; {{ $verifikator->instansi }}. Hanya sertifikat dari pemilik yang punya keahlian di bidang kewenanganmu yang tampil di sini.</p>
    </div>
</div>

<dl class="strip">
    <div><dt>Menunggu keputusan</dt><dd @class(['perlu' => $antrean->count()])>{{ $antrean->count() }}</dd></div>
    <div><dt>Disetujui</dt><dd>{{ $keputusan['disetujui'] ?? 0 }}</dd></div>
    <div><dt>Ditolak</dt><dd>{{ $keputusan['ditolak'] ?? 0 }}</dd></div>
    <div><dt>Bidang kewenangan</dt><dd>{{ $verifikator->kewenangan->count() }}</dd></div>
</dl>

<div class="panel">
    @if ($antrean->isEmpty())
        <div class="empty"><i class="bi bi-inbox"></i><p>Antrean kosong. Sertifikat baru di bidangmu akan muncul di sini.</p></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Sertifikat</th><th>Pemilik</th><th>Keahlian di bidangmu</th><th>Diunggah</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach ($antrean as $b)
                    <tr>
                        <td>
                            <div class="utama"><i class="bi bi-file-earmark-pdf" style="color:var(--rose)"></i> {{ $b->judul }}</div>
                            <div class="sekunder">{{ $b->jenisBukti->nama }} &middot; {{ $b->penerbit ?: '-' }}</div>
                        </td>
                        <td>{{ $b->pengguna->nama }}</td>
                        <td>
                            <div class="wrap-gap">
                                @foreach ($b->pengguna->pencariKerja->klaimKeahlian as $k)
                                    <span class="chip">{{ $k->keahlian->nama }} <span class="lvl">{{ Format::level($k->level_klaim) }}</span></span>
                                @endforeach
                            </div>
                        </td>
                        <td class="muted">{{ Format::tanggal($b->diunggah_pada, true) }}</td>
                        <td>@include('partials.status', ['status' => $b->status_terakhir, 'kosong' => 'Baru'])</td>
                        <td class="aksi"><a href="{{ route('verifikator.periksa.create', $b->id) }}" class="btn btn-sm btn-primary">Periksa</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
