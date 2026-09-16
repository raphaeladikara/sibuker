<?php

namespace App\Http\Controllers\Perusahaan;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Bukti;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Support\Format;
use App\Support\Kecocokan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelamarController extends Controller
{
    use AksesProfil;

    // GET /perusahaan/lowongan/{lowongan}/pelamar -> baca: lamaran JOIN pencari_kerja JOIN pengguna, urut skor_kecocokan DESC
    public function index(Lowongan $lowongan)
    {
        abort_unless($lowongan->perusahaan_id === $this->perusahaan()->id, 404);
        $lowongan->load('syarat.keahlian');

        $pelamar = $lowongan->lamaran()
            ->with(['pencariKerja.pengguna', 'pencariKerja.klaimKeahlian' => fn ($q) => $q->denganStatus()->with('keahlian')])
            ->orderByDesc('skor_kecocokan')
            ->orderBy('dilamar_pada')
            ->get()
            ->map(fn ($l) => (object) (['lamaran' => $l] + Kecocokan::hitung($lowongan, $l->pencariKerja, $l->pencariKerja->klaimKeahlian)));

        return view('perusahaan.pelamar.index', compact('lowongan', 'pelamar'));
    }

    // GET /perusahaan/pelamar/{lamaran}
    // baca: lamaran, pencari_kerja, klaim_keahlian + v_status_keahlian, bukti yang verifikasi terbarunya disetujui, tahap_seleksi
    public function show(Lamaran $lamaran)
    {
        $this->pastikanMilik($lamaran);

        // Membuka lamaran yang baru masuk otomatis menandainya sebagai ditinjau.
        if ($lamaran->status === 'dikirim') {
            $lamaran->update(['status' => 'ditinjau']);
        }

        $lamaran->load(['lowongan.syarat.keahlian', 'pencariKerja.pengguna', 'tahapSeleksi']);
        $profil = $lamaran->pencariKerja;
        $klaim = $profil->klaimKeahlian()->denganStatus()->with('keahlian')->where('aktif', true)->orderByDesc('terverifikasi')->get();
        $sertifikat = Bukti::denganStatus()
            ->with('jenisBukti')
            ->where('pengguna_id', $profil->pengguna_id)
            ->get()
            ->where('status_terakhir', 'disetujui')
            ->values();
        $cocok = Kecocokan::hitung($lamaran->lowongan, $profil, $klaim);

        return view('perusahaan.pelamar.show', compact('lamaran', 'profil', 'klaim', 'sertifikat', 'cocok'));
    }

    // PATCH /perusahaan/pelamar/{lamaran}/status -> ubah: lamaran.status
    public function updateStatus(Request $request, Lamaran $lamaran)
    {
        $this->pastikanMilik($lamaran);

        if ($lamaran->status === 'ditarik') {
            return back()->with('error', 'Pelamar sudah menarik lamaran ini.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(array_diff(Format::STATUS_LAMARAN, ['ditarik']))],
        ]);

        $lamaran->update($data);

        return redirect()->route('perusahaan.pelamar.show', $lamaran)->with('success', 'Status lamaran jadi ' . Format::label($data['status']) . '.');
    }

    private function pastikanMilik(Lamaran $lamaran): void
    {
        abort_unless($lamaran->lowongan->perusahaan_id === $this->perusahaan()->id, 404);
    }
}
