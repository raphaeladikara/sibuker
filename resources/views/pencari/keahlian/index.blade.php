@extends('layouts.app')

@section('title', 'Keahlian')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Keahlian</h1>
        <p class="sub">Keahlian boleh dicantumkan tanpa sertifikat. Centang biru muncul setelah sertifikat pendukungnya disetujui verifikator dan masih berlaku.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('pencari.sertifikat.create') }}" class="btn btn-line"><i class="bi bi-upload"></i> Unggah sertifikat</a>
        <a href="{{ route('pencari.keahlian.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah keahlian</a>
    </div>
</div>

<div class="panel">
    @if ($klaim->isEmpty())
        <div class="empty">
            <i class="bi bi-patch-check"></i>
            <p>Belum ada keahlian di profilmu. Skor kecocokan lowongan akan 0% sampai kamu menambahkannya.</p>
            <a href="{{ route('pencari.keahlian.create') }}" class="btn btn-primary btn-sm">Tambah keahlian pertama</a>
        </div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Keahlian</th><th>Level</th><th>Verifikasi</th><th>Di profil</th><th>Ditambahkan</th><th></th></tr></thead>
                <tbody>
                @foreach ($klaim as $k)
                    <tr>
                        <td>
                            <div class="utama">{{ $k->keahlian->nama }}</div>
                            <div class="sekunder">{{ $k->keahlian->kategori }}</div>
                        </td>
                        <td>{{ Format::level($k->level_klaim) }}</td>
                        <td>
                            @if ($k->terverifikasi)
                                <span class="centang" style="font-weight:500"><i class="bi bi-patch-check-fill"></i> Terverifikasi</span>
                            @else
                                <span class="muted">Belum</span>
                            @endif
                        </td>
                        <td>@if ($k->aktif)<span class="pill t-green">Tampil</span>@else<span class="pill">Disembunyikan</span>@endif</td>
                        <td class="muted">{{ Format::tanggal($k->tanggal_ditambahkan) }}</td>
                        <td class="aksi">
                            <a href="{{ route('pencari.keahlian.edit', $k) }}" class="btn btn-sm btn-line">Ubah</a>
                            <form action="{{ route('pencari.keahlian.destroy', $k) }}" method="POST" class="inline" data-confirm="Hapus {{ $k->keahlian->nama }} dari profil? Status verifikasinya ikut hilang.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus {{ $k->keahlian->nama }}"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
