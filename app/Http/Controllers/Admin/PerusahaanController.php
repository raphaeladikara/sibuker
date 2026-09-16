<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: memantau dan mengubah status perusahaan. Akunnya dibuat lewat pendaftaran. */
class PerusahaanController extends Controller
{
    public function index(Request $request)
    {
        $data = Perusahaan::with('pengguna')
            ->withCount('lowongan')
            ->when(trim((string) $request->query('q')), fn ($q, $kata) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$kata}%")->orWhere('nib', 'like', "%{$kata}%")))
            ->orderBy('nama')
            ->get();

        return view('admin.perusahaan.index', compact('data'));
    }

    public function edit(Perusahaan $perusahaan)
    {
        return view('admin.perusahaan.form', ['item' => $perusahaan->load('pengguna')->loadCount('lowongan')]);
    }

    // PUT /admin/perusahaan/{perusahaan} -> ubah: perusahaan.status_perusahaan
    public function update(Request $request, Perusahaan $perusahaan)
    {
        $data = $request->validate([
            'status_perusahaan' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [], ['status_perusahaan' => 'status']);

        $perusahaan->update($data);

        return redirect()->route('admin.perusahaan.index')->with('success', $perusahaan->nama . ' sekarang ' . $data['status_perusahaan'] . '.');
    }
}
