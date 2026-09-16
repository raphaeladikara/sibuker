<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function formMasuk()
    {
        // Daftar akun demo hanya tampil di mode lokal.
        $akunDemo = app()->isLocal() ? [
            ['Pencari kerja', 'dimas@mail.test'],
            ['Perusahaan', 'hr@nusantaradata.test'],
            ['Verifikator', 'hendra@lsp-inf.test'],
            ['Admin', 'admin@sibuker.test'],
            ['Pencari + verifikator', 'maya@praktisi.test'],
        ] : [];

        return view('auth.masuk', compact('akunDemo'));
    }

    // POST /masuk -> baca: pengguna (email, password), lalu profil peran yang dimiliki.
    public function masuk(Request $request)
    {
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($kredensial)) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $pengguna = $request->user();

        if ($pengguna->status_akun !== 'aktif') {
            return $this->tolak($request, 'Akun ini sedang ' . $pengguna->status_akun . '. Hubungi administrator.');
        }

        $peran = $pengguna->daftarPeran();
        if (! $peran) {
            return $this->tolak($request, 'Akun ini belum punya profil peran. Hubungi administrator.');
        }

        if (count($peran) > 1) {
            return redirect()->route('masuk.peran');
        }

        return redirect()->intended(route($pengguna->ruteDashboard()));
    }

    // GET /masuk/peran -> akun dengan lebih dari satu peran memilih mau masuk sebagai apa.
    public function pilihPeran(Request $request)
    {
        $peran = $request->user()->daftarPeran();
        if (count($peran) === 1) {
            return redirect()->route($request->user()->ruteDashboard());
        }

        return view('auth.peran', compact('peran'));
    }

    public function formDaftar()
    {
        return view('auth.daftar');
    }

    // POST /daftar -> tulis: pengguna, lalu pencari_kerja ATAU perusahaan dalam satu transaksi.
    // Akun verifikator tidak bisa mendaftar sendiri; dibuat oleh admin.
    public function daftar(Request $request)
    {
        $data = $request->validate([
            'peran' => ['required', Rule::in(['pencari', 'perusahaan'])],
            'nama' => ['required', 'string', 'max:150'],
            'nomor_telepon' => ['nullable', 'string', 'max:25'],
            'email' => ['required', 'email', 'max:191', 'unique:pengguna,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'nama_perusahaan' => ['nullable', 'required_if:peran,perusahaan', 'string', 'max:200'],
            'nib' => ['nullable', 'string', 'max:50', 'unique:perusahaan,nib'],
        ], [], [
            'nama_perusahaan' => 'nama perusahaan',
            'nib' => 'NIB',
        ]);

        $pengguna = DB::transaction(function () use ($data) {
            $pengguna = Pengguna::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['password'],
                'nomor_telepon' => $data['nomor_telepon'] ?? null,
            ]);

            if ($data['peran'] === 'perusahaan') {
                $pengguna->perusahaan()->create(['nama' => $data['nama_perusahaan'], 'nib' => $data['nib'] ?? null]);
            } else {
                $pengguna->pencariKerja()->create([]);
            }

            return $pengguna;
        });

        Auth::login($pengguna);
        $request->session()->regenerate();

        $lanjut = $data['peran'] === 'perusahaan'
            ? 'Akun perusahaan sudah jadi. Lengkapi profil sebelum membuka lowongan.'
            : 'Akun sudah jadi. Mulai dengan mencantumkan keahlianmu.';

        return redirect()->route($pengguna->ruteDashboard())->with('success', $lanjut);
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }

    private function tolak(Request $request, string $pesan)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk')->withErrors(['email' => $pesan])->onlyInput('email');
    }
}
