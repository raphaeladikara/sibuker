<?php

namespace App\Http\Controllers\Perusahaan;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Lamaran;

class DashboardController extends Controller
{
    use AksesProfil;

    // GET /perusahaan/dashboard -> baca: perusahaan, lowongan (per status), lamaran terbaru
    public function index()
    {
        $perusahaan = $this->perusahaan();
        $lowongan = $perusahaan->lowongan()->withCount(['lamaran', 'syarat'])->latest('dibuat_pada')->get();

        $lamaran = Lamaran::whereIn('lowongan_id', $lowongan->pluck('id'))
            ->with(['lowongan', 'pencariKerja.pengguna'])
            ->latest('dilamar_pada')
            ->take(8)
            ->get();

        $ringkasan = [
            'dipublikasikan' => $lowongan->where('status', 'dipublikasikan')->count(),
            'draft' => $lowongan->where('status', 'draft')->count(),
            'pelamar' => $lowongan->sum('lamaran_count'),
            'perlu_ditinjau' => Lamaran::whereIn('lowongan_id', $lowongan->pluck('id'))->where('status', 'dikirim')->count(),
        ];

        return view('perusahaan.dashboard', compact('perusahaan', 'lowongan', 'lamaran', 'ringkasan'));
    }
}
