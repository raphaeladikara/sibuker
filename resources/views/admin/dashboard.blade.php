@extends('layouts.app')

@section('title', 'Ringkasan admin')

@section('content')
@php
    use App\Models\Pengguna;
    $maks = max(1, $peta->max(fn ($p) => max($p->diminta, $p->diklaim)));
@endphp
<div class="page-head">
    <div>
        <h1>Ringkasan sistem</h1>
        <p class="sub">Data dihitung langsung dari basis data setiap kali halaman dibuka.</p>
    </div>
</div>

<dl class="strip">
    <div><dt>Akun</dt><dd>{{ $ringkasan['pengguna'] }}</dd></div>
    <div><dt>Sertifikat di antrean</dt><dd @class(['perlu' => $ringkasan['antrean']])>{{ $ringkasan['antrean'] }}</dd></div>
    <div><dt>Lowongan tayang</dt><dd>{{ $ringkasan['lowongan'] }}</dd></div>
    <div><dt>Lamaran</dt><dd>{{ $ringkasan['lamaran'] }}</dd></div>
</dl>

<div class="cols cols-side">
    <div class="panel">
        <div class="panel-head">
            <h2>Kebutuhan dan ketersediaan keahlian</h2>
            <div class="row small muted" style="gap:14px">
                <span class="row" style="gap:6px"><span style="width:10px;height:10px;background:var(--rose);display:inline-block"></span>diminta lowongan</span>
                <span class="row" style="gap:6px"><span style="width:10px;height:10px;background:#c9ccd8;display:inline-block"></span>diklaim</span>
                <span class="row" style="gap:6px"><span style="width:10px;height:10px;background:var(--centang);display:inline-block"></span>terverifikasi</span>
            </div>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Keahlian</th><th style="width:48%"></th><th class="kanan">Diminta</th><th class="kanan">Diklaim</th><th class="kanan">Terverifikasi</th></tr></thead>
                <tbody>
                @foreach ($peta as $p)
                    <tr>
                        <td class="utama">{{ $p->nama }} @unless ($p->aktif)<span class="pill">nonaktif</span>@endunless</td>
                        <td>
                            <div style="display:grid;gap:3px">
                                <span style="height:5px;width:{{ $p->diminta / $maks * 100 }}%;background:var(--rose);min-width:{{ $p->diminta ? 2 : 0 }}px"></span>
                                <span style="height:5px;width:{{ $p->diklaim / $maks * 100 }}%;background:#c9ccd8;position:relative;min-width:{{ $p->diklaim ? 2 : 0 }}px">
                                    <span style="position:absolute;inset:0 auto 0 0;width:{{ $p->diklaim ? $p->terverifikasi / $p->diklaim * 100 : 0 }}%;background:var(--centang)"></span>
                                </span>
                            </div>
                        </td>
                        <td class="kanan">{{ $p->diminta }}</td>
                        <td class="kanan">{{ $p->diklaim }}</td>
                        <td class="kanan">{{ $p->terverifikasi }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <aside class="panel">
        <div class="panel-head"><h2>Akun terbaru</h2><a href="{{ route('admin.pengguna.index') }}">Semua akun</a></div>
        <ul class="list">
            @foreach ($penggunaBaru as $u)
                <li>
                    @include('partials.avatar', ['orang' => $u])
                    <div class="isi">
                        <a href="{{ route('admin.pengguna.edit', $u) }}" class="judul">{{ $u->nama }}</a>
                        <div class="ket">{{ collect($u->daftarPeran())->map(fn ($r) => Pengguna::PERAN[$r])->join(', ') ?: 'Tanpa peran' }}</div>
                    </div>
                    @include('partials.status', ['status' => $u->status_akun])
                </li>
            @endforeach
        </ul>
    </aside>
</div>
@endsection
