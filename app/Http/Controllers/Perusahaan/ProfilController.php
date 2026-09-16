<?php

namespace App\Http\Controllers\Perusahaan;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfilController extends Controller
{
    use AksesProfil;

    // GET /perusahaan/profil -> baca: perusahaan JOIN pengguna
    public function edit()
    {
        $perusahaan = $this->perusahaan()->load('pengguna');

        return view('perusahaan.profil', compact('perusahaan'));
    }

    // PUT /perusahaan/profil -> ubah: perusahaan (nama, nib, deskripsi, alamat, situs_web, logo)
    public function update(Request $request)
    {
        $perusahaan = $this->perusahaan();

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:200'],
            'nib' => ['nullable', 'string', 'max:50', Rule::unique('perusahaan', 'nib')->ignore($perusahaan->id)],
            'deskripsi' => ['nullable', 'string', 'max:3000'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'situs_web' => ['nullable', 'url', 'max:500'],
            'logo' => ['nullable', 'image', 'max:1024'],
        ], [], ['nib' => 'NIB', 'situs_web' => 'situs web']);

        unset($data['logo']);
        if ($request->hasFile('logo')) {
            if ($perusahaan->logo) {
                Storage::disk('public')->delete($perusahaan->logo);
            }
            $data['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $perusahaan->update($data);

        return redirect()->route('perusahaan.profil.edit')->with('success', 'Profil perusahaan tersimpan.');
    }
}
