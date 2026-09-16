@extends('layouts.app')

@section('title', 'Jenis bukti')

@section('content')
<div class="page-head">
    <div>
        <h1>Jenis bukti</h1>
        <p class="sub">Kategori yang dipilih pencari kerja saat mengunggah sertifikat.</p>
    </div>
    <div class="page-actions"><a href="{{ route('admin.jenis-bukti.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah jenis</a></div>
</div>

<div class="panel" style="max-width:760px">
    @include('admin.partials.cari', ['placeholder' => 'Nama jenis bukti'])
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th class="kanan">Dipakai sertifikat</th><th></th></tr></thead>
            <tbody>
            @forelse ($data as $d)
                <tr>
                    <td class="utama">{{ $d->nama }}</td>
                    <td class="kanan">{{ $d->bukti_count }}</td>
                    <td class="aksi">
                        <a href="{{ route('admin.jenis-bukti.edit', $d) }}" class="btn btn-sm btn-line">Ubah</a>
                        @unless ($d->bukti_count)
                            <form action="{{ route('admin.jenis-bukti.destroy', $d) }}" method="POST" class="inline" data-confirm="Hapus jenis bukti {{ $d->nama }}?">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus {{ $d->nama }}"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="3"><div class="empty">Tidak ada jenis bukti yang cocok.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
