@extends('layouts.app')

@section('title', 'Profil perusahaan')

@section('content')
<div class="page-head">
    <div>
        <h1>Profil perusahaan</h1>
        <p class="sub">Tampil di halaman detail setiap lowongan yang kamu publikasikan.</p>
    </div>
</div>

<form action="{{ route('perusahaan.profil.update') }}" method="POST" enctype="multipart/form-data" class="cols cols-left">
    @csrf
    @method('PUT')
    <aside class="panel">
        <div class="panel-body" style="text-align:center">
            <div style="display:flex;justify-content:center">@include('partials.logo', ['perusahaan' => $perusahaan, 'ukuran' => 'lg'])</div>
            <div style="font-weight:600;margin-top:14px">{{ $perusahaan->nama }}</div>
            <div style="margin-top:8px">@include('partials.status', ['status' => $perusahaan->status_perusahaan])</div>
            <label class="btn btn-line btn-sm" style="margin-top:18px">
                <i class="bi bi-image"></i> Ganti logo
                <input type="file" name="logo" accept="image/*" class="sr-only">
            </label>
            <p class="hint" style="margin-top:8px">Maksimal 1 MB. Tanpa logo, inisial nama yang dipakai.</p>
        </div>
        <div class="panel-foot small muted">Penanggung jawab: {{ $perusahaan->pengguna->nama }}<br>{{ $perusahaan->pengguna->email }}</div>
    </aside>

    <div class="panel">
        <div class="panel-body">
            <div class="fields fields-lebar">
                <div class="field">
                    <label class="label" for="nama">Nama perusahaan</label>
                    <input id="nama" name="nama" value="{{ old('nama', $perusahaan->nama) }}" @class(['input', 'is-invalid' => $errors->has('nama')]) required>
                </div>
                <div class="field">
                    <label class="label" for="nib">NIB</label>
                    <input id="nib" name="nib" value="{{ old('nib', $perusahaan->nib) }}" @class(['input', 'is-invalid' => $errors->has('nib')]) inputmode="numeric">
                </div>
            </div>
            <div class="field">
                <label class="label" for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="textarea" placeholder="Bidang usaha, ukuran tim, atau budaya kerja">{{ old('deskripsi', $perusahaan->deskripsi) }}</textarea>
            </div>
            <div class="field">
                <label class="label" for="alamat">Alamat</label>
                <input id="alamat" name="alamat" value="{{ old('alamat', $perusahaan->alamat) }}" class="input">
            </div>
            <div class="field">
                <label class="label" for="situs_web">Situs web</label>
                <input id="situs_web" type="url" name="situs_web" value="{{ old('situs_web', $perusahaan->situs_web) }}" @class(['input', 'is-invalid' => $errors->has('situs_web')]) placeholder="https://">
            </div>
        </div>
        <div class="panel-foot form-actions" style="margin:0">
            <button class="btn btn-primary">Simpan profil</button>
        </div>
    </div>
</form>
@endsection
