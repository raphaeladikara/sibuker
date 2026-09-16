@extends('layouts.app')

@section('title', $item ? 'Ubah keahlian' : 'Tambah keahlian')

@section('content')
<a href="{{ route('admin.keahlian.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Keahlian</a>
<div class="page-head"><div><h1>{{ $item ? $item->nama : 'Tambah keahlian' }}</h1></div></div>

<form action="{{ $item ? route('admin.keahlian.update', $item) : route('admin.keahlian.store') }}" method="POST" class="panel" style="max-width:640px">
    @csrf
    @if ($item) @method('PUT') @endif
    <div class="panel-body">
        <div class="fields">
            <div class="field">
                <label class="label" for="nama">Nama</label>
                <input id="nama" name="nama" value="{{ old('nama', $item->nama ?? '') }}" @class(['input', 'is-invalid' => $errors->has('nama')]) required>
                <span class="hint">Harus unik.</span>
            </div>
            <div class="field">
                <label class="label" for="kategori">Kategori</label>
                <input id="kategori" name="kategori" value="{{ old('kategori', $item->kategori ?? '') }}" class="input" list="daftar-kategori">
                <datalist id="daftar-kategori">@foreach ($kategori as $k)<option value="{{ $k }}">@endforeach</datalist>
            </div>
        </div>
        <div class="field">
            <label class="label" for="deskripsi">Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" rows="3" class="textarea">{{ old('deskripsi', $item->deskripsi ?? '') }}</textarea>
        </div>
        <div class="field" style="margin-top:20px">
            <label class="switch"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $item->aktif ?? true))> Aktif</label>
            <span class="hint">Keahlian nonaktif tidak bisa dipilih untuk klaim atau syarat baru. Data lama tetap ada.</span>
        </div>
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ route('admin.keahlian.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
