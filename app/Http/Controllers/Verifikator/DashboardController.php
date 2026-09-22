<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Bukti;
use App\Models\Pengguna;

class DashboardController extends Controller
{
    use AksesProfil;

    // GET /verifikator/dashboard
    // baca: bukti yang belum diputuskan, milik orang lain, dan pemiliknya punya klaim_keahlian
    //       aktif pada keahlian yang ada di kewenangan_verifikator
    public function index()
    {
        $verifikator = $this->verifikator()->load('kewenangan', 'pengguna');
        $wewenang = $verifikator->kewenangan->pluck('id');

        $antrean = Bukti::denganStatus()
            ->dalamAntrean()
            ->where('pengguna_id', '!=', $verifikator->pengguna_id)
            ->whereHas('pengguna.pencariKerja.klaimKeahlian', fn ($q) => $q->where('aktif', true)->whereIn('keahlian_id', $wewenang))
            ->with(['jenisBukti', 'pengguna.pencariKerja.klaimKeahlian' => fn ($q) => $q->where('aktif', true)->whereIn('keahlian_id', $wewenang)->with('keahlian')])
            ->orderBy('diunggah_pada')
            ->get();

        $antreanPemilik = $antrean
            ->groupBy('pengguna_id')
            ->map(function ($sertifikat) {
                $pertama = $sertifikat->first();

                return (object) [
                    'pengguna' => $pertama->pengguna,
                    'pencari' => $pertama->pengguna->pencariKerja,
                    'klaim' => $pertama->pengguna->pencariKerja->klaimKeahlian,
                    'jumlah_menunggu' => $sertifikat->count(),
                    'terlama' => $sertifikat->min('diunggah_pada'),
                ];
            })
            ->values();

        $keputusan = $verifikator->verifikasi()
            ->selectRaw('keputusan, COUNT(*) AS jumlah')
            ->groupBy('keputusan')
            ->pluck('jumlah', 'keputusan');

        return view('verifikator.dashboard', compact('verifikator', 'antrean', 'antreanPemilik', 'keputusan'));
    }

    // GET /verifikator/pemilik/{pengguna}
    // baca: semua sertifikat pemilik; akses hanya jika pemilik punya klaim aktif dalam kewenangan verifikator
    public function pemilik(Pengguna $pengguna)
    {
        $verifikator = $this->verifikator()->load('kewenangan', 'pengguna');
        $pencari = $pengguna->pencariKerja;
        $wewenang = $verifikator->kewenangan->pluck('id');

        abort_if($pengguna->is($verifikator->pengguna), 403, 'Kamu tidak bisa memeriksa sertifikat milik sendiri.');
        abort_unless($pencari && $pencari->klaimKeahlian()->where('aktif', true)->whereIn('keahlian_id', $wewenang)->exists(), 403, 'Pemilik ini di luar bidang kewenanganmu.');

        $klaim = $pencari->klaimKeahlian()
            ->denganStatus()
            ->with('keahlian')
            ->where('aktif', true)
            ->get();

        $sertifikat = Bukti::denganStatus()
            ->where('pengguna_id', $pengguna->id)
            ->with(['jenisBukti', 'verifikasi.verifikator.pengguna'])
            ->orderByDesc('diunggah_pada')
            ->get()
            ->sortBy(fn ($bukti) => [$bukti->perluDiperiksa() ? 0 : 1, -$bukti->diunggah_pada->timestamp])
            ->values();

        $jumlahMenunggu = $sertifikat->filter->perluDiperiksa()->count();

        return view('verifikator.pemilik', compact('verifikator', 'pengguna', 'pencari', 'klaim', 'sertifikat', 'jumlahMenunggu'));
    }
}
