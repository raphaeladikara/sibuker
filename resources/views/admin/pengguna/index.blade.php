@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
@php use App\Models\Pengguna; @endphp
<div class="page-head">
    <div>
        <h1>Pengguna</h1>
        <p class="sub">Akun berstatus nonaktif atau ditangguhkan tidak bisa masuk.</p>
    </div>
    <div class="page-actions"><a href="{{ route('admin.pengguna.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah akun</a></div>
</div>

<div class="panel">
    @include('admin.partials.cari', ['placeholder' => 'Nama atau email'])
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Peran</th><th>Status</th><th>Dibuat</th><th></th></tr></thead>
            <tbody>
            @forelse ($data as $d)
                <tr>
                    <td>
                        <div class="row" style="gap:12px">
                            @include('partials.avatar', ['orang' => $d])
                            <div>
                                <div class="utama">{{ $d->nama }}</div>
                                <div class="sekunder">{{ $d->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="wrap-gap">
                            @forelse ($d->daftarPeran() as $p)
                                <span class="pill {{ $p === 'admin' ? 't-violet' : '' }}">{{ Pengguna::PERAN[$p] }}</span>
                            @empty
                                <span class="muted">-</span>
                            @endforelse
                        </div>
                    </td>
                    <td>@include('partials.status', ['status' => $d->status_akun])</td>
                    <td class="muted">{{ \App\Support\Format::tanggal($d->dibuat_pada) }}</td>
                    <td class="aksi">
                        <a href="{{ route('admin.pengguna.edit', $d) }}" class="btn btn-sm btn-line">Ubah</a>
                        @unless ($d->is(auth()->user()))
                            <form action="{{ route('admin.pengguna.destroy', $d) }}" method="POST" class="inline" data-confirm="Hapus akun {{ $d->email }}? Profil pencari kerja, klaim, sertifikat, dan lamarannya ikut terhapus.">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus {{ $d->nama }}"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="empty">Tidak ada akun yang cocok.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $data->links('partials.paginasi') }}
@endsection
