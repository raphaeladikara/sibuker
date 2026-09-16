@extends('layouts.app')

@section('title', $bukti->judul)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('pencari.sertifikat.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Sertifikat</a>
<div class="page-head">
    <div>
        <h1>{{ $bukti->judul }}</h1>
        <p class="sub">{{ $bukti->jenisBukti->nama }}@if ($bukti->penerbit) &middot; {{ $bukti->penerbit }}@endif</p>
    </div>
    <div class="page-actions">
        <form action="{{ route('pencari.sertifikat.destroy', $bukti->id) }}" method="POST" data-confirm="Hapus sertifikat ini? Riwayat verifikasi dan centang biru dari sertifikat ini ikut hilang.">
            @csrf @method('DELETE')
            <button class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
        </form>
        <a href="{{ route('pencari.sertifikat.edit', $bukti->id) }}" class="btn btn-line"><i class="bi bi-pencil"></i> Ubah</a>
    </div>
</div>

<div class="cols cols-side">
    <div class="panel" style="overflow:hidden">
        <iframe src="{{ route('berkas.bukti', $bukti->id) }}" class="pdf" title="Pratinjau {{ $bukti->judul }}"></iframe>
    </div>

    <aside class="stack">
        <div class="panel">
            <div class="panel-body">
                <dl class="dl">
                    <dt>Status</dt><dd>@include('partials.status', ['status' => $bukti->status_terakhir, 'kosong' => 'Di antrean'])</dd>
                    <dt>Terbit</dt><dd>{{ Format::tanggal($bukti->tanggal_terbit) }}</dd>
                    <dt>Diunggah</dt><dd>{{ Format::tanggal($bukti->diunggah_pada, true) }}</dd>
                    <dt>Berkas</dt><dd><a href="{{ route('berkas.bukti', $bukti->id) }}" target="_blank" rel="noopener">Buka PDF <i class="bi bi-box-arrow-up-right"></i></a></dd>
                </dl>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Riwayat pemeriksaan</h2></div>
            <div class="panel-body">
                @if ($bukti->verifikasi->isEmpty())
                    <p class="muted">Belum ada pemeriksaan. Sertifikat menunggu di antrean verifikator.</p>
                @else
                    <ol class="timeline">
                        @foreach ($bukti->verifikasi as $v)
                            <li>
                                <span class="no {{ ['disetujui' => 'lulus', 'ditolak' => 'tidak_lulus', 'menunggu' => ''][$v->keputusan] }}">
                                    <i class="bi {{ ['disetujui' => 'bi-check-lg', 'ditolak' => 'bi-x-lg', 'menunggu' => 'bi-hourglass'][$v->keputusan] }}"></i>
                                </span>
                                <div>
                                    <div class="row-between">
                                        <span class="judul">{{ Format::label($v->keputusan) }}</span>
                                        <span class="ket">{{ Format::tanggal($v->diverifikasi_pada ?? $v->dibuat_pada) }}</span>
                                    </div>
                                    <div class="ket">oleh {{ $v->verifikator->pengguna->nama }}</div>
                                    @if ($v->catatan)<div class="catatan">{{ $v->catatan }}</div>@endif
                                    @if ($v->klaimKeahlian->isNotEmpty())
                                        <div class="wrap-gap" style="margin-top:8px">
                                            @foreach ($v->klaimKeahlian as $k)
                                                <span class="chip">{{ $k->keahlian->nama }} <i class="bi bi-patch-check-fill"></i></span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($v->berlaku_sampai)<div class="ket" style="margin-top:6px">Berlaku sampai {{ Format::tanggal($v->berlaku_sampai) }}</div>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
