@extends('layouts.app')

@section('title', 'Riwayat')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Riwayat pemeriksaan</h1>
        <p class="sub">Riwayat tidak pernah ditimpa. Status centang biru selalu memakai pemeriksaan terbaru tiap sertifikat.</p>
    </div>
    <div class="page-actions">
        <div class="segmented">
            @foreach (['' => 'Semua', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'menunggu' => 'Ditunda'] as $n => $t)
                <a href="{{ route('verifikator.riwayat.index', array_filter(['keputusan' => $n])) }}" style="height:38px;padding:0 14px;display:inline-flex;align-items:center;font-size:13px;font-weight:500;border-right:1px solid var(--line);text-decoration:none;{{ request('keputusan', '') === $n ? 'background:var(--indigo);color:#fff' : 'color:var(--ink-2)' }}">{{ $t }}</a>
            @endforeach
        </div>
    </div>
</div>

<div class="panel">
    @if ($riwayat->isEmpty())
        <div class="empty"><i class="bi bi-clock-history"></i><p>Belum ada pemeriksaan dengan filter ini.</p></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Waktu</th><th>Sertifikat</th><th>Keahlian terbukti</th><th>Keputusan</th><th>Berlaku sampai</th></tr></thead>
                <tbody>
                @foreach ($riwayat as $v)
                    <tr>
                        <td class="muted" style="white-space:nowrap">{{ Format::tanggal($v->diverifikasi_pada ?? $v->dibuat_pada, true) }}</td>
                        <td>
                            <div class="utama">{{ $v->bukti->judul }}</div>
                            <div class="sekunder">{{ $v->bukti->pengguna->nama }}@if ($v->catatan) &middot; {{ $v->catatan }}@endif</div>
                        </td>
                        <td>
                            <div class="wrap-gap">
                                @forelse ($v->klaimKeahlian as $k)
                                    <span class="chip">{{ $k->keahlian->nama }} <i class="bi bi-patch-check-fill"></i></span>
                                @empty
                                    <span class="muted">-</span>
                                @endforelse
                            </div>
                        </td>
                        <td>@include('partials.status', ['status' => $v->keputusan])</td>
                        <td class="muted">{{ Format::tanggal($v->berlaku_sampai) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
{{ $riwayat->links('partials.paginasi') }}
@endsection
