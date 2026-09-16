@extends('layouts.app')

@section('title', $lowongan->posisi)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('perusahaan.lowongan.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Lowongan</a>
<div class="page-head">
    <div>
        <h1>{{ $lowongan->posisi }}</h1>
        <p class="sub"><code>{{ $lowongan->kode }}</code> &middot; {{ $lowongan->lokasi ?: '-' }} &middot; {{ Format::tipe($lowongan->tipe_pekerjaan) }}</p>
    </div>
    <div class="page-actions">
        @if ($lowongan->status === 'dipublikasikan')
            <a href="{{ route('lowongan.show', $lowongan) }}" class="btn btn-line" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Lihat halaman publik</a>
        @endif
        <a href="{{ route('perusahaan.lowongan.edit', $lowongan) }}" class="btn btn-line"><i class="bi bi-pencil"></i> Ubah</a>
        <a href="{{ route('perusahaan.pelamar.index', $lowongan) }}" class="btn btn-primary">Pelamar ({{ $lowongan->lamaran_count }})</a>
    </div>
</div>

<div class="cols cols-side">
    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Syarat keahlian</h2><span class="muted small">Total bobot {{ rtrim(rtrim(number_format($lowongan->syarat->sum('bobot'), 2, ',', ''), '0'), ',') }}</span></div>
            @if ($lowongan->syarat->isEmpty())
                <div class="empty"><i class="bi bi-list-check"></i><p>Belum ada syarat. Tambahkan minimal satu supaya lowongan bisa dipublikasikan.</p></div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Keahlian</th><th>Level min.</th><th>Sifat</th><th>Centang biru</th><th class="kanan">Bobot</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($lowongan->syarat as $s)
                            <tr>
                                <td class="utama">{{ $s->keahlian->nama }}</td>
                                <td>{{ Format::level($s->level_minimum) }}</td>
                                <td><span class="pill {{ $s->sifat === 'wajib' ? 't-rose' : '' }}">{{ Format::label($s->sifat) }}</span></td>
                                <td>@if ($s->wajib_terverifikasi)<span class="centang"><i class="bi bi-patch-check-fill"></i> Wajib</span>@else<span class="muted">Tidak</span>@endif</td>
                                <td class="kanan">{{ rtrim(rtrim(number_format($s->bobot, 2, ',', ''), '0'), ',') }}</td>
                                <td class="aksi">
                                    <form action="{{ route('perusahaan.syarat.destroy', $s) }}" method="POST" class="inline" data-confirm="Hapus syarat {{ $s->keahlian->nama }}? Skor lamaran yang sudah masuk tidak berubah.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus syarat {{ $s->keahlian->nama }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <form action="{{ route('perusahaan.syarat.store', $lowongan) }}" method="POST" class="panel-foot">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;align-items:end">
                    <div class="field" style="grid-column:span 2">
                        <label class="label" for="keahlian_id">Tambah keahlian</label>
                        <select id="keahlian_id" name="keahlian_id" class="select" @disabled($keahlian->isEmpty())>
                            @forelse ($keahlian as $k)
                                <option value="{{ $k->id }}" @selected(old('keahlian_id') == $k->id)>{{ $k->nama }}</option>
                            @empty
                                <option>Semua keahlian sudah dipakai</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="level_minimum">Level min.</label>
                        <select id="level_minimum" name="level_minimum" class="select">
                            @foreach (Format::LEVEL as $n => $t)<option value="{{ $n }}" @selected(old('level_minimum', 'menengah') === $n)>{{ $t }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="sifat">Sifat</label>
                        <select id="sifat" name="sifat" class="select">
                            <option value="wajib">Wajib</option>
                            <option value="opsional" @selected(old('sifat') === 'opsional')>Opsional</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label" for="bobot">Bobot</label>
                        <input id="bobot" type="number" name="bobot" step="0.5" min="0" max="999" value="{{ old('bobot', 1) }}" class="input">
                    </div>
                </div>
                <div class="row-between" style="margin-top:14px;flex-wrap:wrap">
                    <label class="check"><input type="checkbox" name="wajib_terverifikasi" value="1" @checked(old('wajib_terverifikasi'))> Pelamar harus punya centang biru untuk keahlian ini</label>
                    <button class="btn btn-primary" @disabled($keahlian->isEmpty())><i class="bi bi-plus-lg"></i> Tambah syarat</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Deskripsi</h2></div>
            <div class="panel-body"><p class="prose">{{ $lowongan->deskripsi }}</p></div>
        </div>
    </div>

    <aside class="panel">
        <div class="panel-body">
            <dl class="dl">
                <dt>Status</dt><dd>@include('partials.status', ['status' => $lowongan->status])</dd>
                <dt>Dibuat</dt><dd>{{ Format::tanggal($lowongan->dibuat_pada) }}</dd>
                <dt>Dipublikasikan</dt><dd>{{ Format::tanggal($lowongan->dipublikasikan_pada) }}</dd>
                <dt>Ditutup</dt><dd>{{ Format::tanggal($lowongan->ditutup_pada) }}</dd>
                <dt>Pelamar</dt><dd>{{ $lowongan->lamaran_count }}</dd>
            </dl>
        </div>
        <div class="panel-foot small muted">
            Skor pelamar = bobot syarat yang terpenuhi dibagi total bobot. Syarat dengan centang biru wajib hanya terpenuhi kalau klaimnya sudah diverifikasi.
        </div>
    </aside>
</div>
@endsection
