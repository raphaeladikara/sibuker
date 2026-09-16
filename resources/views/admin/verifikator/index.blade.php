@extends('layouts.app')

@section('title', 'Verifikator')

@section('content')
<div class="page-head">
    <div>
        <h1>Verifikator</h1>
        <p class="sub">Verifikator hanya melihat sertifikat di bidang kewenangannya dan tidak bisa memeriksa miliknya sendiri.</p>
    </div>
    <div class="page-actions"><a href="{{ route('admin.verifikator.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah verifikator</a></div>
</div>

<div class="panel">
    @include('admin.partials.cari', ['placeholder' => 'Nama atau instansi'])
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Verifikator</th><th>Kewenangan</th><th class="kanan">Pemeriksaan</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($data as $d)
                <tr>
                    <td>
                        <div class="utama">{{ $d->pengguna->nama }}</div>
                        <div class="sekunder">{{ $d->jabatan ?: '-' }} &middot; {{ $d->instansi ?: '-' }}</div>
                    </td>
                    <td>
                        <div class="wrap-gap">
                            @forelse ($d->kewenangan as $k)<span class="chip">{{ $k->nama }}</span>@empty<span class="muted">Belum ada</span>@endforelse
                        </div>
                    </td>
                    <td class="kanan">{{ $d->verifikasi_count }}</td>
                    <td>@include('partials.status', ['status' => $d->status_verifikator])</td>
                    <td class="aksi">
                        <a href="{{ route('admin.verifikator.edit', $d) }}" class="btn btn-sm btn-line">Ubah</a>
                        @unless ($d->verifikasi_count)
                            <form action="{{ route('admin.verifikator.destroy', $d) }}" method="POST" class="inline" data-confirm="Hapus profil verifikator {{ $d->pengguna->nama }}? Akun penggunanya tetap ada.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus {{ $d->pengguna->nama }}"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty">Tidak ada verifikator yang cocok.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
