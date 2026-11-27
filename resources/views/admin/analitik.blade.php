@extends('layouts.app')

@section('title', 'Analitik data warehouse')

@section('content')
@if (isset($gagal))
    <div class="page-head workspace-intro">
        <div>
            <span class="eyebrow">DATA WAREHOUSE</span>
            <h1>Analitik pasar kerja</h1>
            <p class="sub">Database sibuker_dw belum bisa dibuka. Pastikan database itu ada di server MySQL yang sama
                dan sudah diisi lewat job ETL Pentaho, atau atur DW_DATABASE di .env.</p>
        </div>
    </div>
    <div class="panel"><div class="empty"><i class="bi bi-database-x"></i><p>{{ \Illuminate\Support\Str::limit($gagal, 160) }}</p></div></div>
@else
@php
    $angka = fn ($n, $d = 0) => $n === null ? '–' : number_format($n, $d, ',', '.');
    $sekarang = $filter + ['tingkat' => $tingkat];
    $url = fn (array $ubah = []) => route('admin.analitik', array_filter(array_merge($sekarang, $ubah), fn ($v) => $v !== null && $v !== ''));
    $selisih = function ($baru, $lama, $satuan = '') use ($angka) {
        if ($lama === null || $baru === null) return null;
        $d = $baru - $lama;
        return ($d > 0 ? '+' : ($d < 0 ? '−' : '±')) . $angka(abs($d), $satuan === '' ? 0 : 1) . $satuan;
    };
    // Skala sekuensial satu warna (biru) untuk heatmap, terang = kecil, gelap = besar.
    $ramp = ['#eaf2fd', '#cde2fb', '#b7d3f6', '#9ec5f4', '#86b6ef', '#6da7ec', '#5598e7', '#3987e5', '#2a78d6', '#256abf', '#1c5cab', '#184f95'];
    $wilayahList = $pilihan['wilayah'];
@endphp

<div class="page-head workspace-intro">
    <div>
        <span class="eyebrow">DATA WAREHOUSE · SIBUKER_DW</span>
        <h1>Analitik pasar kerja</h1>
        <p class="sub">Angka di halaman ini dibaca dari data warehouse hasil ETL Pentaho, bukan dari database aplikasi.
            Klik batang, sel, atau filter untuk drill-down, roll-up, slice, dan dice.</p>
    </div>
</div>

<form class="panel dw-filter" method="GET" action="{{ route('admin.analitik') }}">
    <input type="hidden" name="tingkat" value="{{ $tingkat }}">
    <label class="field">
        <span class="small muted">Kuartal <span class="dw-op">slice</span></span>
        <select name="kuartal" class="select">
            <option value="">Semua kuartal</option>
            @foreach ($pilihan['kuartal'] as $k)<option value="{{ $k }}" @selected($filter['kuartal'] === $k)>{{ $k }} 2026</option>@endforeach
        </select>
    </label>
    <label class="field">
        <span class="small muted">Wilayah lowongan <span class="dw-op">dice</span></span>
        <select name="wilayah" class="select">
            <option value="">Semua wilayah</option>
            @foreach ($pilihan['wilayah'] as $w)<option value="{{ $w }}" @selected($filter['wilayah'] === $w)>{{ $w }}</option>@endforeach
        </select>
    </label>
    <label class="field">
        <span class="small muted">Bidang <span class="dw-op">dice</span></span>
        <select name="bidang" class="select">
            <option value="">Semua bidang</option>
            @foreach ($pilihan['bidang'] as $b)<option value="{{ $b }}" @selected($filter['bidang'] === $b)>{{ $b }}</option>@endforeach
        </select>
    </label>
    <div class="dw-filter-aksi">
        <button class="btn btn-primary"><i class="bi bi-funnel"></i>Terapkan</button>
        @if (array_filter($filter))<a class="btn btn-line" href="{{ route('admin.analitik', ['tingkat' => $tingkat]) }}">Hapus filter</a>@endif
    </div>
</form>

@if (array_filter($filter))
    <div class="dw-aktif" aria-label="Filter aktif">
        <span class="small muted">Operasi aktif:</span>
        @if ($filter['kuartal'])<a class="dw-chip" href="{{ $url(['kuartal' => null]) }}">Slice kuartal = {{ $filter['kuartal'] }} <i class="bi bi-x"></i></a>@endif
        @if ($filter['wilayah'])<a class="dw-chip" href="{{ $url(['wilayah' => null]) }}">Dice wilayah = {{ $filter['wilayah'] }} <i class="bi bi-x"></i></a>@endif
        @if ($filter['bidang'])<a class="dw-chip" href="{{ $url(['bidang' => null]) }}">Dice bidang = {{ $filter['bidang'] }} <i class="bi bi-x"></i></a>@endif
    </div>
@endif

<dl class="strip dw-kpi">
    <div>
        <dt>Lamaran masuk</dt>
        <dd>{{ $angka($kpi['lamaran']) }}</dd>
        @if ($kpiSebelum)<span class="dw-delta">{{ $selisih($kpi['lamaran'], $kpiSebelum['lamaran']) }} dari {{ $sebelum }}</span>@endif
    </div>
    <div>
        <dt>Lamaran diterima</dt>
        <dd>{{ $angka($kpi['persen_diterima'], 1) }}%</dd>
        <span class="dw-delta">{{ $angka($kpi['diterima']) }} lamaran @if ($kpiSebelum)· {{ $selisih($kpi['persen_diterima'], $kpiSebelum['persen_diterima'], ' poin') }} dari {{ $sebelum }}@endif</span>
    </div>
    <div>
        <dt>Verifikasi disetujui</dt>
        <dd>{{ $angka($kpi['persen_setuju'], 1) }}%</dd>
        <span class="dw-delta">dari {{ $angka($kpi['verifikasi']) }} keputusan final</span>
    </div>
    <div>
        <dt>Rata-rata lama verifikasi</dt>
        <dd>{{ $angka($kpi['jam'], 1) }} jam</dd>
        @if ($kpiSebelum)<span class="dw-delta">{{ $selisih($kpi['jam'], $kpiSebelum['jam'], ' jam') }} dari {{ $sebelum }}</span>@else<span class="dw-delta">total jam ÷ jumlah verifikasi</span>@endif
    </div>
</dl>

<div class="dw-grid">
    {{-- Tren lamaran: drill-down / roll-up pada hierarki dim_waktu --}}
    <section class="panel dw-card" aria-labelledby="judul-tren">
        <div class="panel-head">
            <div>
                <h2 id="judul-tren">Lamaran per {{ $tingkat }}</h2>
                <p class="small muted">Klik batang untuk drill-down. Kena semua filter.</p>
            </div>
            <div class="segmented dw-seg" role="group" aria-label="Tingkat waktu">
                @foreach (['tahun' => 'Tahun', 'kuartal' => 'Kuartal', 'bulan' => 'Bulan'] as $t => $label)
                    <a href="{{ $url(['tingkat' => $t]) }}" @class(['aktif' => $tingkat === $t]) @if ($tingkat === $t) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="dw-body">
            <nav class="dw-jejak small" aria-label="Posisi drill">
                <a href="{{ $url(['tingkat' => 'tahun', 'kuartal' => null]) }}">Semua waktu</a>
                <i class="bi bi-chevron-right"></i><a href="{{ $url(['tingkat' => 'kuartal', 'kuartal' => null]) }}">2026</a>
                @if ($filter['kuartal'])<i class="bi bi-chevron-right"></i><a href="{{ $url(['tingkat' => 'bulan']) }}">{{ $filter['kuartal'] }}</a>@endif
                @if ($tingkat !== 'tahun')<span class="muted">· {{ $tingkat === 'bulan' ? 'per bulan' : 'per kuartal' }}</span>@endif
            </nav>
            @php $maks = max(1, collect($tren)->max('n')); @endphp
            @if (empty($tren))
                <div class="empty"><i class="bi bi-bar-chart"></i><p>Tidak ada lamaran untuk filter ini.</p></div>
            @else
                <div class="dw-kolom" style="--n: {{ count($tren) }}">
                    @foreach ($tren as $t)
                        @php
                            $tujuan = match ($tingkat) {
                                'tahun' => $url(['tingkat' => 'kuartal']),
                                'kuartal' => $url(['tingkat' => 'bulan', 'kuartal' => $t->kunci]),
                                default => null,
                            };
                            $tip = "{$t->label}: " . $angka($t->n) . ' lamaran, ' . $angka($t->diterima) . ' diterima (' . $angka($t->n ? $t->diterima / $t->n * 100 : 0, 1) . '%)';
                        @endphp
                        <{{ $tujuan ? 'a' : 'div' }} class="dw-kolom-item" @if ($tujuan) href="{{ $tujuan }}" @else tabindex="0" @endif data-tip="{{ $tip }}" aria-label="{{ $tip }}">
                            <span class="dw-nilai">{{ $angka($t->n) }}</span>
                            <span class="dw-batang" style="height: {{ $t->n / $maks * 100 }}%"></span>
                            <span class="dw-label">{{ $t->label }}</span>
                        </{{ $tujuan ? 'a' : 'div' }}>
                    @endforeach
                </div>
            @endif
            <details class="dw-tabel">
                <summary>Lihat tabel</summary>
                <table class="table">
                    <thead><tr><th>{{ ucfirst($tingkat) }}</th><th class="kanan">Lamaran</th><th class="kanan">Diterima</th><th class="kanan">% diterima</th></tr></thead>
                    <tbody>
                    @foreach ($tren as $t)
                        <tr><td>{{ $t->label }}</td><td class="kanan">{{ $angka($t->n) }}</td><td class="kanan">{{ $angka($t->diterima) }}</td><td class="kanan">{{ $angka($t->n ? $t->diterima / $t->n * 100 : 0, 1) }}%</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </details>
        </div>
    </section>

    {{-- Heatmap bidang x wilayah: klik sel untuk dice --}}
    <section class="panel dw-card" aria-labelledby="judul-silang">
        <div class="panel-head">
            <div>
                <h2 id="judul-silang">Lamaran per bidang dan wilayah</h2>
                <p class="small muted">Klik sel untuk dice ke bidang dan wilayah itu. Kena filter kuartal.</p>
            </div>
        </div>
        @php
            $semuaSel = collect($silang)->flatten();
            $maksSel = max(1, $semuaSel->max());
        @endphp
        <div class="table-wrap">
            <table class="table dw-heat">
                <thead><tr><th>Bidang</th>@foreach ($wilayahList as $w)<th class="kanan">{{ $w }}</th>@endforeach<th class="kanan">Total</th></tr></thead>
                <tbody>
                @foreach ($pilihan['bidang'] as $b)
                    <tr>
                        <th scope="row">{{ $b }}</th>
                        @foreach ($wilayahList as $w)
                            @php
                                $n = $silang[$b][$w] ?? 0;
                                $step = $n ? min(count($ramp) - 1, (int) floor($n / $maksSel * (count($ramp) - 1))) : 0;
                                $dipilih = $filter['bidang'] === $b && $filter['wilayah'] === $w;
                                $tip = "$b di $w: " . $angka($n) . ' lamaran';
                            @endphp
                            <td class="kanan">
                                <a href="{{ $dipilih ? $url(['bidang' => null, 'wilayah' => null]) : $url(['bidang' => $b, 'wilayah' => $w]) }}"
                                   @class(['dw-sel', 'gelap' => $step >= 8, 'dipilih' => $dipilih])
                                   style="background: {{ $ramp[$step] }}" data-tip="{{ $tip }}" aria-label="{{ $tip }}{{ $dipilih ? ', sedang dipilih' : '' }}">{{ $angka($n) }}</a>
                            </td>
                        @endforeach
                        <td class="kanan utama">{{ $angka(array_sum($silang[$b] ?? [])) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th scope="row">Total</th>
                        @foreach ($wilayahList as $w)<td class="kanan utama">{{ $angka(collect($silang)->sum(fn ($r) => $r[$w] ?? 0)) }}</td>@endforeach
                        <td class="kanan utama">{{ $angka($semuaSel->sum()) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="dw-legenda small muted">
            <span>Sedikit</span>
            <span class="dw-ramp" style="background: linear-gradient(90deg, {{ implode(', ', $ramp) }})"></span>
            <span>Banyak</span>
        </div>
    </section>

    {{-- Verifikasi: dua grafik terpisah karena satuannya beda (persen dan jam) --}}
    <section class="panel dw-card" aria-labelledby="judul-verifikasi">
        <div class="panel-head">
            <div>
                <h2 id="judul-verifikasi">Persen disetujui per jenis bukti</h2>
                <p class="small muted">Dari fakta_verifikasi. Hanya kena filter kuartal karena fakta ini tidak punya dimensi wilayah dan bidang.</p>
            </div>
        </div>
        <div class="dw-body">
            <ul class="dw-hbar">
                @foreach ($verifikasi as $v)
                    @php $p = $v->n ? $v->setuju / $v->n * 100 : 0; $tip = "{$v->nama}: {$v->setuju} dari {$v->n} disetujui (" . $angka($p, 1) . '%)'; @endphp
                    <li tabindex="0" data-tip="{{ $tip }}" aria-label="{{ $tip }}">
                        <span class="dw-hbar-label">{{ $v->nama }}</span>
                        <span class="dw-hbar-track"><span class="dw-hbar-isi" style="width: {{ $p }}%"></span></span>
                        <span class="dw-hbar-nilai">{{ $angka($p, 1) }}%</span>
                    </li>
                @endforeach
            </ul>
            <details class="dw-tabel">
                <summary>Lihat tabel</summary>
                <table class="table">
                    <thead><tr><th>Jenis bukti</th><th class="kanan">Verifikasi</th><th class="kanan">Disetujui</th><th class="kanan">%</th></tr></thead>
                    <tbody>@foreach ($verifikasi as $v)<tr><td>{{ $v->nama }}</td><td class="kanan">{{ $v->n }}</td><td class="kanan">{{ $v->setuju }}</td><td class="kanan">{{ $angka($v->n ? $v->setuju / $v->n * 100 : 0, 1) }}%</td></tr>@endforeach</tbody>
                </table>
            </details>
        </div>
    </section>

    <section class="panel dw-card" aria-labelledby="judul-instansi">
        <div class="panel-head">
            <div>
                <h2 id="judul-instansi">Rata-rata lama verifikasi per instansi</h2>
                <p class="small muted">Jam dari berkas diunggah sampai diputuskan. Kena filter kuartal.</p>
            </div>
        </div>
        <div class="dw-body">
            @php $maksJam = max(1, collect($instansi)->max(fn ($i) => $i->n ? $i->jam / $i->n : 0)); @endphp
            <ul class="dw-hbar">
                @foreach ($instansi as $i)
                    @php $j = $i->n ? $i->jam / $i->n : 0; $tip = "{$i->instansi}: " . $angka($j, 1) . " jam rata-rata dari {$i->n} verifikasi"; @endphp
                    <li tabindex="0" data-tip="{{ $tip }}" aria-label="{{ $tip }}">
                        <span class="dw-hbar-label">{{ $i->instansi }}</span>
                        <span class="dw-hbar-track"><span class="dw-hbar-isi" style="width: {{ $j / $maksJam * 100 }}%"></span></span>
                        <span class="dw-hbar-nilai">{{ $angka($j, 1) }} jam</span>
                    </li>
                @endforeach
            </ul>
            <details class="dw-tabel">
                <summary>Lihat tabel</summary>
                <table class="table">
                    <thead><tr><th>Instansi</th><th class="kanan">Verifikasi</th><th class="kanan">Total jam</th><th class="kanan">Rata-rata</th></tr></thead>
                    <tbody>@foreach ($instansi as $i)<tr><td>{{ $i->instansi }}</td><td class="kanan">{{ $i->n }}</td><td class="kanan">{{ $angka($i->jam) }}</td><td class="kanan">{{ $angka($i->n ? $i->jam / $i->n : 0, 1) }}</td></tr>@endforeach</tbody>
                </table>
            </details>
        </div>
    </section>

    {{-- Kebutuhan keahlian: bagian dari keseluruhan, jadi batang bertumpuk dua segmen --}}
    <section class="panel dw-card dw-lebar" aria-labelledby="judul-keahlian">
        <div class="panel-head">
            <div>
                <h2 id="judul-keahlian">Keahlian yang paling dicari lowongan</h2>
                <p class="small muted">Jumlah syarat keahlian pada lowongan terbit, dari fakta_kebutuhan_keahlian. Kena semua filter.</p>
            </div>
            <div class="dw-legenda-seri small">
                <span><i class="dw-swatch" style="background: var(--dw-1)"></i>Wajib terverifikasi</span>
                <span><i class="dw-swatch" style="background: var(--dw-2)"></i>Syarat lain</span>
            </div>
        </div>
        <div class="dw-body">
            @php $maksKeb = max(1, collect($keahlian)->max('n')); @endphp
            @if (empty($keahlian))
                <div class="empty"><i class="bi bi-bar-chart"></i><p>Tidak ada syarat keahlian untuk filter ini.</p></div>
            @else
                <ul class="dw-hbar">
                    @foreach ($keahlian as $k)
                        @php $tip = "{$k->nama}: {$k->n} syarat, {$k->wv} wajib terverifikasi"; @endphp
                        <li tabindex="0" data-tip="{{ $tip }}" aria-label="{{ $tip }}">
                            <span class="dw-hbar-label">{{ $k->nama }}</span>
                            <span class="dw-hbar-track">
                                <span class="dw-hbar-tumpuk" style="width: {{ $k->n / $maksKeb * 100 }}%">
                                    @if ($k->wv)<span style="flex: {{ $k->wv }}; background: var(--dw-1)"></span>@endif
                                    @if ($k->n - $k->wv)<span style="flex: {{ $k->n - $k->wv }}; background: var(--dw-2)"></span>@endif
                                </span>
                            </span>
                            <span class="dw-hbar-nilai">{{ $k->n }}@if ($k->wv) <span class="muted">· {{ $k->wv }} terverifikasi</span>@endif</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <details class="dw-tabel">
                <summary>Lihat tabel</summary>
                <table class="table">
                    <thead><tr><th>Keahlian</th><th class="kanan">Syarat</th><th class="kanan">Wajib terverifikasi</th></tr></thead>
                    <tbody>@foreach ($keahlian as $k)<tr><td>{{ $k->nama }}</td><td class="kanan">{{ $k->n }}</td><td class="kanan">{{ $k->wv }}</td></tr>@endforeach</tbody>
                </table>
            </details>
        </div>
    </section>
</div>
<div class="dw-tip" role="tooltip" hidden></div>
@endif
@endsection
