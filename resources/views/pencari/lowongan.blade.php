@extends('layouts.app')

@section('title', 'Cari lowongan')

@section('content')
@php use App\Support\Format; @endphp
<div class="page-head">
    <div>
        <h1>Cari lowongan</h1>
        <p class="sub">Diurutkan dari skor tertinggi. Setiap syarat ditunjukkan terpenuhi atau tidak, beserta alasannya.</p>
    </div>
</div>

<form method="GET" class="panel" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;padding:12px 16px;margin-bottom:22px">
    <label style="flex:1;min-width:220px;display:flex;align-items:center;gap:10px">
        <i class="bi bi-search" style="color:var(--indigo)"></i>
        <span class="sr-only">Kata kunci</span>
        <input type="search" name="q" value="{{ request('q') }}" class="input" style="border:0;box-shadow:none;padding-left:0" placeholder="Posisi atau perusahaan">
    </label>
    <label class="switch">
        <input type="checkbox" name="hanya_cocok" value="1" @checked(request()->boolean('hanya_cocok')) onchange="this.form.submit()">
        Hanya yang syarat wajibnya terpenuhi
    </label>
    <button class="btn btn-primary">Cari</button>
</form>

@forelse ($hasil as $h)
    <article class="panel" style="margin-bottom:16px">
        <div class="panel-body hasil">
            <div>
                <div class="row" style="gap:14px;align-items:flex-start">
                    @include('partials.logo', ['perusahaan' => $h->lowongan->perusahaan])
                    <div>
                        <h2 style="font-size:16px"><a href="{{ route('lowongan.show', $h->lowongan) }}" style="color:var(--ink)">{{ $h->lowongan->posisi }}</a></h2>
                        <div class="muted">{{ $h->lowongan->perusahaan->nama }} &middot; {{ $h->lowongan->lokasi }} &middot; {{ Format::tipe($h->lowongan->tipe_pekerjaan) }}</div>
                    </div>
                </div>
                <ul class="cek-list" style="margin-top:18px">
                    @foreach ($h->rincian as $r)
                        <li>
                            <i class="bi {{ $r->ok ? 'bi-check-lg' : 'bi-x-lg' }}" aria-hidden="true"></i>
                            <span>
                                {{ $r->syarat->keahlian->nama }}
                                <span class="muted">min. {{ Format::level($r->syarat->level_minimum) }}, {{ $r->syarat->sifat }}</span>
                                @if ($r->syarat->wajib_terverifikasi)<i class="bi bi-patch-check-fill centang" title="Harus terverifikasi"></i>@endif
                            </span>
                            <span class="ket">
                                @if ($r->ok)
                                    kamu {{ Format::level($r->klaim->level_klaim) }}
                                @else
                                    {{ $r->alasan }}
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="skor-kolom">
                <div>
                    <div class="skor-besar">{{ Format::skor($h->skor) }}<small>%</small></div>
                    <div class="muted small" style="margin-top:6px">{{ $h->memenuhi_wajib ? 'Syarat wajib terpenuhi' : 'Syarat wajib kurang' }}</div>
                </div>
                @if ($h->sudah_dilamar)
                    <span class="btn btn-sm disabled"><i class="bi bi-check2"></i> Sudah dilamar</span>
                @else
                    <form action="{{ route('pencari.lowongan.lamar', $h->lowongan) }}" method="POST" data-confirm="Kirim lamaran ke {{ $h->lowongan->perusahaan->nama }} dengan skor {{ Format::skor($h->skor) }}%?">
                        @csrf
                        <button class="btn btn-primary btn-sm btn-block">Lamar</button>
                    </form>
                @endif
            </div>
        </div>
    </article>
@empty
    <div class="panel empty">
        <i class="bi bi-search"></i>
        <p>{{ request()->boolean('hanya_cocok') ? 'Belum ada lowongan yang semua syarat wajibnya kamu penuhi. Coba matikan filter atau tambah keahlian.' : 'Tidak ada lowongan yang cocok dengan kata kunci itu.' }}</p>
        <a href="{{ route('pencari.lowongan.index') }}" class="btn btn-line btn-sm">Tampilkan semua</a>
    </div>
@endforelse
@endsection
