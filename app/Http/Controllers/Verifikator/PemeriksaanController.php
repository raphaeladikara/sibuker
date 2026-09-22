<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use App\Models\Bukti;
use App\Models\Verifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PemeriksaanController extends Controller
{
    use AksesProfil;

    // GET /verifikator/periksa/{bukti}
    // baca: bukti JOIN jenis_bukti, pemilik, klaim_keahlian pemilik, kewenangan_verifikator, riwayat verifikasi
    public function create(int $bukti)
    {
        [$bukti, $verifikator, $pencari] = $this->siapkan($bukti);

        $klaim = $pencari->klaimKeahlian()->denganStatus()->with('keahlian')->where('aktif', true)->get();
        $wewenang = $verifikator->kewenangan->pluck('id')->all();
        $bukti->load(['jenisBukti', 'pengguna', 'verifikasi.verifikator.pengguna', 'verifikasi.klaimKeahlian.keahlian']);

        return view('verifikator.periksa', compact('bukti', 'verifikator', 'pencari', 'klaim', 'wewenang'));
    }

    // POST /verifikator/periksa/{bukti}
    // tambah: verifikasi (keputusan, catatan, diverifikasi_pada, berlaku_sampai)
    // tambah: bukti_keahlian untuk klaim yang dicentang, dibatasi pada klaim milik pemilik dan dalam kewenangan
    public function store(Request $request, int $bukti)
    {
        [$bukti, $verifikator, $pencari] = $this->siapkan($bukti);

        $data = $request->validate([
            'keputusan' => ['required', Rule::in(['disetujui', 'ditolak', 'menunggu'])],
            'klaim_keahlian_id' => ['array'],
            'klaim_keahlian_id.*' => ['integer'],
            'berlaku_sampai' => ['nullable', 'date', 'after:today'],
            'catatan' => ['nullable', 'required_if:keputusan,ditolak', 'string', 'max:1000'],
        ], ['catatan.required_if' => 'Tulis alasan penolakan supaya pemilik tahu apa yang harus diperbaiki.'], ['berlaku_sampai' => 'masa berlaku']);

        $klaimSah = $pencari->klaimKeahlian()
            ->where('aktif', true)
            ->whereIn('keahlian_id', $verifikator->kewenangan->pluck('id'))
            ->whereIn('id', $data['klaim_keahlian_id'] ?? [])
            ->pluck('id');

        if ($data['keputusan'] === 'disetujui' && $klaimSah->isEmpty()) {
            return back()->withInput()->withErrors(['klaim_keahlian_id' => 'Pilih minimal satu keahlian yang dibuktikan sertifikat ini.']);
        }

        DB::transaction(function () use ($bukti, $verifikator, $data, $klaimSah) {
            $verifikasi = Verifikasi::create([
                'bukti_id' => $bukti->id,
                'verifikator_id' => $verifikator->id,
                'keputusan' => $data['keputusan'],
                'catatan' => $data['catatan'] ?? null,
                'diverifikasi_pada' => $data['keputusan'] === 'menunggu' ? null : now(),
                'berlaku_sampai' => $data['keputusan'] === 'disetujui' ? ($data['berlaku_sampai'] ?? null) : null,
            ]);

            if ($data['keputusan'] === 'disetujui') {
                $verifikasi->klaimKeahlian()->attach($klaimSah);
            }
        });

        $pesan = [
            'disetujui' => 'Sertifikat disetujui. Centang biru langsung muncul di profil ' . $bukti->pengguna->nama . '.',
            'ditolak' => 'Sertifikat ditolak. Catatanmu terkirim ke pemilik.',
            'menunggu' => 'Pemeriksaan ditunda. Sertifikat tetap di antreanmu.',
        ][$data['keputusan']];

        return redirect()->route('verifikator.pemilik.show', $bukti->pengguna_id)->with('success', $pesan);
    }

    /** Memuat bukti dan memastikan verifikator boleh memeriksanya. */
    private function siapkan(int $id): array
    {
        $verifikator = $this->verifikator()->load('kewenangan');
        $bukti = Bukti::denganStatus()->with('pengguna.pencariKerja')->findOrFail($id);
        $pencari = $bukti->pengguna->pencariKerja;

        abort_if($bukti->pengguna_id === $verifikator->pengguna_id, 403, 'Kamu tidak bisa memeriksa sertifikat milik sendiri.');
        abort_unless($pencari && $pencari->klaimKeahlian()->where('aktif', true)->whereIn('keahlian_id', $verifikator->kewenangan->pluck('id'))->exists(), 403, 'Sertifikat ini di luar bidang kewenanganmu.');

        return [$bukti, $verifikator, $pencari];
    }
}
