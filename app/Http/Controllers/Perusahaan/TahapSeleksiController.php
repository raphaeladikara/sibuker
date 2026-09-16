<?php

namespace App\Http\Controllers\Perusahaan;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\TahapSeleksi;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TahapSeleksiController extends Controller
{
    use AksesProfil;

    // POST /perusahaan/pelamar/{lamaran}/tahap -> tambah: tahap_seleksi (UNIQUE lamaran_id + urutan)
    public function store(Request $request, Lamaran $lamaran)
    {
        $this->pastikanMilik($lamaran);

        $data = $request->validate([
            'urutan' => ['required', 'integer', 'min:1', 'max:65535', Rule::unique('tahap_seleksi')->where('lamaran_id', $lamaran->id)],
            'nama_tahap' => ['required', 'string', 'max:120'],
            'jadwal' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], ['urutan.unique' => 'Urutan ini sudah dipakai tahap lain.'], ['nama_tahap' => 'nama tahap']);

        $lamaran->tahapSeleksi()->create($data + ['status' => 'menunggu']);

        return redirect()->route('perusahaan.pelamar.show', $lamaran)->with('success', 'Tahap ' . $data['nama_tahap'] . ' ditambahkan.');
    }

    // PUT /perusahaan/tahap/{tahap} -> ubah: tahap_seleksi (status, jadwal, catatan)
    public function update(Request $request, TahapSeleksi $tahap)
    {
        $this->pastikanMilik($tahap->lamaran);

        $data = $request->validate([
            'status' => ['required', Rule::in(Format::STATUS_TAHAP)],
            'jadwal' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $tahap->update($data);

        return redirect()->route('perusahaan.pelamar.show', $tahap->lamaran_id)->with('success', 'Tahap ' . $tahap->nama_tahap . ' diperbarui.');
    }

    // DELETE /perusahaan/tahap/{tahap} -> hapus: tahap_seleksi
    public function destroy(TahapSeleksi $tahap)
    {
        $this->pastikanMilik($tahap->lamaran);
        $tahap->delete();

        return redirect()->route('perusahaan.pelamar.show', $tahap->lamaran_id)->with('success', 'Tahap dihapus.');
    }

    private function pastikanMilik(Lamaran $lamaran): void
    {
        abort_unless($lamaran->lowongan->perusahaan_id === $this->perusahaan()->id, 404);
    }
}
