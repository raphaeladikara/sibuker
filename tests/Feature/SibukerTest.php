<?php

namespace Tests\Feature;

use App\Models\Bukti;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Diuji ke MySQL (sibuker_pt_test) karena skema memakai view dan CHECK constraint. */
class SibukerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function akun(string $email): Pengguna
    {
        return Pengguna::where('email', $email)->firstOrFail();
    }

    public function test_halaman_publik_terbuka(): void
    {
        foreach (['/', '/lowongan', '/lowongan?tipe[]=kontrak&verifikasi=tidak_wajib&keahlian=1', '/lowongan/1', '/masuk', '/daftar'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/lowongan/3')->assertNotFound(); // draft
    }

    public function test_semua_halaman_panel_terbuka_per_peran(): void
    {
        $halaman = [
            'dimas@mail.test' => ['/pencari/dashboard', '/pencari/profil', '/pencari/keahlian', '/pencari/keahlian/create', '/pencari/keahlian/1/edit',
                '/pencari/sertifikat', '/pencari/sertifikat/create', '/pencari/sertifikat/1', '/pencari/sertifikat/1/edit',
                '/pencari/lowongan', '/pencari/lowongan?hanya_cocok=1', '/pencari/lamaran', '/pencari/lamaran/1', '/lowongan/1', '/lowongan', '/berkas/bukti/1'],
            'hr@nusantaradata.test' => ['/perusahaan/dashboard', '/perusahaan/profil', '/perusahaan/lowongan', '/perusahaan/lowongan/create',
                '/perusahaan/lowongan/1', '/perusahaan/lowongan/1/edit', '/perusahaan/lowongan/1/pelamar', '/perusahaan/pelamar/1', '/berkas/bukti/1'],
            'hendra@lsp-inf.test' => ['/verifikator/dashboard', '/verifikator/periksa/2', '/verifikator/periksa/6', '/verifikator/riwayat',
                '/verifikator/riwayat?keputusan=disetujui', '/verifikator/kewenangan'],
            'admin@sibuker.test' => ['/admin/dashboard', '/admin/pengguna', '/admin/pengguna?q=dimas', '/admin/pengguna/create', '/admin/pengguna/2/edit',
                '/admin/verifikator', '/admin/verifikator/create', '/admin/verifikator/1/edit', '/admin/perusahaan', '/admin/perusahaan/1/edit',
                '/admin/keahlian', '/admin/keahlian/create', '/admin/keahlian/1/edit', '/admin/jenis-bukti', '/admin/jenis-bukti/create', '/admin/jenis-bukti/1/edit'],
            'maya@praktisi.test' => ['/masuk/peran', '/pencari/dashboard', '/verifikator/dashboard'],
        ];

        foreach ($halaman as $email => $urls) {
            $this->actingAs($this->akun($email));
            foreach ($urls as $url) {
                $this->get($url)->assertOk();
            }
        }
    }

    public function test_hak_akses_dijaga(): void
    {
        $this->get('/pencari/dashboard')->assertRedirect('/masuk');

        $this->actingAs($this->akun('dimas@mail.test'));
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/perusahaan/dashboard')->assertForbidden();
        $this->get('/pencari/lamaran/2')->assertNotFound();       // lamaran milik Salsa
        $this->get('/pencari/sertifikat/3')->assertNotFound();    // sertifikat milik Salsa

        $this->actingAs($this->akun('rekrut@arunika.test'));
        $this->get('/perusahaan/lowongan/1')->assertNotFound();   // lowongan perusahaan lain
        $this->get('/berkas/bukti/1')->assertForbidden();         // Dimas tidak melamar ke Arunika

        $this->actingAs($this->akun('maya@praktisi.test'));
        $this->get('/verifikator/periksa/5')->assertForbidden();  // sertifikat miliknya sendiri
    }

    public function test_status_centang_biru_dibaca_dari_view(): void
    {
        $terverifikasi = DB::table('v_status_keahlian')->where('terverifikasi', 1)->pluck('klaim_keahlian_id')->sort()->values()->all();
        $this->assertSame([1, 2, 4, 6], $terverifikasi);
    }

    public function test_login_dan_pilih_peran(): void
    {
        $this->post('/masuk', ['email' => 'dimas@mail.test', 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->post('/masuk', ['email' => 'nadia@mail.test', 'password' => 'password'])->assertSessionHasErrors('email'); // nonaktif
        $this->post('/masuk', ['email' => 'dimas@mail.test', 'password' => 'password'])->assertRedirect('/pencari/dashboard');
        $this->post('/keluar');
        $this->post('/masuk', ['email' => 'maya@praktisi.test', 'password' => 'password'])->assertRedirect('/masuk/peran');
    }

    public function test_daftar_perusahaan_membuat_dua_baris(): void
    {
        $this->post('/daftar', [
            'peran' => 'perusahaan', 'nama' => 'Budi', 'email' => 'budi@contoh.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123', 'nama_perusahaan' => 'PT Contoh',
        ])->assertRedirect('/perusahaan/dashboard');

        $this->assertDatabaseHas('perusahaan', ['nama' => 'PT Contoh']);
    }

    public function test_melamar_menyimpan_skor_dan_menolak_duplikat(): void
    {
        $this->actingAs($this->akun('yohanes@mail.test'));
        $this->post('/pencari/lowongan/1/lamar')->assertRedirect();
        $this->assertDatabaseHas('lamaran', ['lowongan_id' => 1, 'pencari_kerja_id' => 3, 'skor_kecocokan' => 0]);

        $this->post('/pencari/lowongan/1/lamar')->assertSessionHas('error');
        $this->assertSame(1, Lamaran::where('lowongan_id', 1)->where('pencari_kerja_id', 3)->count());
    }

    public function test_verifikasi_disetujui_memberi_centang_biru(): void
    {
        $this->actingAs($this->akun('hendra@lsp-inf.test'));

        // Klaim 3 = Visualisasi Data milik Dimas, belum terverifikasi.
        $this->assertSame(0, (int) DB::table('v_status_keahlian')->where('klaim_keahlian_id', 3)->value('terverifikasi'));

        $this->post('/verifikator/periksa/2', [
            'keputusan' => 'disetujui',
            'klaim_keahlian_id' => [3],
            'berlaku_sampai' => now()->addYear()->format('Y-m-d'),
        ])->assertRedirect('/verifikator/dashboard');

        $this->assertSame(1, (int) DB::table('v_status_keahlian')->where('klaim_keahlian_id', 3)->value('terverifikasi'));
        $this->assertNull(Bukti::dalamAntrean()->find(2));

        $this->post('/verifikator/periksa/6', ['keputusan' => 'ditolak'])->assertSessionHasErrors('catatan');
    }

    public function test_publikasi_butuh_syarat(): void
    {
        $this->actingAs($this->akun('rekrut@arunika.test'));
        DB::table('syarat_keahlian')->where('lowongan_id', 3)->delete();

        $data = ['kode' => 'ARD-2026-005', 'posisi' => 'Magang UI/UX Designer', 'deskripsi' => 'Membantu tim produk.', 'lokasi' => 'Remote', 'tipe_pekerjaan' => 'magang', 'status' => 'dipublikasikan'];
        $this->put('/perusahaan/lowongan/3', $data)->assertSessionHasErrors('status');

        $this->post('/perusahaan/lowongan/3/syarat', ['keahlian_id' => 5, 'level_minimum' => 'pemula', 'sifat' => 'wajib', 'bobot' => 1])->assertRedirect();
        $this->put('/perusahaan/lowongan/3', $data)->assertRedirect('/perusahaan/lowongan/3');
        $this->assertNotNull(Lowongan::find(3)->dipublikasikan_pada);
    }

    public function test_hapus_master_yang_dipakai_ditolak_restrict(): void
    {
        $this->actingAs($this->akun('admin@sibuker.test'));
        $this->delete('/admin/keahlian/1')->assertSessionHas('error');
        $this->assertDatabaseHas('keahlian', ['id' => 1]);
        $this->delete('/admin/verifikator/1')->assertSessionHas('error');
    }
}
