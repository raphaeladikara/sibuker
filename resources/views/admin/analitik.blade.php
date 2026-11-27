@extends('layouts.panel')

@section('title', 'Analitik pasar kerja')
@section('judul', 'Analitik pasar kerja')

@php
    $angka = fn ($n, $d = 0) => $n === null ? '–' : number_format($n, $d, ',', '.');
@endphp

@if (isset($gagal))
    @section('subjudul', 'Data warehouse sibuker_dw belum bisa dibuka')
    @section('content')
        <div class="card">
            <div class="kosong">
                <i class="bi bi-database-x"></i>
                <b>sibuker_dw belum tersedia</b>
                <span>Jalankan start-dashboard.bat atau job ETL Pentaho, lalu muat ulang halaman ini.</span>
                <span class="faint">{{ \Illuminate\Support\Str::limit($gagal, 160) }}</span>
            </div>
        </div>
    @endsection
@else
@php
    $sekarang = $filter + ['tingkat' => $tingkat];
    $url = fn (array $ubah = []) => route('admin.analitik', array_filter(array_merge($sekarang, $ubah), fn ($v) => $v !== null && $v !== ''));
    $tautan = fn ($u) => is_array($u) ? $url($u) : $u;
    // Pil perubahan dari kuartal sebelumnya. $baikNaik menentukan apakah kenaikan itu kabar baik.
    $pil = function ($baru, $lama, $satuan = '', $d = 0, $baikNaik = true) use ($angka) {
        if ($lama === null || $baru === null) return null;
        $s = $baru - $lama;
        $kelas = abs($s) < 0.05 ? '' : (($s > 0) === $baikNaik ? 'up' : 'down');
        return [($s > 0 ? '+' : ($s < 0 ? '−' : '±')) . $angka(abs($s), $d) . $satuan, $kelas];
    };
    $ramp = ['#eef4fd', '#d9e7fb', '#c3d9f8', '#a9c4f7', '#8fb2f2', '#759fee', '#5b8ff0', '#3f7de6', '#2a6fd9', '#1b62c9', '#0a56b5', '#084a9b'];
    $namaTingkat = ['tahun' => 'tahun', 'kuartal' => 'kuartal', 'bulan' => 'bulan'][$tingkat];
@endphp

@section('subjudul')
    Data warehouse sibuker_dw · hasil ETL Pentaho Januari sampai Juni 2026
@endsection

@section('aksi-atas')
    <form class="filter" method="GET" action="{{ route('admin.analitik') }}" aria-label="Filter OLAP">
        <input type="hidden" name="tingkat" value="{{ $tingkat }}">
        <label @class(['pilih', 'aktif' => $filter['kuartal']]) title="Slice: potong semua data ke satu kuartal">
            <i class="bi bi-calendar3"></i><span class="sr">Kuartal</span>
            <select name="kuartal">
                <option value="">Semua kuartal</option>
                @foreach ($pilihan['kuartal'] as $k)<option value="{{ $k }}" @selected($filter['kuartal'] === $k)>{{ $k }} 2026</option>@endforeach
            </select>
        </label>
        <label @class(['pilih', 'aktif' => $filter['wilayah']]) title="Dice: wilayah lowongan">
            <i class="bi bi-geo-alt"></i><span class="sr">Wilayah</span>
            <select name="wilayah">
                <option value="">Semua wilayah</option>
                @foreach ($pilihan['wilayah'] as $w)<option value="{{ $w }}" @selected($filter['wilayah'] === $w)>{{ $w }}</option>@endforeach
            </select>
        </label>
        <label @class(['pilih', 'aktif' => $filter['bidang']]) title="Dice: bidang lowongan">
            <i class="bi bi-briefcase"></i><span class="sr">Bidang</span>
            <select name="bidang">
                <option value="">Semua bidang</option>
                @foreach ($pilihan['bidang'] as $b)<option value="{{ $b }}" @selected($filter['bidang'] === $b)>{{ $b }}</option>@endforeach
            </select>
        </label>
        <button class="btn-terapkan" type="submit">Terapkan</button>
        @if (array_filter($filter))<a class="ghost" href="{{ route('admin.analitik', ['tingkat' => $tingkat]) }}"><i class="bi bi-x-lg"></i>Reset</a>@endif
    </form>
@endsection

@section('content')
@if (array_filter($filter))
    <div class="aktif-bar" aria-label="Operasi OLAP aktif">
        <span>Operasi aktif</span>
        @if ($filter['kuartal'])<a class="chip" href="{{ $url(['kuartal' => null]) }}"><em>Slice</em> kuartal {{ $filter['kuartal'] }} <i class="bi bi-x"></i></a>@endif
        @if ($filter['wilayah'])<a class="chip" href="{{ $url(['wilayah' => null]) }}"><em>Dice</em> {{ $filter['wilayah'] }} <i class="bi bi-x"></i></a>@endif
        @if ($filter['bidang'])<a class="chip" href="{{ $url(['bidang' => null]) }}"><em>Dice</em> {{ $filter['bidang'] }} <i class="bi bi-x"></i></a>@endif
    </div>
@endif

{{-- KPI --}}
<section class="grid k4" aria-label="Ringkasan KPI">
    @php $p = $kpiSebelum ? $pil($kpi['lamaran'], $kpiSebelum['lamaran']) : null; $maksBulan = max(1, collect($bulanan)->max('n')); @endphp
    <div class="card kpi">
        <div class="kpi-h"><span>Lamaran masuk</span><i class="bi bi-info-circle" title="SUM(jumlah_lamaran) dari fakta_lamaran"></i></div>
        <div class="kpi-v">
            <span class="tile info"><i class="bi bi-send"></i></span>
            <b>{{ $angka($kpi['lamaran']) }}</b>
            @if ($p)<span class="tren {{ $p[1] }}">{{ $p[0] }}</span>@endif
        </div>
        <div class="kpi-f">
            <span class="mini" aria-hidden="true">@foreach ($bulanan as $m)<i style="height: {{ max(8, $m->n / $maksBulan * 100) }}%"></i>@endforeach</span>
            <span>per bulan, {{ collect($bulanan)->first()->label ?? '' }} sampai {{ collect($bulanan)->last()->label ?? '' }} @if ($p) · pil dibanding {{ $sebelum }}@endif</span>
        </div>
    </div>

    @php $p = $kpiSebelum ? $pil($kpi['persen_diterima'], $kpiSebelum['persen_diterima'], ' poin', 1) : null; @endphp
    <div class="card kpi">
        <div class="kpi-h"><span>Lamaran diterima</span><i class="bi bi-info-circle" title="SUM(diterima) / SUM(jumlah_lamaran)"></i></div>
        <div class="kpi-v">
            <span class="tile ok"><i class="bi bi-person-check"></i></span>
            <b>{{ $angka($kpi['persen_diterima'], 1) }}<small>%</small></b>
            @if ($p)<span class="tren {{ $p[1] }}">{{ $p[0] }}</span>@endif
        </div>
        <div class="kpi-f">
            <span class="meter"><i style="width: {{ $kpi['persen_diterima'] ?? 0 }}%"></i></span>
            <span>{{ $angka($kpi['diterima']) }} dari {{ $angka($kpi['lamaran']) }} lamaran</span>
        </div>
    </div>

    @php $p = $kpiSebelum ? $pil($kpi['persen_setuju'], $kpiSebelum['persen_setuju'], ' poin', 1) : null; @endphp
    <div class="card kpi">
        <div class="kpi-h"><span>Verifikasi disetujui</span><i class="bi bi-info-circle" title="SUM(disetujui) / SUM(jumlah_verifikasi). Hanya kena filter kuartal."></i></div>
        <div class="kpi-v">
            <span class="tile info"><i class="bi bi-patch-check"></i></span>
            <b>{{ $angka($kpi['persen_setuju'], 1) }}<small>%</small></b>
            @if ($p)<span class="tren {{ $p[1] }}">{{ $p[0] }}</span>@endif
        </div>
        <div class="kpi-f">
            <span class="meter"><i style="width: {{ $kpi['persen_setuju'] ?? 0 }}%"></i></span>
            <span>{{ $angka($kpi['setuju']) }} dari {{ $angka($kpi['verifikasi']) }} keputusan</span>
        </div>
    </div>

    @php $p = $kpiSebelum ? $pil($kpi['jam'], $kpiSebelum['jam'], ' jam', 1, false) : null; @endphp
    <div class="card kpi">
        <div class="kpi-h"><span>Rata-rata lama verifikasi</span><i class="bi bi-info-circle" title="SUM(durasi_jam) / SUM(jumlah_verifikasi). Hanya kena filter kuartal."></i></div>
        <div class="kpi-v">
            <span class="tile warn"><i class="bi bi-hourglass-split"></i></span>
            <b>{{ $angka($kpi['jam'], 1) }}<small>jam</small></b>
            @if ($p)<span class="tren {{ $p[1] }}">{{ $p[0] }}</span>@endif
        </div>
        <div class="kpi-f"><span>dari berkas diunggah sampai diputuskan @if ($p) · pil dibanding {{ $sebelum }}@endif</span></div>
    </div>
</section>

<section class="grid g-21 rata-atas">
    {{-- Tren lamaran: drill-down dan roll-up pada hierarki dim_waktu --}}
    <div class="card" id="kartu-tren">
        <div class="card-h">
            <div>
                <h2>Lamaran per {{ $namaTingkat }}</h2>
                <div class="sub">Klik batang untuk drill-down, atau pilih tingkat di kanan untuk roll-up</div>
            </div>
            <span class="sp"></span>
            <nav class="seg" aria-label="Tingkat waktu">
                @foreach (['tahun' => 'Tahun', 'kuartal' => 'Kuartal', 'bulan' => 'Bulan'] as $t => $label)
                    <a href="{{ $url(['tingkat' => $t]) }}" @if ($tingkat === $t) aria-current="true" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
        <nav class="jejak" aria-label="Posisi drill">
            <a href="{{ $url(['tingkat' => 'tahun', 'kuartal' => null]) }}">Semua waktu</a>
            <i class="bi bi-chevron-right"></i><a href="{{ $url(['tingkat' => 'kuartal', 'kuartal' => null]) }}">2026</a>
            @if ($filter['kuartal'])<i class="bi bi-chevron-right"></i><a href="{{ $url(['tingkat' => 'bulan']) }}">{{ $filter['kuartal'] }}</a>@endif
            <span>· per {{ $namaTingkat }}</span>
        </nav>
        @if (empty($tren))
            <div class="kosong"><i class="bi bi-bar-chart"></i>Tidak ada lamaran untuk filter ini.</div>
        @else
            @php
                $maks = collect($tren)->max('n');
                $langkah = $maks <= 0 ? 1 : 10 ** max(0, floor(log10($maks)) - 1) * 5;
                $atas = max($langkah, ceil($maks / $langkah) * $langkah);
            @endphp
            <div class="batang" style="--n: {{ count($tren) }}">
                <div class="sumbu" aria-hidden="true">
                    @foreach ([0, 0.5, 1] as $r)<span style="bottom: {{ $r * 100 }}%">{{ $angka($atas * $r) }}</span>@endforeach
                </div>
                @foreach ([0.5, 1] as $r)<div class="garis" style="bottom: calc(28px + (100% - 28px) * {{ $r }})"></div>@endforeach
                @foreach ($tren as $t)
                    @php
                        $tujuan = match ($tingkat) {
                            'tahun' => $url(['tingkat' => 'kuartal']),
                            'kuartal' => $url(['tingkat' => 'bulan', 'kuartal' => $t->kunci]),
                            default => null,
                        };
                        $persen = $t->n ? $t->diterima / $t->n * 100 : 0;
                        $tip = "{$t->label}|" . $angka($t->n) . ' lamaran|' . $angka($t->diterima) . ' diterima (' . $angka($persen, 1) . '%)' . ($tujuan ? '|klik untuk drill-down' : '');
                    @endphp
                    <{{ $tujuan ? 'a' : 'div' }} @class(['kolom', 'sorot' => $loop->last]) @if ($tujuan) href="{{ $tujuan }}" @else tabindex="0" @endif
                        data-tip="{{ $tip }}" aria-label="{{ str_replace('|', ', ', $tip) }}">
                        <span class="track"><span class="isi" style="height: {{ $t->n / $atas * 100 }}%"></span></span>
                        <span class="nilai" style="bottom: {{ $t->n / $atas * 100 }}%">{{ $angka($t->n) }}</span>
                        <span class="lbl-x">{{ $t->label }}</span>
                    </{{ $tujuan ? 'a' : 'div' }}>
                @endforeach
            </div>
        @endif
        @isset($catatan['tren'])<div class="note"><i class="bi bi-info-circle"></i><span>{{ $catatan['tren'] }}</span></div>@endisset
        <details class="data">
            <summary>Lihat tabel</summary>
            <div class="tbl-wrap"><table class="tbl">
                <thead><tr><th>{{ ucfirst($namaTingkat) }}</th><th class="r">Lamaran</th><th class="r">Diterima</th><th class="r">% diterima</th></tr></thead>
                <tbody>@foreach ($tren as $t)<tr><td>{{ $t->label }}</td><td class="r">{{ $angka($t->n) }}</td><td class="r">{{ $angka($t->diterima) }}</td><td class="r">{{ $angka($t->n ? $t->diterima / $t->n * 100 : 0, 1) }}%</td></tr>@endforeach</tbody>
            </table></div>
        </details>
    </div>

    {{-- Temuan yang dihitung dari warehouse --}}
    <div class="card" id="kartu-perlu">
        <div class="card-h">
            <div>
                <h2>Perlu ditindak</h2>
                <div class="sub">Dihitung ulang dari warehouse sesuai filter</div>
            </div>
        </div>
        @if (empty($perlu))
            <div class="kosong"><i class="bi bi-check2-circle"></i>Tidak ada temuan untuk filter ini.</div>
        @else
            <div class="list">
                @foreach ($perlu as $x)
                    <a class="row" href="{{ $tautan($x['url']) }}">
                        <span class="tile {{ $x['nada'] }}"><i class="bi {{ $x['ikon'] }}"></i></span>
                        <span class="t"><b>{{ $x['judul'] }}</b><span>{{ $x['ket'] }}</span></span>
                        <span class="pill {{ $x['nada'] }}"><i></i>{{ $x['pill'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="grid g-21 rata-atas">
    {{-- Heatmap bidang x wilayah: klik sel untuk dice --}}
    <div class="card" id="kartu-silang">
        <div class="card-h">
            <div>
                <h2>Lamaran per bidang dan wilayah</h2>
                <div class="sub">Klik sel untuk dice ke bidang dan wilayah itu. Hanya kena filter kuartal.</div>
            </div>
        </div>
        @php $semua = collect($silang)->flatten(); $maksSel = max(1, $semua->max()); @endphp
        <div class="tbl-wrap">
            <table class="tbl heat">
                <thead><tr><th>Bidang</th>@foreach ($pilihan['wilayah'] as $w)<th class="r">{{ $w }}</th>@endforeach<th class="r">Total</th></tr></thead>
                <tbody>
                @foreach ($pilihan['bidang'] as $b)
                    <tr>
                        <th scope="row">{{ $b }}</th>
                        @foreach ($pilihan['wilayah'] as $w)
                            @php
                                $n = $silang[$b][$w] ?? 0;
                                $step = $n ? min(count($ramp) - 1, (int) round($n / $maksSel * (count($ramp) - 1))) : 0;
                                $dipilih = $filter['bidang'] === $b && $filter['wilayah'] === $w;
                            @endphp
                            <td class="r">
                                <a href="{{ $dipilih ? $url(['bidang' => null, 'wilayah' => null]) : $url(['bidang' => $b, 'wilayah' => $w]) }}"
                                   @class(['sel', 'gelap' => $step >= 7, 'dipilih' => $dipilih]) style="background: {{ $ramp[$step] }}"
                                   data-tip="{{ $b }} di {{ $w }}|{{ $angka($n) }} lamaran|{{ $dipilih ? 'klik untuk melepas dice' : 'klik untuk dice' }}"
                                   aria-label="{{ $b }} di {{ $w }}: {{ $angka($n) }} lamaran{{ $dipilih ? ', sedang dipilih' : '' }}">{{ $angka($n) }}</a>
                            </td>
                        @endforeach
                        <td class="r"><b>{{ $angka(array_sum($silang[$b] ?? [])) }}</b></td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot><tr>
                    <th scope="row">Total</th>
                    @foreach ($pilihan['wilayah'] as $w)<td class="r">{{ $angka(collect($silang)->sum(fn ($r) => $r[$w] ?? 0)) }}</td>@endforeach
                    <td class="r">{{ $angka($semua->sum()) }}</td>
                </tr></tfoot>
            </table>
        </div>
        <div class="skala"><span>Sedikit</span><i style="background: linear-gradient(90deg, {{ implode(', ', $ramp) }})"></i><span>Banyak</span></div>
        @isset($catatan['silang'])<div class="note"><i class="bi bi-info-circle"></i><span>{{ $catatan['silang'] }}</span></div>@endisset
    </div>

    {{-- Verifikasi: donut status keputusan, lalu persen disetujui per jenis bukti --}}
    <div class="card" id="kartu-verifikasi">
        <div class="card-h">
            <div>
                <h2>Keputusan verifikasi</h2>
                <div class="sub">fakta_verifikasi, hanya kena filter kuartal</div>
            </div>
        </div>
        @if ($kpi['verifikasi'])
            @php
                $uk = 136; $tb = 16; $r = ($uk - $tb) / 2; $C = 2 * M_PI * $r; $celah = 5;
                $seg = [['v' => $kpi['setuju'], 'c' => 'var(--ok)'], ['v' => $kpi['ditolak'], 'c' => 'var(--bad)']];
                $off = 0;
            @endphp
            <div class="donut-wrap">
                <div class="donut" style="width: {{ $uk }}px; height: {{ $uk }}px" role="img" aria-label="{{ $kpi['setuju'] }} disetujui dan {{ $kpi['ditolak'] }} ditolak">
                    <svg width="{{ $uk }}" height="{{ $uk }}" viewBox="0 0 {{ $uk }} {{ $uk }}">
                        <circle cx="{{ $uk / 2 }}" cy="{{ $uk / 2 }}" r="{{ $r }}" fill="none" stroke="var(--track)" stroke-width="{{ $tb }}"/>
                        @foreach ($seg as $g)
                            @if ($g['v'])
                                <circle cx="{{ $uk / 2 }}" cy="{{ $uk / 2 }}" r="{{ $r }}" fill="none" stroke="{{ $g['c'] }}" stroke-width="{{ $tb }}" stroke-linecap="round"
                                        stroke-dasharray="{{ max(0, $g['v'] / $kpi['verifikasi'] * $C - $celah) }} {{ $C }}" stroke-dashoffset="{{ -$off }}"/>
                            @endif
                            @php $off += $g['v'] / $kpi['verifikasi'] * $C; @endphp
                        @endforeach
                    </svg>
                    <div class="mid"><div><span>Disetujui</span><b>{{ $angka($kpi['persen_setuju'], 1) }}%</b></div></div>
                </div>
                <div class="dleg">
                    <div style="--c: var(--ok)">Disetujui <b>{{ $angka($kpi['setuju']) }}</b></div>
                    <div style="--c: var(--bad)">Ditolak <b>{{ $angka($kpi['ditolak']) }}</b></div>
                    <div style="--c: var(--line-strong)">Total keputusan <b>{{ $angka($kpi['verifikasi']) }}</b></div>
                </div>
            </div>
            <hr class="pisah">
            <div class="sub" style="margin-bottom: 4px">Persen disetujui per jenis bukti</div>
            @foreach ($verifikasi as $v)
                @php $p = $v->n ? $v->setuju / $v->n * 100 : 0; @endphp
                <div class="hbar" tabindex="0" data-tip="{{ $v->nama }}|{{ $v->setuju }} dari {{ $v->n }} disetujui|{{ $angka($p, 1) }}%">
                    <span>{{ $v->nama }}</span>
                    <span class="meter"><i style="width: {{ $p }}%"></i></span>
                    <b>{{ $angka($p, 1) }}%</b>
                </div>
            @endforeach
            <details class="data">
                <summary>Lihat tabel</summary>
                <div class="tbl-wrap"><table class="tbl">
                    <thead><tr><th>Jenis bukti</th><th class="r">Verifikasi</th><th class="r">Disetujui</th><th class="r">%</th></tr></thead>
                    <tbody>@foreach ($verifikasi as $v)<tr><td>{{ $v->nama }}</td><td class="r">{{ $v->n }}</td><td class="r">{{ $v->setuju }}</td><td class="r">{{ $angka($v->n ? $v->setuju / $v->n * 100 : 0, 1) }}%</td></tr>@endforeach</tbody>
                </table></div>
            </details>
        @else
            <div class="kosong"><i class="bi bi-inbox"></i>Belum ada keputusan verifikasi di periode ini.</div>
        @endif
    </div>
</section>

<section class="grid g-2 rata-atas">
    <div class="card" id="kartu-instansi">
        <div class="card-h">
            <div>
                <h2>Rata-rata lama verifikasi per instansi</h2>
                <div class="sub">Jam dari berkas diunggah sampai diputuskan</div>
            </div>
        </div>
        @php $maksJam = max(1, collect($instansi)->max(fn ($i) => $i->n ? $i->jam / $i->n : 0)) * 1.1; @endphp
        @foreach ($instansi as $i)
            @php $j = $i->n ? $i->jam / $i->n : 0; @endphp
            <div class="hbar" tabindex="0" data-tip="{{ $i->instansi }}|{{ $angka($j, 1) }} jam rata-rata|{{ $i->n }} verifikasi">
                <span>{{ $i->instansi }}</span>
                <span class="meter">
                    <i style="width: {{ $j / $maksJam * 100 }}%"></i>
                    @if ($kpi['jam'])<span class="rata" style="left: {{ $kpi['jam'] / $maksJam * 100 }}%" aria-hidden="true"></span>@endif
                </span>
                <b>{{ $angka($j, 1) }} <small>jam</small></b>
            </div>
        @endforeach
        @isset($catatan['instansi'])<div class="note"><i class="bi bi-info-circle"></i><span>{{ $catatan['instansi'] }}</span></div>@endisset
        <details class="data">
            <summary>Lihat tabel</summary>
            <div class="tbl-wrap"><table class="tbl">
                <thead><tr><th>Instansi</th><th class="r">Verifikasi</th><th class="r">Total jam</th><th class="r">Rata-rata</th></tr></thead>
                <tbody>@foreach ($instansi as $i)<tr><td>{{ $i->instansi }}</td><td class="r">{{ $i->n }}</td><td class="r">{{ $angka($i->jam) }}</td><td class="r">{{ $angka($i->n ? $i->jam / $i->n : 0, 1) }}</td></tr>@endforeach</tbody>
            </table></div>
        </details>
    </div>

    <div class="card" id="kartu-keahlian">
        <div class="card-h">
            <div>
                <h2>Keahlian yang paling dicari</h2>
                <div class="sub">Syarat keahlian pada lowongan terbit, kena semua filter</div>
            </div>
            <span class="sp"></span>
            <div class="legend"><span style="--c: var(--primary)">Wajib terverifikasi</span><span style="--c: var(--c3)">Syarat lain</span></div>
        </div>
        @php $maksKeb = max(1, collect($keahlian)->max('n')); @endphp
        @forelse ($keahlian as $k)
            <div class="hbar lebar" tabindex="0" data-tip="{{ $k->nama }}|{{ $k->n }} syarat lowongan|{{ $k->wv }} wajib terverifikasi">
                <span>{{ $k->nama }}</span>
                <span class="stack" aria-hidden="true">
                    @if ($k->wv)<i style="width: {{ $k->wv / $maksKeb * 100 }}%; background: var(--primary)"></i>@endif
                    @if ($k->n - $k->wv)<i style="width: {{ ($k->n - $k->wv) / $maksKeb * 100 }}%; background: var(--c3)"></i>@endif
                </span>
                <b>{{ $k->n }}@if ($k->wv) <small>· {{ $k->wv }} terverifikasi</small>@endif</b>
            </div>
        @empty
            <div class="kosong"><i class="bi bi-bar-chart"></i>Tidak ada syarat keahlian untuk filter ini.</div>
        @endforelse
        <details class="data">
            <summary>Lihat tabel</summary>
            <div class="tbl-wrap"><table class="tbl">
                <thead><tr><th>Keahlian</th><th class="r">Syarat</th><th class="r">Wajib terverifikasi</th></tr></thead>
                <tbody>@foreach ($keahlian as $k)<tr><td>{{ $k->nama }}</td><td class="r">{{ $k->n }}</td><td class="r">{{ $k->wv }}</td></tr>@endforeach</tbody>
            </table></div>
        </details>
    </div>
</section>
@endsection
@endif
