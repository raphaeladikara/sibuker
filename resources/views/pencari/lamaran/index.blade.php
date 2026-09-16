@extends('layouts.app')

@section('title', 'Lamaran')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Lamaran</h1>
        <p class="sub">Satu lowongan hanya bisa dilamar sekali. Skor yang tampil adalah skor saat kamu melamar.</p>
    </div>
</div>

<div class="panel">
    @if ($lamaran->isEmpty())
        <div class="empty">
            <i class="bi bi-send"></i>
            <p>Kamu belum melamar lowongan apa pun.</p>
            <a href="{{ route('pencari.lowongan.index') }}" class="btn btn-primary btn-sm">Cari lowongan</a>
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Lowongan</th><th>Dilamar</th><th class="kanan">Skor</th><th>Tahap terakhir</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($lamaran as $l)
                    @php $tahap = $l->tahapSeleksi->whereIn('status', ['berlangsung', 'lulus', 'tidak_lulus'])->last(); @endphp
                    <tr>
                        <td>
                            <div class="row" style="gap:12px">
                                @include('partials.logo', ['perusahaan' => $l->lowongan->perusahaan])
                                <div>
                                    <div class="utama"><a href="{{ route('pencari.lamaran.show', $l) }}">{{ $l->lowongan->posisi }}</a></div>
                                    <div class="sekunder">{{ $l->lowongan->perusahaan->nama }} &middot; {{ $l->lowongan->kode }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="muted">{{ Format::tanggal($l->dilamar_pada) }}</td>
                        <td class="kanan">{{ Format::skor($l->skor_kecocokan) }}%</td>
                        <td>{{ $tahap?->nama_tahap ?? '-' }}</td>
                        <td>@include('partials.status', ['status' => $l->status])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
