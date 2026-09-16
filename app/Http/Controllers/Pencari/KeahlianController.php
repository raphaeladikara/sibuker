<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Keahlian;
use App\Models\KlaimKeahlian;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Keahlian Saya = CRUD tabel klaim_keahlian milik pencari kerja yang login. */
class KeahlianController extends Controller
{
    use AksesProfil;

    // GET /pencari/keahlian -> baca: klaim_keahlian JOIN keahlian + v_status_keahlian
    public function index()
    {
        $klaim = $this->pencari()->klaimKeahlian()
            ->denganStatus()
            ->with('keahlian')
            ->orderByDesc('aktif')
            ->orderByDesc('terverifikasi')
            ->orderBy('tanggal_ditambahkan')
            ->get();

        return view('pencari.keahlian.index', compact('klaim'));
    }

    // GET /pencari/keahlian/create -> baca: keahlian aktif yang belum dicantumkan
    public function create()
    {
        return view('pencari.keahlian.form', ['item' => null, 'keahlian' => $this->pilihan()]);
    }

    // POST /pencari/keahlian -> tambah: klaim_keahlian (UNIQUE pencari_kerja_id + keahlian_id)
    public function store(Request $request)
    {
        $pencari = $this->pencari();

        $data = $request->validate([
            'keahlian_id' => [
                'required',
                Rule::exists('keahlian', 'id')->where('aktif', true),
                Rule::unique('klaim_keahlian')->where('pencari_kerja_id', $pencari->id),
            ],
            'level_klaim' => ['required', Rule::in(array_keys(Format::LEVEL))],
        ], ['keahlian_id.unique' => 'Keahlian ini sudah ada di profilmu.'], ['keahlian_id' => 'keahlian', 'level_klaim' => 'level']);

        $pencari->klaimKeahlian()->create($data + ['aktif' => $request->boolean('aktif', true)]);

        return redirect()->route('pencari.keahlian.index')->with('success', 'Keahlian ditambahkan ke profil.');
    }

    public function edit(KlaimKeahlian $klaim)
    {
        $this->pastikanMilik($klaim);

        return view('pencari.keahlian.form', ['item' => $klaim->load('keahlian'), 'keahlian' => collect()]);
    }

    // PUT /pencari/keahlian/{klaim} -> ubah: klaim_keahlian (level_klaim, aktif)
    public function update(Request $request, KlaimKeahlian $klaim)
    {
        $this->pastikanMilik($klaim);

        $data = $request->validate([
            'level_klaim' => ['required', Rule::in(array_keys(Format::LEVEL))],
        ], [], ['level_klaim' => 'level']);

        $klaim->update($data + ['aktif' => $request->boolean('aktif')]);

        return redirect()->route('pencari.keahlian.index')->with('success', 'Keahlian ' . $klaim->keahlian->nama . ' diperbarui.');
    }

    // DELETE /pencari/keahlian/{klaim} -> hapus: klaim_keahlian (bukti_keahlian ikut terhapus lewat CASCADE)
    public function destroy(KlaimKeahlian $klaim)
    {
        $this->pastikanMilik($klaim);
        $nama = $klaim->keahlian->nama;
        $klaim->delete();

        return redirect()->route('pencari.keahlian.index')->with('success', $nama . ' dihapus dari profil.');
    }

    private function pilihan()
    {
        return Keahlian::where('aktif', true)
            ->whereNotIn('id', $this->pencari()->klaimKeahlian()->pluck('keahlian_id'))
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();
    }

    private function pastikanMilik(KlaimKeahlian $klaim): void
    {
        abort_unless($klaim->pencari_kerja_id === $this->pencari()->id, 404);
    }
}
