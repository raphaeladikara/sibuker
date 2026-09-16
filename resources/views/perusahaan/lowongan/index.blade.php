@extends('layouts.app')

@section('title', 'Lowongan')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Lowongan &amp; pelamar</h1>
        <p class="sub">Lowongan baru selalu tersimpan sebagai draft. Publikasikan setelah syarat keahliannya lengkap.</p>
    </div>
    <div class="page-actions"><a href="{{ route('perusahaan.lowongan.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Buat lowongan</a></div>
</div>

<div class="panel">
    @if ($lowongan->isEmpty())
        <div class="empty"><i class="bi bi-briefcase"></i><p>Belum ada lowongan.</p><a href="{{ route('perusahaan.lowongan.create') }}" class="btn btn-primary btn-sm">Buat lowongan</a></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Posisi</th><th>Tipe</th><th>Status</th><th class="kanan">Syarat</th><th class="kanan">Pelamar</th><th></th></tr></thead>
                <tbody>
                @foreach ($lowongan as $l)
                    <tr>
                        <td>
                            <div class="utama"><a href="{{ route('perusahaan.lowongan.show', $l) }}">{{ $l->posisi }}</a></div>
                            <div class="sekunder"><code>{{ $l->kode }}</code> &middot; {{ $l->lokasi ?: '-' }}</div>
                        </td>
                        <td>{{ Format::tipe($l->tipe_pekerjaan) }}</td>
                        <td>
                            @include('partials.status', ['status' => $l->status])
                            <div class="sekunder">
                                @if ($l->status === 'dipublikasikan') sejak {{ Format::tanggal($l->dipublikasikan_pada) }}
                                @elseif ($l->status === 'ditutup') {{ Format::tanggal($l->ditutup_pada) }}
                                @endif
                            </div>
                        </td>
                        <td class="kanan">{{ $l->syarat_count }}</td>
                        <td class="kanan">{{ $l->lamaran_count }}</td>
                        <td class="aksi">
                            <a href="{{ route('perusahaan.pelamar.index', $l) }}" class="btn btn-sm btn-line">Pelamar</a>
                            <a href="{{ route('perusahaan.lowongan.edit', $l) }}" class="btn btn-sm btn-icon btn-ghost" aria-label="Ubah {{ $l->posisi }}"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('perusahaan.lowongan.destroy', $l) }}" method="POST" class="inline" data-confirm="Hapus {{ $l->posisi }}? Semua syarat, lamaran, dan tahap seleksinya ikut terhapus.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus {{ $l->posisi }}"><i class="bi bi-trash"></i></button>
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
