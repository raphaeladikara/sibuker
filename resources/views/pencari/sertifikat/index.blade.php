@extends('layouts.app')

@section('title', 'Sertifikat')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Sertifikat</h1>
        <p class="sub">Berkas PDF disimpan di server. Verifikator yang berwenang atas keahlianmu akan memeriksanya.</p>
    </div>
    <div class="page-actions"><a href="{{ route('pencari.sertifikat.create') }}" class="btn btn-primary"><i class="bi bi-upload"></i> Unggah sertifikat</a></div>
</div>

<div class="panel">
    @if ($bukti->isEmpty())
        <div class="empty">
            <i class="bi bi-file-earmark-text"></i>
            <p>Belum ada sertifikat. Tanpa sertifikat keahlianmu tetap tampil, hanya tanpa centang biru.</p>
            <a href="{{ route('pencari.sertifikat.create') }}" class="btn btn-primary btn-sm">Unggah PDF</a>
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Sertifikat</th><th>Jenis</th><th>Terbit</th><th>Diunggah</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($bukti as $b)
                    <tr>
                        <td>
                            <div class="utama"><a href="{{ route('pencari.sertifikat.show', $b->id) }}"><i class="bi bi-file-earmark-pdf" style="color:var(--rose)"></i> {{ $b->judul }}</a></div>
                            <div class="sekunder">{{ $b->penerbit ?: 'Penerbit tidak diisi' }}</div>
                        </td>
                        <td>{{ $b->jenisBukti->nama }}</td>
                        <td class="muted">{{ Format::tanggal($b->tanggal_terbit) }}</td>
                        <td class="muted">{{ Format::tanggal($b->diunggah_pada) }}</td>
                        <td>@include('partials.status', ['status' => $b->status_terakhir, 'kosong' => 'Di antrean'])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
