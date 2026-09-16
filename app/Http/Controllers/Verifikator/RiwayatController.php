<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Concerns\AksesProfil;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiwayatController extends Controller
{
    use AksesProfil;

    // GET /verifikator/riwayat?keputusan= -> baca: verifikasi WHERE verifikator_id = login, JOIN bukti, bukti_keahlian
    public function index(Request $request)
    {
        $request->validate(['keputusan' => ['nullable', Rule::in(['disetujui', 'ditolak', 'menunggu'])]]);

        $riwayat = $this->verifikator()->verifikasi()
            ->with(['bukti.pengguna', 'bukti.jenisBukti', 'klaimKeahlian.keahlian'])
            ->when($request->query('keputusan'), fn ($q, $k) => $q->where('keputusan', $k))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('verifikator.riwayat', compact('riwayat'));
    }

    // GET /verifikator/kewenangan -> baca: kewenangan_verifikator JOIN keahlian (hanya lihat, diatur admin)
    public function kewenangan()
    {
        $verifikator = $this->verifikator()->load(['pengguna', 'kewenangan' => fn ($q) => $q->withCount('klaim')->orderBy('kategori')->orderBy('nama')]);

        return view('verifikator.kewenangan', compact('verifikator'));
    }
}
