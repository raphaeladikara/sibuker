@extends('layouts.app')

@section('title', $item ? 'Ubah verifikator' : 'Tambah verifikator')

@section('content')
@php $dipilih = old('keahlian_id', $item?->kewenangan->pluck('id')->all() ?? []); @endphp
<a href="{{ route('admin.verifikator.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Verifikator</a>
<div class="page-head">
    <div>
        <h1>{{ $item ? $item->pengguna->nama : 'Tambah verifikator' }}</h1>
        <p class="sub">{{ $item ? $item->pengguna->email : 'Pilih akun yang sudah ada. Buat akunnya dulu di menu Pengguna kalau belum ada.' }}</p>
    </div>
</div>

<form action="{{ $item ? route('admin.verifikator.update', $item) : route('admin.verifikator.store') }}" method="POST" class="panel" style="max-width:860px">
    @csrf
    @if ($item) @method('PUT') @endif
    <div class="panel-body">
        @unless ($item)
            <div class="field">
                <label class="label" for="pengguna_id">Akun</label>
                <select id="pengguna_id" name="pengguna_id" @class(['select', 'is-invalid' => $errors->has('pengguna_id')]) required>
                    <option value="">Pilih akun</option>
                    @foreach ($pengguna as $p)
                        <option value="{{ $p->id }}" @selected(old('pengguna_id') == $p->id)>{{ $p->nama }} ({{ $p->email }})</option>
                    @endforeach
                </select>
            </div>
        @endunless
        <div class="fields" @unless ($item) style="margin-top:18px" @endunless>
            <div class="field">
                <label class="label" for="instansi">Instansi</label>
                <input id="instansi" name="instansi" value="{{ old('instansi', $item->instansi ?? '') }}" class="input" placeholder="LSP, BLK, atau praktisi independen">
            </div>
            <div class="field">
                <label class="label" for="jabatan">Jabatan</label>
                <input id="jabatan" name="jabatan" value="{{ old('jabatan', $item->jabatan ?? '') }}" class="input">
            </div>
        </div>
        <div class="field" style="margin-top:22px">
            <span class="label" id="label-status">Status</span>
            <div class="segmented" role="radiogroup" aria-labelledby="label-status">
                @foreach (['aktif', 'nonaktif'] as $s)
                    <input type="radio" name="status_verifikator" id="sv-{{ $s }}" value="{{ $s }}" @checked(old('status_verifikator', $item->status_verifikator ?? 'aktif') === $s)>
                    <label for="sv-{{ $s }}">{{ ucfirst($s) }}</label>
                @endforeach
            </div>
            <span class="hint">Verifikator nonaktif tidak bisa membuka panel verifikator.</span>
        </div>
        <div class="field" style="margin-top:22px">
            <span class="label">Kewenangan</span>
            <div class="pilih-grid">
                @foreach ($keahlian as $k)
                    <label>
                        <input type="checkbox" name="keahlian_id[]" value="{{ $k->id }}" @checked(in_array($k->id, $dipilih))>
                        <span><span style="display:block;font-weight:500">{{ $k->nama }}</span><span class="muted small">{{ $k->kategori ?: '-' }}</span></span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ route('admin.verifikator.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
