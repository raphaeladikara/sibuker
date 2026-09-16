<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Models\Bukti;
use App\Models\JenisBukti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Sertifikat Saya = CRUD tabel bukti. PDF disimpan di storage lokal, path-nya di bukti.url_berkas. */
class SertifikatController extends Controller
{
    // GET /pencari/sertifikat -> baca: bukti JOIN jenis_bukti + keputusan verifikasi terbaru
    public function index(Request $request)
    {
        $bukti = Bukti::denganStatus()
            ->with('jenisBukti')
            ->where('pengguna_id', $request->user()->id)
            ->latest('diunggah_pada')
            ->get();

        return view('pencari.sertifikat.index', compact('bukti'));
    }

    public function create()
    {
        return view('pencari.sertifikat.form', ['item' => null, 'jenisBukti' => JenisBukti::orderBy('nama')->get()]);
    }

    // POST /pencari/sertifikat -> simpan PDF ke storage, tambah: bukti
    public function store(Request $request)
    {
        $data = $this->validasi($request, true);
        $data['url_berkas'] = $request->file('berkas')->store('bukti', 'local');
        unset($data['berkas']);

        $bukti = $request->user()->bukti()->create($data);

        return redirect()->route('pencari.sertifikat.show', $bukti)->with('success', 'Sertifikat terunggah dan masuk antrean verifikator.');
    }

    // GET /pencari/sertifikat/{bukti} -> baca: bukti, riwayat verifikasi, bukti_keahlian
    public function show(Request $request, int $bukti)
    {
        $bukti = $this->milik($request, $bukti);
        $bukti->load(['jenisBukti', 'verifikasi.verifikator.pengguna', 'verifikasi.klaimKeahlian.keahlian']);

        return view('pencari.sertifikat.show', compact('bukti'));
    }

    public function edit(Request $request, int $bukti)
    {
        return view('pencari.sertifikat.form', [
            'item' => $this->milik($request, $bukti),
            'jenisBukti' => JenisBukti::orderBy('nama')->get(),
        ]);
    }

    // PUT /pencari/sertifikat/{bukti} -> ubah: bukti. Mengganti berkas membuat sertifikat masuk antrean lagi.
    public function update(Request $request, int $bukti)
    {
        $bukti = $this->milik($request, $bukti);
        $data = $this->validasi($request, false);
        unset($data['berkas']);

        if ($request->hasFile('berkas')) {
            Storage::disk('local')->delete($bukti->url_berkas);
            $data['url_berkas'] = $request->file('berkas')->store('bukti', 'local');
            $data['diunggah_pada'] = now();
        }

        $bukti->update($data);

        $pesan = $request->hasFile('berkas')
            ? 'Berkas diganti. Sertifikat akan diperiksa ulang.'
            : 'Data sertifikat diperbarui.';

        return redirect()->route('pencari.sertifikat.show', $bukti)->with('success', $pesan);
    }

    // DELETE /pencari/sertifikat/{bukti} -> hapus: bukti (verifikasi dan bukti_keahlian ikut terhapus lewat CASCADE)
    public function destroy(Request $request, int $bukti)
    {
        $bukti = $this->milik($request, $bukti);
        Storage::disk('local')->delete($bukti->url_berkas);
        $bukti->delete();

        return redirect()->route('pencari.sertifikat.index')->with('success', 'Sertifikat dihapus.');
    }

    private function validasi(Request $request, bool $berkasWajib): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'jenis_bukti_id' => ['required', 'exists:jenis_bukti,id'],
            'penerbit' => ['nullable', 'string', 'max:200'],
            'tanggal_terbit' => ['nullable', 'date', 'before_or_equal:today'],
            'berkas' => [$berkasWajib ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:2048'],
        ], [], [
            'jenis_bukti_id' => 'jenis bukti',
            'tanggal_terbit' => 'tanggal terbit',
            'berkas' => 'berkas PDF',
        ]);
    }

    private function milik(Request $request, int $id): Bukti
    {
        return Bukti::denganStatus()->where('pengguna_id', $request->user()->id)->findOrFail($id);
    }
}
