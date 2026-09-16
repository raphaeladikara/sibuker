@extends('layouts.app')

@section('title', $item ? 'Ubah akun' : 'Tambah akun')

@section('content')
<a href="{{ route('admin.pengguna.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Pengguna</a>
<div class="page-head">
    <div>
        <h1>{{ $item ? $item->nama : 'Tambah akun' }}</h1>
        <p class="sub">{{ $item ? 'Peran: ' . (collect($item->daftarPeran())->map(fn ($p) => \App\Models\Pengguna::PERAN[$p])->join(', ') ?: 'belum ada') : 'Akun tanpa profil belum bisa masuk. Tambahkan profil verifikator lewat menu Verifikator.' }}</p>
    </div>
</div>

<form action="{{ $item ? route('admin.pengguna.update', $item) : route('admin.pengguna.store') }}" method="POST" class="panel" style="max-width:720px">
    @csrf
    @if ($item) @method('PUT') @endif
    <div class="panel-body">
        <div class="fields">
            <div class="field">
                <label class="label" for="nama">Nama</label>
                <input id="nama" name="nama" value="{{ old('nama', $item->nama ?? '') }}" @class(['input', 'is-invalid' => $errors->has('nama')]) required>
            </div>
            <div class="field">
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $item->email ?? '') }}" @class(['input', 'is-invalid' => $errors->has('email')]) required>
            </div>
            <div class="field">
                <label class="label" for="nomor_telepon">Nomor telepon</label>
                <input id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $item->nomor_telepon ?? '') }}" class="input">
            </div>
            <div class="field">
                <label class="label" for="password">Kata sandi @if ($item)<span class="opsi">(kosongkan kalau tidak diganti)</span>@endif</label>
                <input id="password" type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) autocomplete="new-password" @required(! $item)>
            </div>
        </div>
        <div class="field" style="margin-top:22px">
            <span class="label" id="label-status">Status akun</span>
            <div class="segmented" role="radiogroup" aria-labelledby="label-status">
                @foreach (['aktif', 'nonaktif', 'ditangguhkan'] as $s)
                    <input type="radio" name="status_akun" id="sa-{{ $s }}" value="{{ $s }}" @checked(old('status_akun', $item->status_akun ?? 'aktif') === $s)>
                    <label for="sa-{{ $s }}">{{ ucfirst($s) }}</label>
                @endforeach
            </div>
        </div>
        <div class="field" style="margin-top:22px">
            <label class="switch"><input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $item->is_admin ?? false))> Hak administrator</label>
        </div>
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ route('admin.pengguna.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">{{ $item ? 'Simpan perubahan' : 'Buat akun' }}</button>
    </div>
</form>
@endsection
