@extends('layouts.app')

@section('title', 'Periksa ' . $bukti->judul)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('verifikator.pemilik.show', $bukti->pengguna_id) }}" class="back"><i class="bi bi-arrow-return-left"></i> Sertifikat {{ $bukti->pengguna->nama }}</a>
<div class="page-head">
    <div>
        <h1>{{ $bukti->judul }}</h1>
        <p class="sub">Milik {{ $bukti->pengguna->nama }} &middot; {{ $bukti->jenisBukti->nama }} &middot; {{ $bukti->penerbit ?: 'penerbit tidak diisi' }} &middot; terbit {{ Format::tanggal($bukti->tanggal_terbit) }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('berkas.bukti', $bukti->id) }}" target="_blank" rel="noopener" class="btn btn-line"><i class="bi bi-box-arrow-up-right"></i> Buka PDF di tab baru</a>
    </div>
</div>

<div class="cols cols-periksa">
    <div class="stack">
        <div class="panel" style="overflow:hidden">
            <iframe src="{{ route('berkas.bukti', $bukti->id) }}" class="pdf" style="height:620px" title="Berkas {{ $bukti->judul }}"></iframe>
        </div>
        @if ($bukti->verifikasi->isNotEmpty())
            <div class="panel">
                <div class="panel-head"><h2>Pemeriksaan sebelumnya</h2></div>
                <ul class="list">
                    @foreach ($bukti->verifikasi as $v)
                        <li>
                            @include('partials.status', ['status' => $v->keputusan])
                            <div class="isi">
                                <span class="judul">{{ $v->verifikator->pengguna->nama }}</span>
                                <div class="ket">{{ Format::tanggal($v->diverifikasi_pada ?? $v->dibuat_pada, true) }}@if ($v->catatan) &middot; {{ $v->catatan }}@endif</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <form action="{{ route('verifikator.periksa.store', $bukti->id) }}" method="POST" class="panel" id="form-periksa">
        @csrf
        <div class="panel-head"><h2>Keputusan</h2></div>
        <div class="panel-body">
            <div class="segmented keputusan" role="radiogroup" aria-label="Keputusan" style="display:flex">
                <input type="radio" name="keputusan" id="k-setuju" value="disetujui" @checked(old('keputusan', 'disetujui') === 'disetujui')>
                <label for="k-setuju" style="flex:1;justify-content:center"><i class="bi bi-check-lg"></i> Setujui</label>
                <input type="radio" name="keputusan" id="k-tolak" value="ditolak" @checked(old('keputusan') === 'ditolak')>
                <label for="k-tolak" style="flex:1;justify-content:center"><i class="bi bi-x-lg"></i> Tolak</label>
                <input type="radio" name="keputusan" id="k-tunda" value="menunggu" @checked(old('keputusan') === 'menunggu')>
                <label for="k-tunda" style="flex:1;justify-content:center"><i class="bi bi-hourglass"></i> Tunda</label>
            </div>

            <div class="field" data-hanya-setuju style="margin-top:22px">
                <span class="label">Keahlian yang dibuktikan sertifikat ini</span>
                <div class="pilih-list">
                    @foreach ($klaim as $k)
                        @php $berwenang = in_array($k->keahlian_id, $wewenang); @endphp
                        <label @class(['nonaktif' => ! $berwenang])>
                            <input type="checkbox" name="klaim_keahlian_id[]" value="{{ $k->id }}" @disabled(! $berwenang) @checked(in_array($k->id, old('klaim_keahlian_id', [])))>
                            <span>{{ $k->keahlian->nama }} <span class="muted">&middot; klaim {{ Format::level($k->level_klaim) }}</span></span>
                            <span class="kanan">
                                @if (! $berwenang) di luar kewenangan
                                @elseif ($k->terverifikasi)<span class="centang"><i class="bi bi-patch-check-fill"></i> sudah bercentang</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
                <span class="hint">Pilih hanya keahlian yang memang tercantum atau diuji di sertifikat.</span>
            </div>

            <div class="field" data-hanya-setuju>
                <label class="label" for="berlaku_sampai">Berlaku sampai <span class="opsi">(opsional)</span></label>
                <input id="berlaku_sampai" type="date" name="berlaku_sampai" value="{{ old('berlaku_sampai') }}" @class(['input', 'is-invalid' => $errors->has('berlaku_sampai')]) min="{{ now()->addDay()->format('Y-m-d') }}">
                <span class="hint">Kosongkan kalau sertifikat tidak punya masa berlaku. Setelah tanggal ini centang biru hilang sendiri.</span>
            </div>

            <div class="field">
                <label class="label" for="catatan">Catatan untuk pemilik</label>
                <textarea id="catatan" name="catatan" rows="3" @class(['textarea', 'is-invalid' => $errors->has('catatan')]) placeholder="Wajib diisi kalau ditolak">{{ old('catatan') }}</textarea>
            </div>
        </div>
        <div class="panel-foot form-actions" style="margin:0">
            <button class="btn btn-primary btn-block">Simpan keputusan</button>
        </div>
    </form>
</div>
@endsection
