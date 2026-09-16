@extends('layouts.app')

@section('title', $item ? 'Ubah sertifikat' : 'Unggah sertifikat')

@section('content')
<a href="{{ $item ? route('pencari.sertifikat.show', $item->id) : route('pencari.sertifikat.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> {{ $item ? 'Detail sertifikat' : 'Sertifikat' }}</a>
<div class="page-head">
    <div><h1>{{ $item ? 'Ubah sertifikat' : 'Unggah sertifikat' }}</h1></div>
</div>

<div class="cols cols-side">
    <form action="{{ $item ? route('pencari.sertifikat.update', $item->id) : route('pencari.sertifikat.store') }}" method="POST" enctype="multipart/form-data" class="panel">
        @csrf
        @if ($item) @method('PUT') @endif
        <div class="panel-body">
            <div class="field">
                <label class="label" for="judul">Judul sertifikat</label>
                <input id="judul" name="judul" value="{{ old('judul', $item->judul ?? '') }}" @class(['input', 'is-invalid' => $errors->has('judul')]) placeholder="Contoh: Sertifikat Kompetensi Associate Data Analyst" required>
            </div>
            <div class="fields" style="margin-top:18px">
                <div class="field">
                    <label class="label" for="jenis_bukti_id">Jenis</label>
                    <select id="jenis_bukti_id" name="jenis_bukti_id" @class(['select', 'is-invalid' => $errors->has('jenis_bukti_id')])>
                        @foreach ($jenisBukti as $j)
                            <option value="{{ $j->id }}" @selected(old('jenis_bukti_id', $item->jenis_bukti_id ?? null) == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="label" for="tanggal_terbit">Tanggal terbit</label>
                    <input id="tanggal_terbit" type="date" name="tanggal_terbit" value="{{ old('tanggal_terbit', $item?->tanggal_terbit?->format('Y-m-d')) }}" @class(['input', 'is-invalid' => $errors->has('tanggal_terbit')])>
                </div>
            </div>
            <div class="field">
                <label class="label" for="penerbit">Penerbit</label>
                <input id="penerbit" name="penerbit" value="{{ old('penerbit', $item->penerbit ?? '') }}" class="input" placeholder="Lembaga yang menerbitkan">
            </div>
            <div class="field">
                <span class="label">Berkas PDF @if ($item)<span class="opsi">(kosongkan kalau tidak diganti)</span>@endif</span>
                <label @class(['dropzone', 'is-invalid' => $errors->has('berkas')]) style="position:relative">
                    <input type="file" name="berkas" accept="application/pdf" @required(! $item)>
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                    <span class="nama-berkas" data-kosong="Pilih berkas PDF">Pilih berkas PDF</span>
                    <span class="hint">Maksimal 2 MB</span>
                </label>
                @if ($item)
                    <span class="hint">Berkas sekarang: <code>{{ basename($item->url_berkas) }}</code>. Mengganti berkas membuat sertifikat diperiksa ulang.</span>
                @endif
            </div>
        </div>
        <div class="panel-foot form-actions" style="margin:0">
            <a href="{{ route('pencari.sertifikat.index') }}" class="btn btn-ghost">Batal</a>
            <button class="btn btn-primary">{{ $item ? 'Simpan perubahan' : 'Unggah' }}</button>
        </div>
    </form>

    <aside class="panel">
        <div class="panel-head"><h2>Setelah diunggah</h2></div>
        <div class="panel-body">
            <ol class="timeline">
                <li><span class="no">1</span><div><div class="judul">Masuk antrean</div><div class="ket">Hanya verifikator yang berwenang atas salah satu keahlianmu yang melihatnya.</div></div></li>
                <li><span class="no">2</span><div><div class="judul">Diperiksa</div><div class="ket">Verifikator mengecek keaslian dan memilih keahlian mana yang dibuktikan.</div></div></li>
                <li><span class="no lulus">3</span><div><div class="judul">Centang biru</div><div class="ket">Kalau disetujui, keahlian itu bercentang biru sampai masa berlakunya habis.</div></div></li>
            </ol>
        </div>
    </aside>
</div>
@endsection
