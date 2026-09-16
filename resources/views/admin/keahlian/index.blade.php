@extends('layouts.app')

@section('title', 'Keahlian')

@section('content')
<div class="page-head">
    <div>
        <h1>Master keahlian</h1>
        <p class="sub">Dipakai klaim pencari kerja, syarat lowongan, dan kewenangan verifikator. Keahlian yang sudah dipakai tidak bisa dihapus, hanya dinonaktifkan.</p>
    </div>
    <div class="page-actions"><a href="{{ route('admin.keahlian.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah keahlian</a></div>
</div>

<div class="panel">
    @include('admin.partials.cari', ['placeholder' => 'Nama atau kategori'])
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Keahlian</th><th>Kategori</th><th class="kanan">Diklaim</th><th class="kanan">Jadi syarat</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($data as $d)
                <tr>
                    <td>
                        <div class="utama">{{ $d->nama }}</div>
                        <div class="sekunder">{{ $d->deskripsi }}</div>
                    </td>
                    <td>{{ $d->kategori ?: '-' }}</td>
                    <td class="kanan">{{ $d->klaim_count }}</td>
                    <td class="kanan">{{ $d->syarat_count }}</td>
                    <td>@include('partials.status', ['status' => $d->aktif ? 'aktif' : 'nonaktif'])</td>
                    <td class="aksi">
                        <a href="{{ route('admin.keahlian.edit', $d) }}" class="btn btn-sm btn-line">Ubah</a>
                        @if (! $d->klaim_count && ! $d->syarat_count)
                            <form action="{{ route('admin.keahlian.destroy', $d) }}" method="POST" class="inline" data-confirm="Hapus keahlian {{ $d->nama }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus {{ $d->nama }}"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">Tidak ada keahlian yang cocok.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
