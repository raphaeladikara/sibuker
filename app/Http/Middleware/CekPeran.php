<?php

namespace App\Http\Middleware;

use App\Models\Pengguna;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dipasang sebagai peran:pencari, peran:perusahaan, peran:verifikator, atau peran:admin.
 * Akun yang statusnya bukan 'aktif' langsung dikeluarkan.
 */
class CekPeran
{
    public function handle(Request $request, Closure $next, string $peran): Response
    {
        $pengguna = $request->user();

        if ($pengguna->status_akun !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('masuk')->withErrors(['email' => 'Akun ini sedang ' . $pengguna->status_akun . '. Hubungi administrator.']);
        }

        abort_unless($pengguna->punyaPeran($peran), 403, 'Akun ini tidak punya akses sebagai ' . Pengguna::PERAN[$peran] . '.');

        $request->session()->put('peran_aktif', $peran);

        return $next($request);
    }
}
