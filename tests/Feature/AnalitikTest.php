<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

/**
 * Halaman analitik membaca sibuker_dw lewat koneksi "dw". Test ini hanya membaca warehouse,
 * tidak pernah menulis, dan dilewati kalau sibuker_dw belum ada di server MySQL.
 */
class AnalitikTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            DB::connection('dw')->table('fakta_lamaran')->limit(1)->get();
        } catch (Throwable) {
            $this->markTestSkipped('sibuker_dw belum tersedia.');
        }
    }

    private function admin(): Pengguna
    {
        return Pengguna::where('email', 'admin@sibuker.test')->firstOrFail();
    }

    public function test_hanya_admin_yang_bisa_membuka(): void
    {
        $this->get('/admin/analitik')->assertRedirect('/masuk');
        $this->actingAs(Pengguna::where('email', 'dimas@mail.test')->firstOrFail());
        $this->get('/admin/analitik')->assertForbidden();
    }

    public function test_angka_sama_dengan_query_langsung_ke_warehouse(): void
    {
        $dw = DB::connection('dw');
        $total = (int) $dw->table('fakta_lamaran')->sum('jumlah_lamaran');
        $q2 = (int) $dw->table('fakta_lamaran as f')->join('dim_waktu as w', 'w.waktu_key', '=', 'f.waktu_key')
            ->where('w.kuartal', 'Q2')->sum('f.jumlah_lamaran');

        $this->actingAs($this->admin());

        $semua = $this->get('/admin/analitik')->assertOk();
        $this->assertSame($total, $semua->viewData('kpi')['lamaran']);

        // slice: hanya Q2, dan pembandingnya kuartal sebelumnya
        $slice = $this->get('/admin/analitik?kuartal=Q2')->assertOk();
        $this->assertSame($q2, $slice->viewData('kpi')['lamaran']);
        $this->assertSame('Q1', $slice->viewData('sebelum'));
        $this->assertSame($total - $q2, $slice->viewData('kpiSebelum')['lamaran']);
    }

    public function test_drill_down_bulan_menjumlah_ke_kuartal(): void
    {
        $this->actingAs($this->admin());

        $kuartal = collect($this->get('/admin/analitik?tingkat=kuartal')->viewData('tren'))->pluck('n', 'label');
        $bulanQ1 = collect($this->get('/admin/analitik?tingkat=bulan&kuartal=Q1')->viewData('tren'));

        $this->assertSame(['Januari', 'Februari', 'Maret'], $bulanQ1->pluck('label')->all());
        $this->assertEquals($kuartal['Q1'], $bulanQ1->sum('n'));
    }

    public function test_dice_dan_filter_tidak_dikenal_diabaikan(): void
    {
        $this->actingAs($this->admin());

        $dice = $this->get('/admin/analitik?kuartal=Q2&wilayah=Surabaya&bidang=Data%20Analyst')->assertOk();
        $silang = $dice->viewData('silang');
        $this->assertSame($silang['Data Analyst']['Surabaya'], $dice->viewData('kpi')['lamaran']);

        // nilai di luar dimensi tidak boleh ikut ke query
        $asal = $this->get("/admin/analitik?kuartal=Q9&wilayah=' OR 1=1 --&tingkat=menit")->assertOk();
        $this->assertSame(['kuartal' => null, 'wilayah' => null, 'bidang' => null], $asal->viewData('filter'));
        $this->assertSame('kuartal', $asal->viewData('tingkat'));
    }
}
