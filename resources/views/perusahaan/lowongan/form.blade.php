@extends('layouts.app')

@section('title', $item ? 'Ubah lowongan' : 'Buat lowongan')

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ $item ? route('perusahaan.lowongan.show', $item) : route('perusahaan.lowongan.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Kembali</a>
<div class="page-head">
    <div>
        <h1>{{ $item ? 'Ubah lowongan' : 'Buat lowongan' }}</h1>
        <p class="sub">{{ $item ? $item->kode : 'Tersimpan sebagai draft. Syarat keahlian ditambahkan di langkah berikutnya.' }}</p>
    </div>
</div>

<form action="{{ $item ? route('perusahaan.lowongan.update', $item) : route('perusahaan.lowongan.store') }}" method="POST" class="panel" style="max-width:820px">
    @csrf
    @if ($item) @method('PUT') @endif
    <div class="panel-body">
        <div class="fields fields-sempit">
            <div class="field">
                <label class="label" for="kode">Kode</label>
                <input id="kode" name="kode" value="{{ old('kode', $item->kode ?? $kodeSaran) }}" @class(['input', 'is-invalid' => $errors->has('kode')]) required>
            </div>
            <div class="field">
                <label class="label" for="posisi">Posisi</label>
                <input id="posisi" name="posisi" value="{{ old('posisi', $item->posisi ?? '') }}" @class(['input', 'is-invalid' => $errors->has('posisi')]) placeholder="Contoh: Junior Data Engineer" required>
            </div>
        </div>
        <div class="field">
            <label class="label" for="deskripsi">Deskripsi pekerjaan</label>
            <textarea id="deskripsi" name="deskripsi" rows="7" @class(['textarea', 'is-invalid' => $errors->has('deskripsi')]) placeholder="Apa yang dikerjakan sehari-hari, dengan siapa, dan pakai alat apa" required>{{ old('deskripsi', $item->deskripsi ?? '') }}</textarea>
            <span class="hint">Tidak perlu menulis syarat ijazah. Kebutuhan kandidat diatur lewat syarat keahlian.</span>
        </div>
        <div class="fields" style="margin-top:18px">
            <div class="field">
                <label class="label" for="lokasi">Lokasi</label>
                <input id="lokasi" name="lokasi" value="{{ old('lokasi', $item->lokasi ?? '') }}" class="input" placeholder="Surabaya, Remote, Hybrid">
            </div>
            <div class="field">
                <label class="label" for="tipe_pekerjaan">Tipe pekerjaan</label>
                <select id="tipe_pekerjaan" name="tipe_pekerjaan" class="select">
                    @foreach (Format::TIPE_PEKERJAAN as $n => $t)
                        <option value="{{ $n }}" @selected(old('tipe_pekerjaan', $item->tipe_pekerjaan ?? 'penuh_waktu') === $n)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($item)
            <div class="field" style="margin-top:22px">
                <span class="label" id="label-status">Status</span>
                <div class="segmented" role="radiogroup" aria-labelledby="label-status">
                    @foreach (Format::STATUS_LOWONGAN as $s)
                        <input type="radio" name="status" id="st-{{ $s }}" value="{{ $s }}" @checked(old('status', $item->status) === $s)>
                        <label for="st-{{ $s }}">{{ Format::label($s) }}</label>
                    @endforeach
                </div>
                @error('status')<span class="error">{{ $message }}</span>@enderror
                @if (! $item->syarat_count)
                    <span class="hint">Lowongan ini belum punya syarat keahlian, jadi belum bisa dipublikasikan.</span>
                @endif
            </div>
        @endif
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ $item ? route('perusahaan.lowongan.show', $item) : route('perusahaan.lowongan.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">{{ $item ? 'Simpan perubahan' : 'Simpan draft' }}</button>
    </div>
</form>
@endsection
