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

    private function terverifikasi(int $klaim): bool
    {
        return (bool) DB::table('v_status_keahlian')->where('klaim_keahlian_id', $klaim)->value('terverifikasi');
    }

    /**
     * Mengganti berkas PDF harus mencabut centang biru sampai diperiksa ulang. Sebelum
     * perbaikan, v_status_keahlian tidak melihat bukti.diunggah_pada, sehingga sertifikat
     * yang sudah disetujui bisa ditukar isinya tanpa kehilangan centang biru.
     */
    public function test_mengganti_berkas_mencabut_centang_biru(): void
    {
        $this->assertTrue($this->terverifikasi(1));

        $bukti = DB::table('bukti_keahlian')
            ->join('verifikasi', 'verifikasi.id', '=', 'bukti_keahlian.verifikasi_id')
            ->where('bukti_keahlian.klaim_keahlian_id', 1)
            ->value('verifikasi.bukti_id');

        DB::table('bukti')->where('id', $bukti)->update(['diunggah_pada' => now()->addSecond()]);

        $this->assertFalse($this->terverifikasi(1), 'centang biru harus hilang setelah berkas diganti');
        $this->assertNotNull(Bukti::dalamAntrean()->find($bukti), 'sertifikat harus kembali masuk antrean verifikator');
    }

    /**
     * bukti_keahlian tidak boleh menautkan verifikasi milik satu orang ke klaim milik
     * orang lain. Foreign key tidak bisa menyatakan aturan ini, jadi dijaga trigger.
     */
    public function test_bukti_keahlian_menolak_klaim_milik_orang_lain(): void
    {
        $verifikasi = DB::table('verifikasi')->where('keputusan', 'disetujui')->value('id');
        $pemilik = DB::table('verifikasi')
            ->join('bukti', 'bukti.id', '=', 'verifikasi.bukti_id')
            ->where('verifikasi.id', $verifikasi)
            ->value('bukti.pengguna_id');

        $klaimOrangLain = DB::table('klaim_keahlian')
            ->join('pencari_kerja', 'pencari_kerja.id', '=', 'klaim_keahlian.pencari_kerja_id')
            ->where('pencari_kerja.pengguna_id', '!=', $pemilik)
            ->value('klaim_keahlian.id');

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('bukti_keahlian')->insert([
            'verifikasi_id' => $verifikasi,
            'klaim_keahlian_id' => $klaimOrangLain,
        ]);
    }

    /**
     * Satu akun boleh merangkap verifikator dan pencari kerja, tetapi tidak boleh
     * memeriksa sertifikatnya sendiri. Sebelum perbaikan, aturan ini hanya dijaga
     * controller sehingga data awal seeder sempat melanggarnya tanpa terdeteksi.
     */
    public function test_verifikator_tidak_bisa_memeriksa_sertifikat_sendiri(): void
    {
        $maya = $this->akun('maya@praktisi.test');
        $buktiSendiri = Bukti::where('pengguna_id', $maya->id)->firstOrFail();

        // Lewat aplikasi: ditolak middleware/controller.
        $this->actingAs($maya)->post("/verifikator/periksa/{$buktiSendiri->id}", [
            'keputusan' => 'disetujui',
            'klaim_keahlian_id' => [9],
        ])->assertForbidden();

        // Langsung ke database: ditolak trigger.
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('verifikasi')->insert([
            'bukti_id' => $buktiSendiri->id,
            'verifikator_id' => $maya->verifikator->id,
            'keputusan' => 'disetujui',
            'diverifikasi_pada' => now(),
        ]);
    }

    /** Keahlian yang dinonaktifkan admin tidak boleh lagi tampil sebagai terverifikasi. */
    public function test_keahlian_nonaktif_tidak_lagi_terverifikasi(): void
    {
        $this->assertTrue($this->terverifikasi(1));

        $keahlian = DB::table('klaim_keahlian')->where('id', 1)->value('keahlian_id');
        DB::table('keahlian')->where('id', $keahlian)->update(['aktif' => false]);

        $this->assertFalse($this->terverifikasi(1));
    }

    /** CHECK constraint menolak kombinasi kolom yang tidak konsisten. */
    public function test_check_constraint_menjaga_konsistensi_kolom(): void
    {
        $gagal = 0;

        $kasus = [
            // keputusan sudah final tetapi waktu pemeriksaan kosong
            fn () => DB::table('verifikasi')->where('id', 1)->update(['diverifikasi_pada' => null]),
            // masa berlaku diisi pada verifikasi yang ditolak
            fn () => DB::table('verifikasi')->where('keputusan', 'ditolak')->update(['berlaku_sampai' => '2030-01-01']),
            // lowongan berstatus dipublikasikan tanpa tanggal publikasi
            fn () => DB::table('lowongan')->where('status', 'dipublikasikan')->update(['dipublikasikan_pada' => null]),
        ];

        foreach ($kasus as $jalankan) {
            try {
                $jalankan();
            } catch (\Illuminate\Database\QueryException) {
                $gagal++;
            }
        }

        $this->assertSame(count($kasus), $gagal, 'setiap kombinasi kolom yang tidak konsisten harus ditolak database');
    }
}
