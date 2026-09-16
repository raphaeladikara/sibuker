@extends('layouts.app')

@section('title', 'Profil saya')

@section('content')
<a href="{{ route('pencari.dashboard') }}" class="back"><i class="bi bi-arrow-return-left"></i> Kembali</a>
<div class="page-head">
    <div>
        <h1>Profil saya</h1>
        <p class="sub">Perusahaan melihat bagian ini saat meninjau lamaranmu.</p>
    </div>
</div>

<form action="{{ route('pencari.profil.update') }}" method="POST" enctype="multipart/form-data" class="cols cols-left">
    @csrf
    @method('PUT')
    <aside class="panel">
        <div class="panel-body" style="text-align:center">
            <div style="display:flex;justify-content:center">@include('partials.avatar', ['orang' => $pengguna, 'ukuran' => 'lg'])</div>
            <div style="font-weight:600;margin-top:14px">{{ $pengguna->nama }}</div>
            <div class="muted small">{{ $pengguna->email }}</div>
            <label class="btn btn-line btn-sm" style="margin-top:18px">
                <i class="bi bi-image"></i> Ganti foto
                <input type="file" name="foto_profil" accept="image/*" class="sr-only">
            </label>
            <p class="hint" style="margin-top:8px">JPG atau PNG, maksimal 1 MB.</p>
        </div>
        <div class="panel-foot small muted">Peran akun: {{ collect($pengguna->daftarPeran())->map(fn ($p) => \App\Models\Pengguna::PERAN[$p])->join(', ') }}</div>
    </aside>

    <div class="panel">
        <div class="panel-head"><h2>Akun</h2></div>
        <div class="panel-body">
            <div class="fields">
                <div class="field">
                    <label class="label" for="nama">Nama</label>
                    <input id="nama" name="nama" value="{{ old('nama', $pengguna->nama) }}" @class(['input', 'is-invalid' => $errors->has('nama')]) required>
                </div>
                <div class="field">
                    <label class="label" for="nomor_telepon">Nomor telepon</label>
                    <input id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $pengguna->nomor_telepon) }}" class="input" inputmode="tel">
                </div>
            </div>
            <div class="field">
                <label class="label" for="email">Email</label>
                <input id="email" value="{{ $pengguna->email }}" class="input" disabled>
                <span class="hint">Email dipakai untuk masuk dan hanya bisa diubah administrator.</span>
            </div>
        </div>
        <div class="panel-head" style="border-top:1px solid var(--line)"><h2>Profil pencari kerja</h2></div>
        <div class="panel-body">
            <div class="field">
                <label class="label" for="headline">Headline</label>
                <input id="headline" name="headline" value="{{ old('headline', $profil->headline) }}" class="input" placeholder="Contoh: Data Analyst | SQL & Python">
            </div>
            <div class="fields" style="margin-top:18px">
                <div class="field">
                    <label class="label" for="lokasi">Kota</label>
                    <input id="lokasi" name="lokasi" value="{{ old('lokasi', $profil->lokasi) }}" class="input">
                </div>
                <div class="field">
                    <label class="label" for="tanggal_lahir">Tanggal lahir</label>
                    <input id="tanggal_lahir" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $profil->tanggal_lahir?->format('Y-m-d')) }}" @class(['input', 'is-invalid' => $errors->has('tanggal_lahir')])>
                </div>
            </div>
            <div class="field">
                <label class="label" for="ringkasan">Ringkasan</label>
                <textarea id="ringkasan" name="ringkasan" rows="5" class="textarea" placeholder="Pengalaman kerja, proyek, atau pelatihan yang pernah kamu ikuti">{{ old('ringkasan', $profil->ringkasan) }}</textarea>
            </div>
        </div>
        <div class="panel-foot form-actions" style="margin:0">
            <button class="btn btn-primary">Simpan profil</button>
        </div>
    </div>
</form>
@endsection
