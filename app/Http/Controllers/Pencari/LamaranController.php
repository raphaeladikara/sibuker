<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Support\Kecocokan;

class LamaranController extends Controller
{
    use AksesProfil;

    // GET /pencari/lamaran -> baca: lamaran JOIN lowongan JOIN perusahaan
    public function index()
    {
        $lamaran = $this->pencari()->lamaran()
            ->with(['lowongan.perusahaan', 'tahapSeleksi'])
            ->latest('dilamar_pada')
            ->get();

        return view('pencari.lamaran.index', compact('lamaran'));
    }

    // GET /pencari/lamaran/{lamaran} -> baca: lamaran, tahap_seleksi (urut), syarat lowongan
    public function show(Lamaran $lamaran)
    {
        $this->pastikanMilik($lamaran);
        $lamaran->load(['lowongan.perusahaan', 'lowongan.syarat.keahlian', 'tahapSeleksi']);
        $cocok = Kecocokan::hitung($lamaran->lowongan, $this->pencari());

        return view('pencari.lamaran.show', compact('lamaran', 'cocok'));
    }

    // PATCH /pencari/lamaran/{lamaran}/tarik -> ubah: lamaran.status = 'ditarik'
    public function tarik(Lamaran $lamaran)
    {
        $this->pastikanMilik($lamaran);

        if ($lamaran->sudahFinal()) {
            return back()->with('error', 'Lamaran yang sudah ' . $lamaran->status . ' tidak bisa ditarik.');
        }

        $lamaran->update(['status' => 'ditarik']);

        return redirect()->route('pencari.lamaran.show', $lamaran)->with('success', 'Lamaran ditarik.');
    }

    private function pastikanMilik(Lamaran $lamaran): void
    {
        abort_unless($lamaran->pencari_kerja_id === $this->pencari()->id, 404);
    }
}
