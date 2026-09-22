<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\Pencari;
use App\Http\Controllers\Perusahaan;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\Verifikator;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SIBUKER
|--------------------------------------------------------------------------
| Lima kelompok route: publik, pencari kerja, perusahaan, verifikator, admin.
| Tiap kelompok peran punya prefix URL, prefix nama route, dan middleware
| 'peran:*' sendiri. Middleware itu mengecek profil peran pada akun yang login
| (pencari_kerja, perusahaan, verifikator aktif, atau pengguna.is_admin).
*/

// ---------------------------------------------------------------------
// 1. PUBLIK
// ---------------------------------------------------------------------
Route::get('/', [PublikController::class, 'beranda'])->name('beranda');
Route::get('/lowongan', [PublikController::class, 'lowongan'])->name('lowongan.index');
Route::get('/lowongan/{lowongan}', [PublikController::class, 'detailLowongan'])->name('lowongan.show');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'masuk'])->name('masuk.proses')->middleware('throttle:10,1');
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->name('daftar.proses');
});

Route::middleware('auth')->group(function () {
    Route::get('/masuk/peran', [AuthController::class, 'pilihPeran'])->name('masuk.peran');
    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');

    // Berkas PDF sertifikat dilayani lewat controller supaya hak aksesnya dicek.
    Route::get('/berkas/bukti/{bukti}', [BerkasController::class, 'bukti'])->name('berkas.bukti');
});

// ---------------------------------------------------------------------
// 2. PENCARI KERJA  (pencari_kerja, klaim_keahlian, bukti, lamaran)
// ---------------------------------------------------------------------
Route::prefix('pencari')->name('pencari.')->middleware(['auth', 'peran:pencari'])->group(function () {
    Route::get('/dashboard', [Pencari\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [Pencari\ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [Pencari\ProfilController::class, 'update'])->name('profil.update');

    Route::resource('keahlian', Pencari\KeahlianController::class)->except(['show'])->parameters(['keahlian' => 'klaim']);
    Route::resource('sertifikat', Pencari\SertifikatController::class)->parameters(['sertifikat' => 'bukti']);

    Route::get('/lowongan', [Pencari\LowonganController::class, 'index'])->name('lowongan.index');
    Route::post('/lowongan/{lowongan}/lamar', [Pencari\LowonganController::class, 'lamar'])->name('lowongan.lamar');

    Route::get('/lamaran', [Pencari\LamaranController::class, 'index'])->name('lamaran.index');
    Route::get('/lamaran/{lamaran}', [Pencari\LamaranController::class, 'show'])->name('lamaran.show');
    Route::patch('/lamaran/{lamaran}/tarik', [Pencari\LamaranController::class, 'tarik'])->name('lamaran.tarik');
});

// ---------------------------------------------------------------------
// 3. PERUSAHAAN  (perusahaan, lowongan, syarat_keahlian, lamaran, tahap_seleksi)
// ---------------------------------------------------------------------
Route::prefix('perusahaan')->name('perusahaan.')->middleware(['auth', 'peran:perusahaan'])->group(function () {
    Route::get('/dashboard', [Perusahaan\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [Perusahaan\ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [Perusahaan\ProfilController::class, 'update'])->name('profil.update');

    Route::resource('lowongan', Perusahaan\LowonganController::class);

    Route::post('/lowongan/{lowongan}/syarat', [Perusahaan\SyaratController::class, 'store'])->name('syarat.store');
    Route::delete('/syarat/{syarat}', [Perusahaan\SyaratController::class, 'destroy'])->name('syarat.destroy');

    Route::get('/lowongan/{lowongan}/pelamar', [Perusahaan\PelamarController::class, 'index'])->name('pelamar.index');
    Route::get('/pelamar/{lamaran}', [Perusahaan\PelamarController::class, 'show'])->name('pelamar.show');
    Route::patch('/pelamar/{lamaran}/status', [Perusahaan\PelamarController::class, 'updateStatus'])->name('pelamar.status');

    Route::post('/pelamar/{lamaran}/tahap', [Perusahaan\TahapSeleksiController::class, 'store'])->name('tahap.store');
    Route::put('/tahap/{tahap}', [Perusahaan\TahapSeleksiController::class, 'update'])->name('tahap.update');
    Route::delete('/tahap/{tahap}', [Perusahaan\TahapSeleksiController::class, 'destroy'])->name('tahap.destroy');
});

// ---------------------------------------------------------------------
// 4. VERIFIKATOR  (bukti, verifikasi, bukti_keahlian, kewenangan_verifikator)
// ---------------------------------------------------------------------
Route::prefix('verifikator')->name('verifikator.')->middleware(['auth', 'peran:verifikator'])->group(function () {
    Route::get('/dashboard', [Verifikator\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pemilik/{pengguna}', [Verifikator\DashboardController::class, 'pemilik'])->name('pemilik.show');
    Route::get('/periksa/{bukti}', [Verifikator\PemeriksaanController::class, 'create'])->name('periksa.create');
    Route::post('/periksa/{bukti}', [Verifikator\PemeriksaanController::class, 'store'])->name('periksa.store');
    Route::get('/riwayat', [Verifikator\RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/kewenangan', [Verifikator\RiwayatController::class, 'kewenangan'])->name('kewenangan');
});

// ---------------------------------------------------------------------
// 5. ADMINISTRATOR  (pengguna, verifikator, perusahaan, keahlian, jenis_bukti)
// ---------------------------------------------------------------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'peran:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pengguna', Admin\PenggunaController::class)->except(['show'])->parameters(['pengguna' => 'pengguna']);
    Route::resource('verifikator', Admin\VerifikatorController::class)->except(['show'])->parameters(['verifikator' => 'verifikator']);
    Route::resource('perusahaan', Admin\PerusahaanController::class)->only(['index', 'edit', 'update'])->parameters(['perusahaan' => 'perusahaan']);
    Route::resource('keahlian', Admin\KeahlianController::class)->except(['show'])->parameters(['keahlian' => 'keahlian']);
    Route::resource('jenis-bukti', Admin\JenisBuktiController::class)->except(['show'])->parameters(['jenis-bukti' => 'jenisBukti']);
});
