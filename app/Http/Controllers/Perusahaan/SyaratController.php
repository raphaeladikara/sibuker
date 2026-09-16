<?php

namespace App\Http\Controllers\Perusahaan;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Models\SyaratKeahlian;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Syarat keahlian dikelola dari halaman detail lowongan. */
class SyaratController extends Controller
{
    use AksesProfil;

    // POST /perusahaan/lowongan/{lowongan}/syarat -> tambah: syarat_keahlian (UNIQUE lowongan_id + keahlian_id)
    public function store(Request $request, Lowongan $lowongan)
    {
        abort_unless($lowongan->perusahaan_id === $this->perusahaan()->id, 404);

        $data = $request->validate([
            'keahlian_id' => [
                'required',
                Rule::exists('keahlian', 'id')->where('aktif', true),
                Rule::unique('syarat_keahlian')->where('lowongan_id', $lowongan->id),
            ],
            'level_minimum' => ['nullable', Rule::in(array_keys(Format::LEVEL))],
            'sifat' => ['required', Rule::in(['wajib', 'opsional'])],
            'bobot' => ['required', 'numeric', 'min:0', 'max:999.99'],
        ], ['keahlian_id.unique' => 'Keahlian ini sudah jadi syarat lowongan.'], ['keahlian_id' => 'keahlian', 'level_minimum' => 'level minimum']);

        $lowongan->syarat()->create($data + ['wajib_terverifikasi' => $request->boolean('wajib_terverifikasi')]);

        return redirect()->route('perusahaan.lowongan.show', $lowongan)->with('success', 'Syarat keahlian ditambahkan.');
    }

    // DELETE /perusahaan/syarat/{syarat} -> hapus: syarat_keahlian
    public function destroy(SyaratKeahlian $syarat)
    {
        $lowongan = $syarat->lowongan;
        abort_unless($lowongan->perusahaan_id === $this->perusahaan()->id, 404);

        if ($lowongan->status === 'dipublikasikan' && $lowongan->syarat()->count() === 1) {
            return back()->with('error', 'Lowongan yang sedang tayang harus punya minimal satu syarat. Tutup atau jadikan draft dulu.');
        }

        $syarat->delete();

        return redirect()->route('perusahaan.lowongan.show', $lowongan)->with('success', 'Syarat keahlian dihapus.');
    }
}
