@extends('layouts.app')

@section('title', $item ? 'Ubah jenis bukti' : 'Tambah jenis bukti')

@section('content')
<a href="{{ route('admin.jenis-bukti.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Jenis bukti</a>
<div class="page-head"><div><h1>{{ $item ? $item->nama : 'Tambah jenis bukti' }}</h1></div></div>

<form action="{{ $item ? route('admin.jenis-bukti.update', $item) : route('admin.jenis-bukti.store') }}" method="POST" class="panel" style="max-width:520px">
    @csrf
    @if ($item) @method('PUT') @endif
    <div class="panel-body">
        <div class="field">
            <label class="label" for="nama">Nama</label>
            <input id="nama" name="nama" value="{{ old('nama', $item->nama ?? '') }}" @class(['input', 'is-invalid' => $errors->has('nama')]) placeholder="Contoh: Sertifikat Profesi" required>
        </div>
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ route('admin.jenis-bukti.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
