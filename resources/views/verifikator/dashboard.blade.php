@extends('layouts.app')

@section('title', 'Antrean')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head workspace-intro">
    <div>
        <span class="eyebrow">KEAHLIAN YANG BISA DIPERCAYA</span>
            <h1>Antrean pemeriksaan</h1>
        <p class="sub">{{ $verifikator->pengguna->nama }} &middot; {{ $verifikator->instansi }}. Hanya sertifikat dari pemilik yang punya keahlian di bidang kewenanganmu yang tampil di sini.</p>
    </div>
</div>

<dl class="strip">
    <div><dt>Sertifikat menunggu</dt><dd @class(['perlu' => $antrean->count()])>{{ $antrean->count() }}</dd></div>
    <div><dt>Pemilik dalam antrean</dt><dd>{{ $antreanPemilik->count() }}</dd></div>
    <div><dt>Disetujui</dt><dd>{{ $keputusan['disetujui'] ?? 0 }}</dd></div>
    <div><dt>Bidang kewenangan</dt><dd>{{ $verifikator->kewenangan->count() }}</dd></div>
</dl>

<div class="panel">
    <div class="panel-head">
        <div><h2>Pemilik sertifikat</h2><p class="muted small" style="margin-top:4px">Pilih nama untuk melihat semua sertifikat yang pernah diunggah.</p></div>
    </div>
    @if ($antreanPemilik->isEmpty())
        <div class="empty"><i class="bi bi-inbox"></i><p>Antrean kosong. Sertifikat baru di bidangmu akan muncul di sini.</p></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Pemilik</th><th>Profil</th><th>Keahlian di bidangmu</th><th>Menunggu</th><th>Antrean sejak</th><th></th></tr></thead>
                <tbody>
                @foreach ($antreanPemilik as $orang)
                    <tr>
                        <td>
                            <div class="row" style="gap:12px">
                                @include('partials.avatar', ['orang' => $orang->pengguna])
                                <div><a class="utama" href="{{ route('verifikator.pemilik.show', $orang->pengguna) }}">{{ $orang->pengguna->nama }}</a><div class="sekunder">{{ $orang->pengguna->email }}</div></div>
                            </div>
                        </td>
                        <td><div class="utama">{{ $orang->pencari->headline ?: 'Pencari kerja' }}</div><div class="sekunder">{{ $orang->pencari->lokasi ?: 'Lokasi belum diisi' }}</div></td>
                        <td>
                            <div class="wrap-gap">
                                @foreach ($orang->klaim as $k)
                                    <span class="chip">{{ $k->keahlian->nama }} <span class="lvl">{{ Format::level($k->level_klaim) }}</span></span>
                                @endforeach
                            </div>
                        </td>
                        <td><span class="pill t-indigo">{{ $orang->jumlah_menunggu }} sertifikat</span></td>
                        <td class="muted">{{ Format::tanggal($orang->terlama, true) }}</td>
                        <td class="aksi"><a href="{{ route('verifikator.pemilik.show', $orang->pengguna) }}" class="btn btn-sm btn-primary">Lihat sertifikat</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
