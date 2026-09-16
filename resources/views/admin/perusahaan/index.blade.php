@extends('layouts.app')

@section('title', 'Perusahaan')

@section('content')
<div class="page-head">
    <div>
        <h1>Perusahaan</h1>
        <p class="sub">Perusahaan mendaftar sendiri. Admin hanya mengatur statusnya; perusahaan nonaktif tidak tampil di publik.</p>
    </div>
</div>

<div class="panel">
    @include('admin.partials.cari', ['placeholder' => 'Nama atau NIB'])
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Perusahaan</th><th>NIB</th><th>Penanggung jawab</th><th class="kanan">Lowongan</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($data as $d)
                <tr>
                    <td>
                        <div class="row" style="gap:12px">
                            @include('partials.logo', ['perusahaan' => $d])
                            <div>
                                <div class="utama">{{ $d->nama }}</div>
                                <div class="sekunder">{{ $d->alamat ?: '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code>{{ $d->nib ?: '-' }}</code></td>
                    <td>{{ $d->pengguna->nama }}<div class="sekunder">{{ $d->pengguna->email }}</div></td>
                    <td class="kanan">{{ $d->lowongan_count }}</td>
                    <td>@include('partials.status', ['status' => $d->status_perusahaan])</td>
                    <td class="aksi"><a href="{{ route('admin.perusahaan.edit', $d) }}" class="btn btn-sm btn-line">Ubah status</a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty">Tidak ada perusahaan yang cocok.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
