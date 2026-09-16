<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Bukti;
use App\Models\Lowongan;
use App\Support\Kecocokan;

class DashboardController extends Controller
{
    use AksesProfil;

    // GET /pencari/dashboard
    // baca: pencari_kerja, klaim_keahlian + v_status_keahlian, bukti + verifikasi terbaru, lamaran
    public function index()
    {
        $profil = $this->pencari()->load('pengguna');
        $klaim = $profil->klaimKeahlian()->denganStatus()->with('keahlian')->orderByDesc('terverifikasi')->get();
        $bukti = Bukti::denganStatus()->where('pengguna_id', $profil->pengguna_id)->latest('diunggah_pada')->get();
        $lamaran = $profil->lamaran()->with('lowongan.perusahaan')->latest('dilamar_pada')->get();

        $sudahDilamar = $lamaran->pluck('lowongan_id')->all();

        $rekomendasi = Lowongan::dipublikasikan()
            ->whereHas('perusahaan', fn ($p) => $p->where('status_perusahaan', 'aktif'))
            ->whereNotIn('id', $sudahDilamar)
            ->with(['perusahaan', 'syarat.keahlian'])
            ->get()
            ->map(fn ($l) => (object) (['lowongan' => $l] + Kecocokan::hitung($l, $profil, $klaim)))
            ->sortByDesc('skor')
            ->take(4)
            ->values();

        return view('pencari.dashboard', compact('profil', 'klaim', 'bukti', 'lamaran', 'rekomendasi'));
    }
}
