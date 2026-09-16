@extends('layouts.app')

@section('title', 'Pelamar ' . $lowongan->posisi)

@section('content')
@php use App\Support\Format; @endphp
<a href="{{ route('perusahaan.lowongan.show', $lowongan) }}" class="back"><i class="bi bi-arrow-return-left"></i> {{ $lowongan->posisi }}</a>
<div class="page-head">
    <div>
        <h1>Pelamar</h1>
        <p class="sub">{{ $lowongan->posisi }} &middot; urut dari skor saat melamar, tertinggi di atas.</p>
    </div>
</div>

<div class="panel">
    @if ($pelamar->isEmpty())
        <div class="empty"><i class="bi bi-people"></i><p>Belum ada yang melamar lowongan ini.</p></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Pelamar</th><th>Keahlian yang relevan</th><th>Syarat wajib</th><th class="kanan">Skor</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($pelamar as $p)
                    <tr>
                        <td>
                            <div class="row" style="gap:12px">
                                @include('partials.avatar', ['orang' => $p->lamaran->pencariKerja->pengguna])
                                <div>
                                    <div class="utama"><a href="{{ route('perusahaan.pelamar.show', $p->lamaran) }}">{{ $p->lamaran->pencariKerja->pengguna->nama }}</a></div>
                                    <div class="sekunder">{{ $p->lamaran->pencariKerja->headline ?: '-' }} &middot; {{ Format::tanggal($p->lamaran->dilamar_pada) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="wrap-gap">
                                @foreach ($p->rincian as $r)
                                    @if ($r->klaim)
                                        <span @class(['chip', 'ok' => $r->ok, 'gagal' => ! $r->ok]) title="{{ $r->alasan }}">
                                            {{ $r->syarat->keahlian->nama }}
                                            @if ($r->klaim->terverifikasi)<i class="bi bi-patch-check-fill"></i>@endif
                                        </span>
                                    @endif
                                @endforeach
                                @if (collect($p->rincian)->whereNull('klaim')->isNotEmpty())
                                    <span class="chip redup">{{ collect($p->rincian)->whereNull('klaim')->count() }} tidak dicantumkan</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($p->memenuhi_wajib)
                                <span style="color:var(--green)"><i class="bi bi-check-lg"></i> Terpenuhi</span>
                            @else
                                <span style="color:var(--rose)"><i class="bi bi-x-lg"></i> Kurang</span>
                            @endif
                        </td>
                        <td class="kanan" style="font-weight:500">{{ Format::skor($p->lamaran->skor_kecocokan) }}%</td>
                        <td>@include('partials.status', ['status' => $p->lamaran->status])</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
