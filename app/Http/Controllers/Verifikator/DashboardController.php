<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Bukti;

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

        $keputusan = $verifikator->verifikasi()
            ->selectRaw('keputusan, COUNT(*) AS jumlah')
            ->groupBy('keputusan')
            ->pluck('jumlah', 'keputusan');

        return view('verifikator.dashboard', compact('verifikator', 'antrean', 'keputusan'));
    }
}
