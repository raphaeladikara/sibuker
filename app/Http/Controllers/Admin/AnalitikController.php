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

        return view('admin.analitik', [
            'pilihan' => $pilihan,
            'filter' => $filter,
            'tingkat' => $tingkat,
            'sebelum' => $sebelum,
            'kpi' => $kpi,
            'kpiSebelum' => $kpiSebelum,
            'tren' => $this->tren($filter, $tingkat),
            'silang' => $this->silang($filter),
            'verifikasi' => $this->verifikasi($filter),
            'instansi' => $this->instansi($filter),
            'keahlian' => $this->keahlian($filter),
        ]);
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
            ->selectRaw('SUM(f.jumlah_verifikasi) AS n, SUM(f.disetujui) AS setuju, SUM(f.durasi_jam) AS jam')->first();
        $keb = $this->fakta('fakta_kebutuhan_keahlian', $filter)
            ->selectRaw('SUM(f.jumlah_kebutuhan) AS n, SUM(f.wajib_terverifikasi) AS wv')->first();

        return [
            'lamaran' => (int) $lam->n,
            'diterima' => (int) $lam->diterima,
            'persen_diterima' => $lam->n ? $lam->diterima / $lam->n * 100 : null,
            'skor' => $lam->skor !== null ? (float) $lam->skor : null,
            'verifikasi' => (int) $ver->n,
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
