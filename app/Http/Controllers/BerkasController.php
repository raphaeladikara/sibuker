<?php

namespace App\Http\Controllers;

use App\Models\Bukti;
use App\Models\Lamaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BerkasController extends Controller
{
    /**
     * GET /berkas/bukti/{bukti}
     * Boleh dibuka oleh: pemilik, verifikator aktif, admin, dan perusahaan
     * yang menerima lamaran dari pemilik sertifikat.
     */
    public function bukti(Request $request, Bukti $bukti)
    {
        $pengguna = $request->user();

        $boleh = $bukti->pengguna_id === $pengguna->id
            || $pengguna->punyaPeran('verifikator')
            || $pengguna->is_admin
            || ($pengguna->perusahaan && Lamaran::query()
                ->whereHas('lowongan', fn ($q) => $q->where('perusahaan_id', $pengguna->perusahaan->id))
                ->whereHas('pencariKerja', fn ($q) => $q->where('pengguna_id', $bukti->pengguna_id))
                ->exists());

        abort_unless($boleh, 403);
        abort_unless(Storage::disk('local')->exists($bukti->url_berkas), 404, 'Berkas tidak ditemukan di storage.');

        return Storage::disk('local')->response($bukti->url_berkas, basename($bukti->url_berkas), [
            'Content-Type' => 'application/pdf',
        ], 'inline');
    }
}
