<?php

namespace App\Http\Controllers\Perusahaan;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Keahlian;
use App\Models\Lowongan;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** CRUD tabel lowongan milik perusahaan yang login. */
class LowonganController extends Controller
{
    use AksesProfil;

    // GET /perusahaan/lowongan -> baca: lowongan WHERE perusahaan_id = perusahaan login
    public function index()
    {
        $lowongan = $this->perusahaan()->lowongan()->withCount(['lamaran', 'syarat'])->latest('dibuat_pada')->get();

        return view('perusahaan.lowongan.index', compact('lowongan'));
    }

    public function create()
    {
        return view('perusahaan.lowongan.form', ['item' => null, 'kodeSaran' => $this->kodeSaran()]);
    }

    // POST /perusahaan/lowongan -> tambah: lowongan, selalu mulai sebagai draft
    public function store(Request $request)
    {
        $data = $this->validasi($request);
        $lowongan = $this->perusahaan()->lowongan()->create($data + ['status' => 'draft']);

        return redirect()->route('perusahaan.lowongan.show', $lowongan)
            ->with('success', 'Draft tersimpan. Tambahkan minimal satu syarat keahlian, lalu publikasikan.');
    }

    // GET /perusahaan/lowongan/{lowongan} -> baca: lowongan, syarat_keahlian JOIN keahlian, jumlah lamaran
    public function show(Lowongan $lowongan)
    {
        $this->pastikanMilik($lowongan);
        $lowongan->load('syarat.keahlian')->loadCount('lamaran');

        $keahlian = Keahlian::where('aktif', true)
            ->whereNotIn('id', $lowongan->syarat->pluck('keahlian_id'))
            ->orderBy('nama')
            ->get();

        return view('perusahaan.lowongan.show', compact('lowongan', 'keahlian'));
    }

    public function edit(Lowongan $lowongan)
    {
        $this->pastikanMilik($lowongan);

        return view('perusahaan.lowongan.form', ['item' => $lowongan->loadCount('syarat'), 'kodeSaran' => null]);
    }

    // PUT /perusahaan/lowongan/{lowongan} -> ubah: lowongan.
    // Status 'dipublikasikan' mengisi dipublikasikan_pada, 'ditutup' mengisi ditutup_pada.
    public function update(Request $request, Lowongan $lowongan)
    {
        $this->pastikanMilik($lowongan);

        $data = $this->validasi($request, $lowongan) + $request->validate([
            'status' => ['required', Rule::in(Format::STATUS_LOWONGAN)],
        ]);

        if ($data['status'] === 'dipublikasikan' && $lowongan->status !== 'dipublikasikan') {
            if ($this->perusahaan()->status_perusahaan !== 'aktif') {
                return back()->withInput()->withErrors(['status' => 'Perusahaan nonaktif tidak bisa mempublikasikan lowongan.']);
            }
            if ($lowongan->syarat()->doesntExist()) {
                return back()->withInput()->withErrors(['status' => 'Tambahkan minimal satu syarat keahlian sebelum mempublikasikan.']);
            }
            $data['dipublikasikan_pada'] = now();
            $data['ditutup_pada'] = null;
        }

        if ($data['status'] === 'ditutup' && $lowongan->status !== 'ditutup') {
            $data['ditutup_pada'] = now();
        }

        $lowongan->update($data);

        return redirect()->route('perusahaan.lowongan.show', $lowongan)->with('success', 'Lowongan diperbarui.');
    }

    // DELETE /perusahaan/lowongan/{lowongan} -> hapus: lowongan (syarat, lamaran, tahap_seleksi ikut terhapus lewat CASCADE)
    public function destroy(Lowongan $lowongan)
    {
        $this->pastikanMilik($lowongan);
        $lowongan->delete();

        return redirect()->route('perusahaan.lowongan.index')->with('success', 'Lowongan ' . $lowongan->posisi . ' dihapus.');
    }

    private function validasi(Request $request, ?Lowongan $lowongan = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('lowongan', 'kode')->ignore($lowongan?->id)],
            'posisi' => ['required', 'string', 'max:180'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tipe_pekerjaan' => ['required', Rule::in(array_keys(Format::TIPE_PEKERJAAN))],
        ], [], ['tipe_pekerjaan' => 'tipe pekerjaan']);
    }

    /** Contoh: NDS-2026-003 untuk PT Nusantara Data Solusi. */
    private function kodeSaran(): string
    {
        $perusahaan = $this->perusahaan();
        $kata = preg_split('/\s+/', preg_replace('/^(PT|CV|UD)\.?\s+/i', '', $perusahaan->nama));
        $prefix = strtoupper(implode('', array_map(fn ($k) => mb_substr($k, 0, 1), array_slice($kata, 0, 3))));
        $urut = $perusahaan->lowongan()->count() + 1;

        do {
            $kode = sprintf('%s-%s-%03d', $prefix, now()->year, $urut++);
        } while (Lowongan::where('kode', $kode)->exists());

        return $kode;
    }

    private function pastikanMilik(Lowongan $lowongan): void
    {
        abort_unless($lowongan->perusahaan_id === $this->perusahaan()->id, 404);
    }
}
