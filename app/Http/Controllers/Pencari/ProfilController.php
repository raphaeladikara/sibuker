<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    use AksesProfil;

    // GET /pencari/profil  -> baca: pengguna JOIN pencari_kerja
    public function edit(Request $request)
    {
        $pengguna = $request->user();
        $profil = $this->pencari();

        return view('pencari.profil', compact('pengguna', 'profil'));
    }

    // PUT /pencari/profil  -> ubah: pengguna (nama, nomor_telepon, foto_profil), pencari_kerja (headline, ringkasan, lokasi, tanggal_lahir)
    public function update(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nomor_telepon' => ['nullable', 'string', 'max:25'],
            'foto_profil' => ['nullable', 'image', 'max:1024'],
            'headline' => ['nullable', 'string', 'max:200'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'ringkasan' => ['nullable', 'string', 'max:2000'],
        ], [], ['foto_profil' => 'foto profil', 'tanggal_lahir' => 'tanggal lahir']);

        $pengguna = $request->user();

        DB::transaction(function () use ($request, $pengguna, $data) {
            $akun = ['nama' => $data['nama'], 'nomor_telepon' => $data['nomor_telepon']];

            if ($request->hasFile('foto_profil')) {
                if ($pengguna->foto_profil) {
                    Storage::disk('public')->delete($pengguna->foto_profil);
                }
                $akun['foto_profil'] = $request->file('foto_profil')->store('foto', 'public');
            }

            $pengguna->update($akun);
            $this->pencari()->update([
                'headline' => $data['headline'],
                'lokasi' => $data['lokasi'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'ringkasan' => $data['ringkasan'],
            ]);
        });

        return redirect()->route('pencari.profil.edit')->with('success', 'Profil tersimpan.');
    }
}
