<?php

namespace Tests\Feature;

use App\Models\Bukti;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AntreanVerifikatorTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_antrean_merangkum_beberapa_sertifikat_milik_satu_orang(): void
    {
        Bukti::create([
            'pengguna_id' => 2, 'jenis_bukti_id' => 1, 'judul' => 'Pelatihan tambahan',
            'url_berkas' => 'bukti/tambahan.pdf', 'diunggah_pada' => '2026-09-15 10:00',
        ]);

        $response = $this->actingAs(Pengguna::findOrFail(6))->get('/verifikator/dashboard');
        $response->assertOk()->assertViewHas('antreanPemilik', function ($orang) {
            return $orang->count() === 2
                && $orang->pluck('pengguna.id')->all() === [2, 3]
                && $orang->pluck('jumlah_menunggu')->all() === [2, 1];
        });
        $response->assertSee('/verifikator/pemilik/2', false)
            ->assertDontSee('/verifikator/periksa/2', false);
        $this->assertSame(1, substr_count($response->getContent(), 'Dimas Prasetyo'));
    }

    public function test_halaman_pemilik_memuat_semua_sertifikat_dan_mendahulukan_antrean(): void
    {
        $response = $this->actingAs(Pengguna::findOrFail(6))->get('/verifikator/pemilik/2');
        $response->assertOk()->assertViewHas('sertifikat', fn ($items) => $items->pluck('id')->all() === [2, 1])
            ->assertSee('Pelatihan Dashboard dengan Looker Studio')
            ->assertSee('Sertifikat Kompetensi Associate Data Analyst')
            ->assertSee('Disetujui')->assertSee('Menunggu')
            ->assertSee('/verifikator/periksa/2', false)
            ->assertDontSee('Uji Kompetensi Junior Web Developer');
    }

    public function test_akses_pemilik_dibatasi_peran_kepemilikan_dan_kewenangan(): void
    {
        $this->get('/verifikator/pemilik/2')->assertRedirect('/masuk');
        $this->actingAs(Pengguna::findOrFail(2))->get('/verifikator/pemilik/3')->assertForbidden();
        $this->actingAs(Pengguna::findOrFail(7))->get('/verifikator/pemilik/7')->assertForbidden();
        $this->actingAs(Pengguna::findOrFail(6))->get('/verifikator/pemilik/4')->assertForbidden();
        $this->get('/verifikator/pemilik/8')->assertForbidden();
        $this->get('/verifikator/pemilik/99999')->assertNotFound();
        DB::table('klaim_keahlian')->where('pencari_kerja_id', 1)->update(['aktif' => false]);
        $this->get('/verifikator/pemilik/2')->assertForbidden();
        $this->get('/verifikator/dashboard')->assertDontSee('Dimas Prasetyo');
    }

    public function test_setelah_keputusan_kembali_ke_pemilik_dan_antrean_diperbarui(): void
    {
        $this->actingAs(Pengguna::findOrFail(6));
        $this->post('/verifikator/periksa/2', ['keputusan' => 'ditolak', 'catatan' => 'Nomor tidak cocok.'])
            ->assertRedirect('/verifikator/pemilik/2');
        $this->get('/verifikator/dashboard')->assertDontSee('Dimas Prasetyo')->assertSee('Salsabila Putri');
        $this->get('/verifikator/pemilik/2')->assertOk()->assertSee('Ditolak')
            ->assertViewHas('jumlahMenunggu', 0);
        $this->get('/verifikator/periksa/2')->assertOk()->assertSee('/verifikator/pemilik/2', false);
    }

    public function test_sertifikat_yang_diunggah_ulang_masuk_antrean_pemilik(): void
    {
        DB::table('bukti')->where('id', 1)->update(['diunggah_pada' => '2026-09-20 10:00']);
        $this->actingAs(Pengguna::findOrFail(6))->get('/verifikator/dashboard')
            ->assertOk()->assertViewHas('antreanPemilik', fn ($orang) => $orang->first()->jumlah_menunggu === 2);
        $this->get('/verifikator/pemilik/2')->assertOk()->assertViewHas('jumlahMenunggu', 2);
    }

    public function test_antrean_kosong_jika_tidak_ada_pemilik_dalam_kewenangan(): void
    {
        $this->actingAs(Pengguna::findOrFail(7))->get('/verifikator/dashboard')
            ->assertOk()->assertViewHas('antreanPemilik', fn ($orang) => $orang->isEmpty())
            ->assertSee('Antrean kosong');
    }
}
