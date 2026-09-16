<?php

namespace App\Support;

use App\Models\Lowongan;
use App\Models\PencariKerja;
use Illuminate\Support\Collection;

/**
 * Pencocokan syarat_keahlian lowongan dengan klaim_keahlian pencari kerja.
 *
 * Satu syarat terpenuhi kalau klaimnya aktif, level klaim >= level minimum,
 * dan (bila syarat meminta) klaim itu terverifikasi menurut v_status_keahlian.
 * Skor = bobot syarat terpenuhi / total bobot x 100.
 */
class Kecocokan
{
    /**
     * @param  Collection|null  $klaim  klaim yang sudah dimuat dengan scope denganStatus(), supaya tidak query ulang per lowongan
     * @return array{skor: float, memenuhi_wajib: bool, rincian: array<int, object>}
     */
    public static function hitung(Lowongan $lowongan, PencariKerja $pencari, ?Collection $klaim = null): array
    {
        $urutan = array_keys(Format::LEVEL);

        $klaim ??= $pencari->klaimKeahlian()->denganStatus()->get();
        $klaim = $klaim->where('aktif', true)->keyBy('keahlian_id');

        $lowongan->loadMissing('syarat.keahlian');

        $total = 0.0;
        $terpenuhi = 0.0;
        $wajibGagal = 0;
        $rincian = [];

        foreach ($lowongan->syarat as $s) {
            $k = $klaim->get($s->keahlian_id);
            $levelCukup = $k && ($s->level_minimum === null
                || array_search($k->level_klaim, $urutan, true) >= array_search($s->level_minimum, $urutan, true));
            $verifCukup = $k && (! $s->wajib_terverifikasi || $k->terverifikasi);
            $ok = $levelCukup && $verifCukup;

            $total += $s->bobot;
            $terpenuhi += $ok ? $s->bobot : 0;
            $wajibGagal += (! $ok && $s->sifat === 'wajib') ? 1 : 0;

            $rincian[] = (object) [
                'syarat' => $s,
                'klaim' => $k,
                'ok' => $ok,
                'alasan' => match (true) {
                    $ok => 'Terpenuhi',
                    ! $k => 'Belum dicantumkan',
                    ! $levelCukup => 'Level di bawah minimum',
                    default => 'Butuh centang biru',
                },
            ];
        }

        return [
            'skor' => $total > 0 ? round($terpenuhi / $total * 100, 2) : 0.0,
            'memenuhi_wajib' => $wajibGagal === 0,
            'rincian' => $rincian,
        ];
    }
}
