<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keahlian;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: CRUD tabel keahlian. */
class KeahlianController extends Controller
{
    public function index(Request $request)
    {
        $data = Keahlian::withCount(['klaim', 'syarat'])
            ->when(trim((string) $request->query('q')), fn ($q, $kata) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$kata}%")->orWhere('kategori', 'like', "%{$kata}%")))
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();

        return view('admin.keahlian.index', compact('data'));
    }

    public function create()
    {
        return view('admin.keahlian.form', ['item' => null, 'kategori' => $this->kategori()]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);
        Keahlian::create($data + ['aktif' => $request->boolean('aktif')]);

        return redirect()->route('admin.keahlian.index')->with('success', 'Keahlian ' . $data['nama'] . ' ditambahkan.');
    }

    public function edit(Keahlian $keahlian)
    {
        return view('admin.keahlian.form', ['item' => $keahlian, 'kategori' => $this->kategori()]);
    }

    public function update(Request $request, Keahlian $keahlian)
    {
        $data = $this->validasi($request, $keahlian);
        $keahlian->update($data + ['aktif' => $request->boolean('aktif')]);

        return redirect()->route('admin.keahlian.index')->with('success', 'Keahlian ' . $keahlian->nama . ' diperbarui.');
    }

    // DELETE -> klaim_keahlian dan syarat_keahlian memakai ON DELETE RESTRICT
    public function destroy(Keahlian $keahlian)
    {
        try {
            $keahlian->delete();
        } catch (QueryException) {
            return back()->with('error', $keahlian->nama . ' masih dipakai klaim atau syarat lowongan. Nonaktifkan saja supaya tidak bisa dipilih lagi.');
        }

        return redirect()->route('admin.keahlian.index')->with('success', 'Keahlian ' . $keahlian->nama . ' dihapus.');
    }

    private function validasi(Request $request, ?Keahlian $keahlian = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:120', Rule::unique('keahlian', 'nama')->ignore($keahlian?->id)],
            'kategori' => ['nullable', 'string', 'max:120'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function kategori()
    {
        return Keahlian::whereNotNull('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
    }
}
