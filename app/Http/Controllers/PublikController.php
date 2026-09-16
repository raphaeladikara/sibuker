<?php

namespace App\Http\Controllers;

use App\Models\Keahlian;
use App\Models\Lowongan;
use App\Models\Perusahaan;
use App\Support\Format;
use App\Support\Kecocokan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublikController extends Controller
{
    // GET /  -> baca: lowongan (dipublikasikan), perusahaan, keahlian, v_status_keahlian
    public function beranda(Request $request)
    {
        $lowongan = $this->lowonganTerbuka()
            ->with(['perusahaan', 'syarat.keahlian'])
            ->latest('dipublikasikan_pada')
            ->take(6)
            ->get();

        $statistik = [
            'lowongan' => $this->lowonganTerbuka()->count(),
            'perusahaan' => Perusahaan::where('status_perusahaan', 'aktif')->count(),
            'terverifikasi' => DB::table('v_status_keahlian')->where('terverifikasi', 1)->count(),
            'keahlian' => Keahlian::where('aktif', true)->count(),
        ];

        $keahlianPopuler = Keahlian::where('aktif', true)
            ->withCount('syarat')
            ->orderByDesc('syarat_count')
            ->take(6)
            ->get();

        return view('publik.beranda', compact('lowongan', 'statistik', 'keahlianPopuler'));
    }

    // GET /lowongan?q=&lokasi=&tipe[]=&verifikasi=&keahlian=
    // baca: lowongan JOIN perusahaan, syarat_keahlian JOIN keahlian
    public function lowongan(Request $request)
    {
        $query = $this->lowonganTerbuka()->with(['perusahaan', 'syarat.keahlian'])->withCount('lamaran');

        if ($kata = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('posisi', 'like', "%{$kata}%")
                ->orWhere('deskripsi', 'like', "%{$kata}%")
                ->orWhereHas('perusahaan', fn ($p) => $p->where('nama', 'like', "%{$kata}%")));
        }

        if ($lokasi = trim((string) $request->query('lokasi'))) {
            $query->where('lokasi', 'like', "%{$lokasi}%");
        }

        $tipe = array_values(array_intersect((array) $request->query('tipe', []), array_keys(Format::TIPE_PEKERJAAN)));
        if ($tipe) {
            $query->whereIn('tipe_pekerjaan', $tipe);
        }

        if ($request->filled('keahlian')) {
            $query->whereHas('syarat', fn ($s) => $s->where('keahlian_id', $request->integer('keahlian')));
        }

        // "Tanpa centang biru wajib": lowongan yang tidak punya syarat wajib_terverifikasi sama sekali.
        if ($request->query('verifikasi') === 'tidak_wajib') {
            $query->whereDoesntHave('syarat', fn ($s) => $s->where('wajib_terverifikasi', true));
        }

        $lowongan = $query->latest('dipublikasikan_pada')->paginate(9)->withQueryString();

        // Jumlah lowongan per tipe untuk angka di sebelah checkbox filter.
        $jumlahTipe = $this->lowonganTerbuka()
            ->select('tipe_pekerjaan', DB::raw('COUNT(*) AS jumlah'))
            ->groupBy('tipe_pekerjaan')
            ->pluck('jumlah', 'tipe_pekerjaan');

        $keahlian = Keahlian::where('aktif', true)->orderBy('nama')->get();

        // Pencari kerja yang sedang login langsung melihat skor kecocokannya.
        $cocok = [];
        $pencari = $request->user()?->pencariKerja;
        if ($pencari) {
            $klaim = $pencari->klaimKeahlian()->denganStatus()->get();
            foreach ($lowongan as $l) {
                $cocok[$l->id] = Kecocokan::hitung($l, $pencari, $klaim);
            }
        }

        return view('publik.lowongan', compact('lowongan', 'jumlahTipe', 'keahlian', 'tipe', 'cocok'));
    }

    // GET /lowongan/{lowongan}  -> baca: lowongan, perusahaan, syarat_keahlian JOIN keahlian
    public function detailLowongan(Request $request, Lowongan $lowongan)
    {
        $pengguna = $request->user();
        $milikSendiri = $pengguna?->perusahaan?->id === $lowongan->perusahaan_id;

        abort_unless($lowongan->status === 'dipublikasikan' || $milikSendiri || $pengguna?->is_admin, 404);

        $lowongan->load(['perusahaan', 'syarat.keahlian'])->loadCount('lamaran');

        $lainnya = $this->lowonganTerbuka()
            ->with('perusahaan')
            ->whereKeyNot($lowongan->id)
            ->latest('dipublikasikan_pada')
            ->take(4)
            ->get();

        $cocok = null;
        $lamaran = null;
        if ($pencari = $pengguna?->pencariKerja) {
            $cocok = Kecocokan::hitung($lowongan, $pencari);
            $lamaran = $lowongan->lamaran()->where('pencari_kerja_id', $pencari->id)->first();
        }

        return view('publik.lowongan-detail', compact('lowongan', 'lainnya', 'cocok', 'lamaran'));
    }

    /** Lowongan dipublikasikan milik perusahaan yang statusnya aktif. */
    private function lowonganTerbuka()
    {
        return Lowongan::dipublikasikan()->whereHas('perusahaan', fn ($p) => $p->where('status_perusahaan', 'aktif'));
    }
}
