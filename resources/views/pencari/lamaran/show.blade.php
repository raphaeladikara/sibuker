@extends('layouts.app')

@section('title', $lamaran->lowongan->posisi)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('pencari.lamaran.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Lamaran</a>
<div class="page-head">
    <div class="row" style="gap:16px;align-items:flex-start">
        @include('partials.logo', ['perusahaan' => $lamaran->lowongan->perusahaan, 'ukuran' => 'lg'])
        <div>
            <h1>{{ $lamaran->lowongan->posisi }}</h1>
            <p class="sub">{{ $lamaran->lowongan->perusahaan->nama }} &middot; dilamar {{ Format::tanggal($lamaran->dilamar_pada, true) }}</p>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('lowongan.show', $lamaran->lowongan) }}" class="btn btn-line">Lihat lowongan</a>
        @unless ($lamaran->sudahFinal())
            <form action="{{ route('pencari.lamaran.tarik', $lamaran) }}" method="POST" data-confirm="Tarik lamaran ini? Kamu tidak bisa melamar lowongan yang sama lagi.">
                @csrf @method('PATCH')
                <button class="btn btn-danger">Tarik lamaran</button>
            </form>
        @endunless
    </div>
</div>

<div class="cols cols-side">
    <div class="panel">
        <div class="panel-head"><h2>Tahapan seleksi</h2>@include('partials.status', ['status' => $lamaran->status])</div>
        <div class="panel-body">
            @if ($lamaran->tahapSeleksi->isEmpty())
                <p class="muted">Perusahaan belum menjadwalkan tahap seleksi. Status akan berubah di sini begitu mereka meninjau lamaranmu.</p>
            @else
                <ol class="timeline">
                    @foreach ($lamaran->tahapSeleksi as $t)
                        <li>
                            <span class="no {{ $t->status }}">{{ $t->urutan }}</span>
                            <div>
                                <div class="row-between"><span class="judul">{{ $t->nama_tahap }}</span>@include('partials.status', ['status' => $t->status])</div>
                                <div class="ket"><i class="bi bi-calendar3"></i> {{ $t->jadwal ? Format::tanggal($t->jadwal, true) : 'Jadwal belum ditentukan' }}</div>
                                @if ($t->catatan)<div class="catatan">{{ $t->catatan }}</div>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </div>

    <aside class="panel">
        <div class="panel-body">
            <div class="muted small">Skor saat melamar</div>
            <div class="skor-besar" style="margin-top:6px">{{ Format::skor($lamaran->skor_kecocokan) }}<small>%</small></div>
            @if (round($cocok['skor'], 2) !== round((float) $lamaran->skor_kecocokan, 2))
                <p class="hint" style="margin-top:8px">Dengan keahlianmu sekarang skornya {{ Format::skor($cocok['skor']) }}%. Skor lamaran tidak ikut berubah.</p>
            @endif
        </div>
        <div class="panel-head" style="border-top:1px solid var(--line)"><h2>Syarat sekarang</h2></div>
        <div class="panel-body">
            <ul class="cek-list">
                @foreach ($cocok['rincian'] as $r)
                    <li>
                        <i class="bi {{ $r->ok ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                        <span>{{ $r->syarat->keahlian->nama }}</span>
                        <span class="ket">{{ $r->ok ? '' : $r->alasan }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
</div>
@endsection
