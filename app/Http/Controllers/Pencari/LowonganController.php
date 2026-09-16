<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Support\Kecocokan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class LowonganController extends Controller
{
    use AksesProfil;

    // GET /pencari/lowongan?q=&hanya_cocok=1
    // baca: lowongan, syarat_keahlian, klaim_keahlian + v_status_keahlian milik pencari -> skor kecocokan
    public function index(Request $request)
    {
        $pencari = $this->pencari();
        $klaim = $pencari->klaimKeahlian()->denganStatus()->get();
        $sudahDilamar = $pencari->lamaran()->pluck('lowongan_id')->all();

        $query = Lowongan::dipublikasikan()
            ->whereHas('perusahaan', fn ($p) => $p->where('status_perusahaan', 'aktif'))
            ->with(['perusahaan', 'syarat.keahlian']);

        if ($kata = trim((string) $request->query('q'))) {
            $query->where(fn ($q) => $q->where('posisi', 'like', "%{$kata}%")
                ->orWhereHas('perusahaan', fn ($p) => $p->where('nama', 'like', "%{$kata}%")));
        }

        $hasil = $query->get()
            ->map(fn ($l) => (object) (['lowongan' => $l, 'sudah_dilamar' => in_array($l->id, $sudahDilamar)] + Kecocokan::hitung($l, $pencari, $klaim)))
            ->when($request->boolean('hanya_cocok'), fn ($c) => $c->where('memenuhi_wajib', true))
            ->sortByDesc('skor')
            ->values();

        return view('pencari.lowongan', compact('hasil'));
    }

    // POST /pencari/lowongan/{lowongan}/lamar -> tambah: lamaran (skor_kecocokan disimpan saat itu)
    public function lamar(Lowongan $lowongan)
    {
        abort_unless($lowongan->status === 'dipublikasikan', 404);

        $pencari = $this->pencari();
        $skor = Kecocokan::hitung($lowongan, $pencari)['skor'];

        try {
            $lamaran = $pencari->lamaran()->create([
                'lowongan_id' => $lowongan->id,
                'status' => 'dikirim',
                'skor_kecocokan' => $skor,
            ]);
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', 'Kamu sudah pernah melamar lowongan ini.');
        }

        return redirect()->route('pencari.lamaran.show', $lamaran)->with('success', 'Lamaran terkirim ke ' . $lowongan->perusahaan->nama . '.');
    }
}
