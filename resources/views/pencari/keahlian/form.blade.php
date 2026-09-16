@extends('layouts.app')

@section('title', $item ? 'Ubah keahlian' : 'Tambah keahlian')

@section('content')
<a href="{{ route('pencari.keahlian.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Keahlian</a>
<div class="page-head">
    <div>
        <h1>{{ $item ? 'Ubah ' . $item->keahlian->nama : 'Tambah keahlian' }}</h1>
        <p class="sub">Level adalah penilaianmu sendiri. Verifikator tidak menilai ulang level, hanya keaslian sertifikat.</p>
    </div>
</div>

<form action="{{ $item ? route('pencari.keahlian.update', $item) : route('pencari.keahlian.store') }}" method="POST" class="panel" style="max-width:640px">
    @csrf
    @if ($item) @method('PUT') @endif
    <div class="panel-body">
        @unless ($item)
            <div class="field">
                <label class="label" for="keahlian_id">Keahlian</label>
                @if ($keahlian->isEmpty())
                    <p class="muted">Semua keahlian yang tersedia sudah ada di profilmu.</p>
                @else
                    <select id="keahlian_id" name="keahlian_id" @class(['select', 'is-invalid' => $errors->has('keahlian_id')]) required>
                        <option value="">Pilih keahlian</option>
                        @foreach ($keahlian->groupBy('kategori') as $kategori => $isi)
                            <optgroup label="{{ $kategori ?: 'Lainnya' }}">
                                @foreach ($isi as $k)
                                    <option value="{{ $k->id }}" @selected(old('keahlian_id') == $k->id)>{{ $k->nama }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <span class="hint">Tidak ada di daftar? Keahlian baru ditambahkan oleh administrator.</span>
                @endif
            </div>
        @endunless

        <div class="field">
            <span class="label" id="label-level">Level</span>
            <div class="segmented" role="radiogroup" aria-labelledby="label-level">
                @foreach (\App\Support\Format::LEVEL as $nilai => $teks)
                    <input type="radio" name="level_klaim" id="lv-{{ $nilai }}" value="{{ $nilai }}" @checked(old('level_klaim', $item->level_klaim ?? 'menengah') === $nilai)>
                    <label for="lv-{{ $nilai }}">{{ $teks }}</label>
                @endforeach
            </div>
        </div>

        <div class="field" style="margin-top:22px">
            <input type="hidden" name="aktif" value="0">
            <label class="switch">
                <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $item->aktif ?? true))>
                Tampilkan di profil
            </label>
            <span class="hint">Keahlian yang disembunyikan tidak ikut dihitung dalam skor kecocokan.</span>
        </div>
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ route('pencari.keahlian.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">{{ $item ? 'Simpan perubahan' : 'Tambahkan' }}</button>
    </div>
</form>
@endsection
