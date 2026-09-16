<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keahlian;
use App\Models\Pengguna;
use App\Models\Verifikator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Admin: CRUD tabel verifikator dan sinkronisasi kewenangan_verifikator. */
class VerifikatorController extends Controller
{
    public function index(Request $request)
    {
        $data = Verifikator::with(['pengguna', 'kewenangan'])
            ->withCount('verifikasi')
            ->when(trim((string) $request->query('q')), fn ($q, $kata) => $q->where(fn ($w) => $w->where('instansi', 'like', "%{$kata}%")
                ->orWhereHas('pengguna', fn ($p) => $p->where('nama', 'like', "%{$kata}%"))))
            ->get()
            ->sortBy('pengguna.nama');

        return view('admin.verifikator.index', compact('data'));
    }

    public function create()
    {
        return view('admin.verifikator.form', [
            'item' => null,
            'pengguna' => Pengguna::doesntHave('verifikator')->orderBy('nama')->get(),
            'keahlian' => Keahlian::orderBy('kategori')->orderBy('nama')->get(),
        ]);
    }

    // POST /admin/verifikator -> tambah: verifikator + kewenangan_verifikator
    public function store(Request $request)
    {
        $data = $this->validasi($request) + $request->validate([
            'pengguna_id' => ['required', 'exists:pengguna,id', 'unique:verifikator,pengguna_id'],
        ], ['pengguna_id.unique' => 'Akun ini sudah terdaftar sebagai verifikator.'], ['pengguna_id' => 'akun pengguna']);

        DB::transaction(function () use ($data) {
            $verifikator = Verifikator::create($data);
            $verifikator->kewenangan()->sync($data['keahlian_id'] ?? []);
        });

        return redirect()->route('admin.verifikator.index')->with('success', 'Verifikator ditambahkan.');
    }

    public function edit(Verifikator $verifikator)
    {
        return view('admin.verifikator.form', [
            'item' => $verifikator->load(['pengguna', 'kewenangan']),
            'pengguna' => collect(),
            'keahlian' => Keahlian::orderBy('kategori')->orderBy('nama')->get(),
        ]);
    }

    // PUT /admin/verifikator/{verifikator} -> ubah: verifikator, sinkron kewenangan_verifikator
    public function update(Request $request, Verifikator $verifikator)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($verifikator, $data) {
            $verifikator->update($data);
            $verifikator->kewenangan()->sync($data['keahlian_id'] ?? []);
        });

        return redirect()->route('admin.verifikator.index')->with('success', 'Data ' . $verifikator->pengguna->nama . ' diperbarui.');
    }

    // DELETE /admin/verifikator/{verifikator} -> ditolak bila sudah punya riwayat verifikasi (FK RESTRICT)
    public function destroy(Verifikator $verifikator)
    {
        try {
            $verifikator->delete();
        } catch (QueryException) {
            return back()->with('error', $verifikator->pengguna->nama . ' sudah punya riwayat verifikasi, jadi tidak bisa dihapus. Ubah statusnya jadi nonaktif.');
        }

        return redirect()->route('admin.verifikator.index')->with('success', 'Verifikator dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'instansi' => ['nullable', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'status_verifikator' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'keahlian_id' => ['array'],
            'keahlian_id.*' => ['integer', 'exists:keahlian,id'],
        ], [], ['status_verifikator' => 'status', 'keahlian_id' => 'kewenangan']);
    }
}
