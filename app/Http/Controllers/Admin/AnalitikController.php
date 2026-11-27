<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * GET /admin/analitik -> baca sibuker_dw (koneksi "dw"), bukan database aplikasi.
 *
 * Operasi OLAP lewat query string:
 *  - slice  : kuartal=Q2            (memotong semua fakta pada satu kuartal)
 *  - dice   : wilayah=..&bidang=..  (memotong lamaran dan kebutuhan pada beberapa dimensi)
 *  - drill  : tingkat=tahun|kuartal|bulan (hierarki dim_waktu, dipakai grafik tren)
 */
class AnalitikController extends Controller
{
    private const TINGKAT = ['tahun', 'kuartal', 'bulan'];

    public function index(Request $request)
    {
        try {
            $dw = DB::connection('dw');
            $dw->getPdo();
        } catch (Throwable $e) {
            return view('admin.analitik', ['gagal' => $e->getMessage()]);
        }

        $pilihan = [
            'kuartal' => $dw->table('fakta_lamaran as f')->join('dim_waktu as w', 'w.waktu_key', '=', 'f.waktu_key')
                ->distinct()->orderBy('w.kuartal')->pluck('w.kuartal')->all(),
            'wilayah' => $dw->table('dim_lokasi')->distinct()->orderBy('wilayah')->pluck('wilayah')->all(),
            'bidang' => $dw->table('dim_lowongan')->distinct()->orderBy('bidang')->pluck('bidang')->all(),
        ];

        // Nilai filter hanya diterima kalau memang ada di dimensi, supaya query string asal tidak ikut ke SQL.
        $filter = [
            'kuartal' => in_array($request->query('kuartal'), $pilihan['kuartal'], true) ? $request->query('kuartal') : null,
            'wilayah' => in_array($request->query('wilayah'), $pilihan['wilayah'], true) ? $request->query('wilayah') : null,
            'bidang' => in_array($request->query('bidang'), $pilihan['bidang'], true) ? $request->query('bidang') : null,
        ];
        $tingkat = in_array($request->query('tingkat'), self::TINGKAT, true) ? $request->query('tingkat') : 'kuartal';

        // Pembanding KPI: kuartal sebelumnya kalau sedang slice, selain itu tidak ada.
        $idx = $filter['kuartal'] ? array_search($filter['kuartal'], $pilihan['kuartal'], true) : false;
        $sebelum = $idx ? $pilihan['kuartal'][$idx - 1] : null;

        $kpi = $this->kpi($filter);
        $kpiSebelum = $sebelum ? $this->kpi(['kuartal' => $sebelum] + $filter) : null;

        $data = [
            'pilihan' => $pilihan,
            'filter' => $filter,
            'tingkat' => $tingkat,
            'sebelum' => $sebelum,
            'kpi' => $kpi,
            'kpiSebelum' => $kpiSebelum,
            'tren' => $this->tren($filter, $tingkat),
            'bulanan' => $this->tren($filter, 'bulan'),
            'silang' => $this->silang($filter),
            'verifikasi' => $this->verifikasi($filter),
            'instansi' => $this->instansi($filter),
            'keahlian' => $this->keahlian($filter),
        ];
        $data['perlu'] = $this->perluDitindak($data);
        $data['catatan'] = $this->catatan($data);

        return view('admin.analitik', $data);
    }

    private static function angka(float|int $n, int $d = 0): string
    {
        return number_format($n, $d, ',', '.');
    }

    /** Dua kuartal yang dibandingkan: kuartal terpilih dan sebelumnya, atau dua kuartal terakhir. */
    private function pasanganKuartal(array $data): ?array
    {
        if ($data['filter']['kuartal']) {
            return $data['sebelum'] ? [$data['sebelum'], $data['filter']['kuartal']] : null;
        }
        $k = $data['pilihan']['kuartal'];

        return count($k) >= 2 ? array_slice($k, -2) : null;
    }

    /**
     * Daftar "Perlu ditindak": temuan yang dihitung ulang dari warehouse setiap kali halaman dibuka,
     * jadi ikut berubah kalau filter diganti. Setiap temuan membawa tautan ke grafik atau filter terkait.
     */
    private function perluDitindak(array $data): array
    {
        $kpi = $data['kpi'];
        $f = $data['filter'];
        $perlu = [];

        $ver = collect($data['verifikasi'])->filter(fn ($v) => $v->n > 0);
        if ($ver->count() > 1 && $kpi['persen_setuju'] !== null) {
            $v = $ver->sortBy(fn ($v) => $v->setuju / $v->n)->first();
            $p = $v->setuju / $v->n * 100;
            $perlu[] = [
                'ikon' => 'bi-file-earmark-x', 'nada' => $p < $kpi['persen_setuju'] - 10 ? 'bad' : 'warn',
                'judul' => "{$v->nama} paling sering ditolak",
                'ket' => "{$v->setuju} dari {$v->n} disetujui, " . self::angka($kpi['persen_setuju'] - $p, 1) . ' poin di bawah rata-rata',
                'pill' => self::angka($p, 1) . '%', 'url' => '#kartu-verifikasi',
            ];
        }

        $ins = collect($data['instansi'])->filter(fn ($i) => $i->n > 0);
        if ($ins->count() > 1 && $kpi['jam'] !== null) {
            $i = $ins->sortByDesc(fn ($i) => $i->jam / $i->n)->first();
            $j = $i->jam / $i->n;
            $perlu[] = [
                'ikon' => 'bi-hourglass-split', 'nada' => 'warn',
                'judul' => "{$i->instansi} paling lama memeriksa",
                'ket' => 'rata-rata ' . self::angka($j, 1) . ' jam, ' . self::angka($j - $kpi['jam'], 1) . ' jam di atas rata-rata semua instansi',
                'pill' => self::angka($j, 1) . ' jam', 'url' => '#kartu-instansi',
            ];
        }

        if ($pasang = $this->pasanganKuartal($data)) {
            [$lama, $baru] = $pasang;
            $jam = fn ($k) => $this->kpi(['kuartal' => $k] + $f)['jam'];
            [$a, $b] = [$jam($lama), $jam($baru)];
            if ($a !== null && $b !== null && $b > $a) {
                $perlu[] = [
                    'ikon' => 'bi-clock-history', 'nada' => 'warn',
                    'judul' => 'Verifikasi makin lambat',
                    'ket' => 'rata-rata ' . self::angka($a, 1) . " jam di $lama menjadi " . self::angka($b, 1) . " jam di $baru",
                    'pill' => '+' . self::angka($b - $a, 1) . ' jam', 'url' => ['kuartal' => $baru],
                ];
            }

            // Sel bidang x wilayah dengan kenaikan lamaran terbesar di antara dua kuartal itu.
            $selLama = $this->silang(['kuartal' => $lama] + $f);
            $selBaru = $this->silang(['kuartal' => $baru] + $f);
            $terbaik = null;
            foreach ($selBaru as $bidang => $baris) {
                foreach ($baris as $wilayah => $n) {
                    $d = $n - ($selLama[$bidang][$wilayah] ?? 0);
                    if (! $terbaik || $d > $terbaik[2]) {
                        $terbaik = [$bidang, $wilayah, $d, $selLama[$bidang][$wilayah] ?? 0, $n];
                    }
                }
            }
            if ($terbaik && $terbaik[2] > 0) {
                [$bd, $wl, $d, $x, $y] = $terbaik;
                $perlu[] = [
                    'ikon' => 'bi-graph-up-arrow', 'nada' => 'info',
                    'judul' => "$bd di $wl naik paling cepat",
                    'ket' => "$x menjadi $y lamaran dari $lama ke $baru",
                    'pill' => '+' . self::angka($d), 'url' => ['kuartal' => $baru, 'wilayah' => $wl, 'bidang' => $bd],
                ];
            }
        }

        if ($k = $data['keahlian'][0] ?? null) {
            // Kalau beberapa keahlian seri di posisi teratas, semuanya disebut.
            $teratas = collect($data['keahlian'])->where('n', $k->n);
            $nama = $teratas->pluck('nama');
            $judul = $nama->count() > 1 ? $nama->slice(0, -1)->implode(', ') . ' dan ' . $nama->last() : $k->nama;
            $wv = $teratas->sum('wv');
            $perlu[] = [
                'ikon' => 'bi-patch-check', 'nada' => 'info',
                'judul' => "$judul paling banyak diminta",
                'ket' => ($nama->count() > 1 ? "masing-masing {$k->n} syarat lowongan, " : "{$k->n} syarat lowongan, ")
                    . ($wv ? "$wv di antaranya wajib terverifikasi" : 'belum ada yang wajib terverifikasi'),
                'pill' => "{$k->n} syarat", 'url' => '#kartu-keahlian',
            ];
        }

        return $perlu;
    }

    /** Kalimat pembacaan di bawah grafik, dihitung dari angka yang sama dengan grafiknya. */
    private function catatan(array $data): array
    {
        $c = [];
        $t = $data['tren'];
        if (count($t) >= 2) {
            [$a, $b] = array_slice($t, -2);
            $naik = $a->n ? ($b->n - $a->n) / $a->n * 100 : null;
            $pa = $a->n ? $a->diterima / $a->n * 100 : 0;
            $pb = $b->n ? $b->diterima / $b->n * 100 : 0;
            $c['tren'] = "Lamaran {$b->label} " . ($naik === null ? 'mulai masuk' : ($naik >= 0 ? 'naik ' : 'turun ') . self::angka(abs($naik), 1) . '%')
                . " dari {$a->label} (" . self::angka($a->n) . ' menjadi ' . self::angka($b->n) . '), sedangkan persen diterima '
                . ($pb >= $pa ? 'naik' : 'turun') . ' dari ' . self::angka($pa, 1) . '% ke ' . self::angka($pb, 1) . '%.';
        } elseif (count($t) === 1) {
            $c['tren'] = 'Hanya ada satu periode di tingkat ini. Klik batangnya untuk drill-down.';
        }

        $maks = null;
        foreach ($data['silang'] as $bidang => $baris) {
            foreach ($baris as $wilayah => $n) {
                if (! $maks || $n > $maks[2]) {
                    $maks = [$bidang, $wilayah, $n];
                }
            }
        }
        if ($maks) {
            $total = collect($data['silang'])->flatten()->sum();
            $c['silang'] = "Sel tergelap adalah {$maks[0]} di {$maks[1]} dengan " . self::angka($maks[2]) . ' lamaran, '
                . self::angka($total ? $maks[2] / $total * 100 : 0, 1) . '% dari semua lamaran di periode ini.';
        }
        if ($data['kpi']['jam'] !== null) {
            $c['instansi'] = 'Garis putus-putus adalah rata-rata semua instansi, ' . self::angka($data['kpi']['jam'], 1) . ' jam.';
        }

        return $c;
    }

    /** Lamaran atau kebutuhan dengan join dimensi bersama dan filter slice/dice. */
    private function fakta(string $tabel, array $filter, bool $pakaiDice = true): Builder
    {
        $q = DB::connection('dw')->table("$tabel as f")
            ->join('dim_waktu as w', 'w.waktu_key', '=', 'f.waktu_key')
            ->join('dim_lokasi as k', 'k.lokasi_key', '=', 'f.lokasi_key')
            ->join('dim_lowongan as l', 'l.lowongan_key', '=', 'f.lowongan_key')
            ->when($filter['kuartal'], fn ($q, $v) => $q->where('w.kuartal', $v));

        if ($pakaiDice) {
            $q->when($filter['wilayah'], fn ($q, $v) => $q->where('k.wilayah', $v))
                ->when($filter['bidang'], fn ($q, $v) => $q->where('l.bidang', $v));
        }

        return $q;
    }

    private function verifikasiQuery(array $filter): Builder
    {
        // fakta_verifikasi tidak punya dimensi lokasi dan lowongan, jadi hanya kena slice kuartal.
        return DB::connection('dw')->table('fakta_verifikasi as f')
            ->join('dim_waktu as w', 'w.waktu_key', '=', 'f.waktu_key')
            ->when($filter['kuartal'], fn ($q, $v) => $q->where('w.kuartal', $v));
    }

    private function kpi(array $filter): array
    {
        $lam = $this->fakta('fakta_lamaran', $filter)
            ->selectRaw('SUM(f.jumlah_lamaran) AS n, SUM(f.diterima) AS diterima, AVG(f.skor_kecocokan) AS skor')->first();
        $ver = $this->verifikasiQuery($filter)
            ->selectRaw('SUM(f.jumlah_verifikasi) AS n, SUM(f.disetujui) AS setuju, SUM(f.ditolak) AS ditolak, SUM(f.durasi_jam) AS jam')->first();
        $keb = $this->fakta('fakta_kebutuhan_keahlian', $filter)
            ->selectRaw('SUM(f.jumlah_kebutuhan) AS n, SUM(f.wajib_terverifikasi) AS wv')->first();

        return [
            'lamaran' => (int) $lam->n,
            'diterima' => (int) $lam->diterima,
            'persen_diterima' => $lam->n ? $lam->diterima / $lam->n * 100 : null,
            'skor' => $lam->skor !== null ? (float) $lam->skor : null,
            'verifikasi' => (int) $ver->n,
            'setuju' => (int) $ver->setuju,
            'ditolak' => (int) $ver->ditolak,
            'persen_setuju' => $ver->n ? $ver->setuju / $ver->n * 100 : null,
            // rata-rata jam dihitung dari total jam dibagi jumlah, sama seperti laporan
            'jam' => $ver->n ? $ver->jam / $ver->n : null,
            'kebutuhan' => (int) $keb->n,
            'persen_wajib_terverifikasi' => $keb->n ? $keb->wv / $keb->n * 100 : null,
        ];
    }

    private function tren(array $filter, string $tingkat): array
    {
        $q = $this->fakta('fakta_lamaran', $filter);
        match ($tingkat) {
            'tahun' => $q->selectRaw('w.tahun AS kunci, w.tahun AS label')->groupBy('w.tahun'),
            'kuartal' => $q->selectRaw('w.kuartal AS kunci, w.kuartal AS label')->groupBy('w.kuartal'),
            'bulan' => $q->selectRaw('w.bulan AS kunci, w.nama_bulan AS label, w.kuartal AS kuartal')
                ->groupBy('w.bulan', 'w.nama_bulan', 'w.kuartal'),
        };

        return $q->selectRaw('SUM(f.jumlah_lamaran) AS n, SUM(f.diterima) AS diterima')
            ->orderBy('kunci')->get()->all();
    }

    private function silang(array $filter): array
    {
        // Heatmap bidang x wilayah: hanya kena slice kuartal, sel yang sedang di-dice ditandai di view.
        $baris = $this->fakta('fakta_lamaran', $filter, false)
            ->selectRaw('l.bidang, k.wilayah, SUM(f.jumlah_lamaran) AS n')
            ->groupBy('l.bidang', 'k.wilayah')->get();

        $sel = [];
        foreach ($baris as $b) {
            $sel[$b->bidang][$b->wilayah] = (int) $b->n;
        }

        return $sel;
    }

    private function verifikasi(array $filter): array
    {
        return $this->verifikasiQuery($filter)
            ->join('dim_jenis_bukti as j', 'j.jenis_bukti_key', '=', 'f.jenis_bukti_key')
            ->selectRaw('j.nama, SUM(f.jumlah_verifikasi) AS n, SUM(f.disetujui) AS setuju')
            ->groupBy('j.nama')->orderBy('j.nama')->get()->all();
    }

    private function instansi(array $filter): array
    {
        return $this->verifikasiQuery($filter)
            ->join('dim_verifikator as v', 'v.verifikator_key', '=', 'f.verifikator_key')
            ->selectRaw('v.instansi, SUM(f.jumlah_verifikasi) AS n, SUM(f.durasi_jam) AS jam')
            ->groupBy('v.instansi')->orderBy('v.instansi')->get()->all();
    }

    private function keahlian(array $filter): array
    {
        return $this->fakta('fakta_kebutuhan_keahlian', $filter)
            ->join('dim_keahlian as ke', 'ke.keahlian_key', '=', 'f.keahlian_key')
            ->selectRaw('ke.nama, SUM(f.jumlah_kebutuhan) AS n, SUM(f.wajib_terverifikasi) AS wv')
            ->groupBy('ke.nama')->orderByDesc('n')->orderBy('ke.nama')->get()->all();
    }
}
