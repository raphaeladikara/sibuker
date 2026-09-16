<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bukti;
use App\Models\Keahlian;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Pengguna;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // GET /admin/dashboard -> baca: COUNT dari pengguna, bukti, lowongan, lamaran;
    // kebutuhan vs ketersediaan: syarat_keahlian vs v_status_keahlian per keahlian
    public function index()
    {
        $ringkasan = [
            'pengguna' => Pengguna::count(),
            'antrean' => Bukti::dalamAntrean()->count(),
            'lowongan' => Lowongan::dipublikasikan()->count(),
            'lamaran' => Lamaran::count(),
        ];

        $terverifikasi = DB::table('v_status_keahlian')
            ->where('terverifikasi', 1)
            ->selectRaw('keahlian_id, COUNT(*) AS jumlah')
            ->groupBy('keahlian_id')
            ->pluck('jumlah', 'keahlian_id');

        $peta = Keahlian::withCount(['syarat', 'klaim' => fn ($q) => $q->where('aktif', true)])
            ->orderBy('nama')
            ->get()
            ->map(fn ($k) => (object) [
                'nama' => $k->nama,
                'aktif' => $k->aktif,
                'diminta' => $k->syarat_count,
                'diklaim' => $k->klaim_count,
                'terverifikasi' => (int) ($terverifikasi[$k->id] ?? 0),
            ]);

        $penggunaBaru = Pengguna::with(['pencariKerja', 'perusahaan', 'verifikator'])->latest('dibuat_pada')->take(6)->get();

        return view('admin.dashboard', compact('ringkasan', 'peta', 'penggunaBaru'));
    }
}
