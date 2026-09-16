<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Admin: CRUD tabel pengguna. */
class PenggunaController extends Controller
{
    // GET /admin/pengguna?q= -> baca: pengguna + profil peran
    public function index(Request $request)
    {
        $data = Pengguna::with(['pencariKerja', 'perusahaan', 'verifikator'])
            ->when(trim((string) $request->query('q')), fn ($q, $kata) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$kata}%")->orWhere('email', 'like', "%{$kata}%")))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status_akun', $s))
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengguna.index', compact('data'));
    }

    public function create()
    {
        return view('admin.pengguna.form', ['item' => null]);
    }

    // POST /admin/pengguna -> tambah: pengguna
    public function store(Request $request)
    {
        $data = $this->validasi($request);
        Pengguna::create($data + ['is_admin' => $request->boolean('is_admin')]);

        return redirect()->route('admin.pengguna.index')->with('success', 'Akun ' . $data['email'] . ' dibuat.');
    }

    public function edit(Pengguna $pengguna)
    {
        return view('admin.pengguna.form', ['item' => $pengguna->load(['pencariKerja', 'perusahaan', 'verifikator'])]);
    }

    // PUT /admin/pengguna/{pengguna} -> ubah: pengguna
    public function update(Request $request, Pengguna $pengguna)
    {
        $data = $this->validasi($request, $pengguna);
        $data['is_admin'] = $request->boolean('is_admin');

        if ($pengguna->is($request->user()) && (! $data['is_admin'] || $data['status_akun'] !== 'aktif')) {
            return back()->withInput()->with('error', 'Kamu tidak bisa mencabut hak admin atau menonaktifkan akunmu sendiri.');
        }

        if (blank($data['password'])) {
            unset($data['password']);
        }

        $pengguna->update($data);

        return redirect()->route('admin.pengguna.index')->with('success', 'Akun ' . $pengguna->email . ' diperbarui.');
    }

    // DELETE /admin/pengguna/{pengguna} -> hapus: pengguna (ditolak bila masih dipakai perusahaan atau verifikasi)
    public function destroy(Request $request, Pengguna $pengguna)
    {
        if ($pengguna->is($request->user())) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        try {
            $pengguna->delete();
        } catch (QueryException) {
            return back()->with('error', $pengguna->nama . ' masih terhubung ke profil perusahaan atau riwayat verifikasi. Nonaktifkan akunnya saja.');
        }

        return redirect()->route('admin.pengguna.index')->with('success', 'Akun ' . $pengguna->email . ' dihapus.');
    }

    private function validasi(Request $request, ?Pengguna $pengguna = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:191', Rule::unique('pengguna', 'email')->ignore($pengguna?->id)],
            'nomor_telepon' => ['nullable', 'string', 'max:25'],
            'password' => [$pengguna ? 'nullable' : 'required', Password::min(8)],
            'status_akun' => ['required', Rule::in(['aktif', 'nonaktif', 'ditangguhkan'])],
        ], [], ['status_akun' => 'status akun']);
    }
}
