@extends('layouts.app')

@section('title', $profil->pengguna->nama)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('perusahaan.pelamar.index', $lamaran->lowongan) }}" class="back"><i class="bi bi-arrow-return-left"></i> Daftar pelamar</a>
<div class="page-head">
    <div class="row" style="gap:16px;align-items:flex-start">
        @include('partials.avatar', ['orang' => $profil->pengguna, 'ukuran' => 'lg'])
        <div>
            <h1>{{ $profil->pengguna->nama }}</h1>
            <p class="sub">{{ $profil->headline ?: 'Tanpa headline' }} &middot; melamar {{ $lamaran->lowongan->posisi }} pada {{ Format::tanggal($lamaran->dilamar_pada) }}</p>
        </div>
    </div>
    @if ($lamaran->status !== 'ditarik')
        <form action="{{ route('perusahaan.pelamar.status', $lamaran) }}" method="POST" class="page-actions">
            @csrf @method('PATCH')
            <label class="sr-only" for="status">Status lamaran</label>
            <select id="status" name="status" class="select" style="height:38px;width:170px">
                @foreach (array_diff(Format::STATUS_LAMARAN, ['ditarik']) as $s)
                    <option value="{{ $s }}" @selected($lamaran->status === $s)>{{ Format::label($s) }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Ubah status</button>
        </form>
    @endif
</div>

@if ($lamaran->status === 'ditarik')
    <div class="flash info"><i class="bi bi-info-circle"></i><div>Pelamar sudah menarik lamaran ini, jadi status dan tahapnya tidak bisa diubah lagi.</div></div>
@endif

<div class="cols cols-left">
    <aside class="stack">
        <div class="panel">
            <div class="panel-body">
                <div class="muted small">Skor saat melamar</div>
                <div class="skor-besar" style="margin-top:6px">{{ Format::skor($lamaran->skor_kecocokan) }}<small>%</small></div>
                <div @class(['meter', 'hijau' => $cocok['memenuhi_wajib']]) style="margin:12px 0 18px"><span style="width:{{ $lamaran->skor_kecocokan }}%"></span></div>
                <ul class="cek-list">
                    @foreach ($cocok['rincian'] as $r)
                        <li>
                            <i class="bi {{ $r->ok ? 'bi-check-lg' : 'bi-x-lg' }}"></i>
                            <span>{{ $r->syarat->keahlian->nama }} <span class="muted">({{ $r->syarat->sifat }})</span></span>
                            <span class="ket">{{ $r->ok ? '' : $r->alasan }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="panel-foot small muted">Rincian memakai keahlian pelamar hari ini, jadi bisa berbeda dari skor tersimpan.</div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Kontak</h2></div>
            <div class="panel-body">
                <dl class="dl">
                    <dt>Email</dt><dd><a href="mailto:{{ $profil->pengguna->email }}">{{ $profil->pengguna->email }}</a></dd>
                    <dt>Telepon</dt><dd>{{ $profil->pengguna->nomor_telepon ?: '-' }}</dd>
                    <dt>Kota</dt><dd>{{ $profil->lokasi ?: '-' }}</dd>
                </dl>
            </div>
        </div>
    </aside>

    <div class="stack">
        <div class="panel">
            <div class="panel-head"><h2>Profil</h2></div>
            <div class="panel-body">
                <p class="prose">{{ $profil->ringkasan ?: 'Pelamar belum menulis ringkasan.' }}</p>
                <h3 class="section-title" style="font-size:14px;margin-top:22px">Keahlian</h3>
                <div class="wrap-gap">
                    @forelse ($klaim as $k)
                        @include('partials.keahlian', ['k' => $k])
                    @empty
                        <span class="muted">Tidak ada keahlian yang ditampilkan.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Sertifikat yang disetujui</h2></div>
            @if ($sertifikat->isEmpty())
                <div class="panel-body muted">Belum ada sertifikat yang lolos verifikasi.</div>
            @else
                <ul class="list">
                    @foreach ($sertifikat as $s)
                        <li>
                            <i class="bi bi-file-earmark-pdf" style="color:var(--rose);font-size:18px"></i>
                            <div class="isi">
                                <span class="judul">{{ $s->judul }}</span>
                                <div class="ket">{{ $s->jenisBukti->nama }} &middot; {{ $s->penerbit }}</div>
                            </div>
                            <a href="{{ route('berkas.bukti', $s->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-line">Buka PDF</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="panel">
            <div class="panel-head"><h2>Tahapan seleksi</h2></div>
            @if ($lamaran->tahapSeleksi->isEmpty())
                <div class="panel-body muted">Belum ada tahap. Tambahkan di bawah, misalnya seleksi berkas atau wawancara.</div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>#</th><th>Tahap</th><th>Jadwal</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($lamaran->tahapSeleksi as $t)
                            <tr>
                                <td class="muted">{{ $t->urutan }}</td>
                                <td colspan="3">
                                    <form action="{{ route('perusahaan.tahap.update', $t) }}" method="POST" class="baris-tahap">
                                        @csrf @method('PUT')
                                        <div>
                                            <div class="utama">{{ $t->nama_tahap }}</div>
                                            <input name="catatan" value="{{ $t->catatan }}" class="input" style="height:32px;margin-top:6px;font-size:12.5px" placeholder="Catatan" aria-label="Catatan {{ $t->nama_tahap }}">
                                        </div>
                                        <input type="datetime-local" name="jadwal" value="{{ $t->jadwal?->format('Y-m-d\TH:i') }}" class="input" style="height:34px" aria-label="Jadwal {{ $t->nama_tahap }}">
                                        <select name="status" class="select" style="height:34px" aria-label="Status {{ $t->nama_tahap }}">
                                            @foreach (Format::STATUS_TAHAP as $s)<option value="{{ $s }}" @selected($t->status === $s)>{{ Format::label($s) }}</option>@endforeach
                                        </select>
                                        <button class="btn btn-sm btn-line">Simpan</button>
                                    </form>
                                </td>
                                <td class="aksi">
                                    <form action="{{ route('perusahaan.tahap.destroy', $t) }}" method="POST" class="inline" data-confirm="Hapus tahap {{ $t->nama_tahap }}?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-ghost" aria-label="Hapus tahap {{ $t->nama_tahap }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @if ($lamaran->status !== 'ditarik')
                <form action="{{ route('perusahaan.tahap.store', $lamaran) }}" method="POST" class="panel-foot">
                    @csrf
                    <div class="baris-baru">
                        <div class="field">
                            <label class="label" for="urutan">Urutan</label>
                            <input id="urutan" type="number" name="urutan" min="1" value="{{ old('urutan', ($lamaran->tahapSeleksi->max('urutan') ?? 0) + 1) }}" class="input">
                        </div>
                        <div class="field">
                            <label class="label" for="nama_tahap">Tahap baru</label>
                            <input id="nama_tahap" name="nama_tahap" value="{{ old('nama_tahap') }}" class="input" placeholder="Contoh: Wawancara user">
                        </div>
                        <div class="field">
                            <label class="label" for="jadwal">Jadwal</label>
                            <input id="jadwal" type="datetime-local" name="jadwal" value="{{ old('jadwal') }}" class="input">
                        </div>
                        <button class="btn btn-primary" style="height:44px">Tambah</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
