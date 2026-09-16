<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisBukti;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: CRUD tabel jenis_bukti. */
class JenisBuktiController extends Controller
{
    public function index(Request $request)
    {
        $data = JenisBukti::withCount('bukti')
            ->when(trim((string) $request->query('q')), fn ($q, $kata) => $q->where('nama', 'like', "%{$kata}%"))
            ->orderBy('nama')
            ->get();

        return view('admin.jenis-bukti.index', compact('data'));
    }

    public function create()
    {
        return view('admin.jenis-bukti.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        JenisBukti::create($data);

        return redirect()->route('admin.jenis-bukti.index')->with('success', 'Jenis bukti ' . $data['nama'] . ' ditambahkan.');
    }

    public function edit(JenisBukti $jenisBukti)
    {
        return view('admin.jenis-bukti.form', ['item' => $jenisBukti]);
    }

    public function update(Request $request, JenisBukti $jenisBukti)
    {
        $jenisBukti->update($this->validasi($request, $jenisBukti));

        return redirect()->route('admin.jenis-bukti.index')->with('success', 'Jenis bukti diperbarui.');
    }

    // DELETE -> bukti.jenis_bukti_id memakai ON DELETE RESTRICT
    public function destroy(JenisBukti $jenisBukti)
    {
        try {
            $jenisBukti->delete();
        } catch (QueryException) {
            return back()->with('error', $jenisBukti->nama . ' masih dipakai sertifikat, jadi tidak bisa dihapus.');
        }

        return redirect()->route('admin.jenis-bukti.index')->with('success', 'Jenis bukti ' . $jenisBukti->nama . ' dihapus.');
    }

    private function validasi(Request $request, ?JenisBukti $jenisBukti = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_bukti', 'nama')->ignore($jenisBukti?->id)],
        ]);
    }
}
