<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/** Label enum dan format tampilan yang dipakai di banyak view. */
class Format
{
    public const LEVEL = ['pemula' => 'Pemula', 'menengah' => 'Menengah', 'mahir' => 'Mahir', 'ahli' => 'Ahli'];

    public const TIPE_PEKERJAAN = [
        'penuh_waktu' => 'Penuh waktu',
        'paruh_waktu' => 'Paruh waktu',
        'kontrak' => 'Kontrak',
        'magang' => 'Magang',
        'freelance' => 'Freelance',
    ];

    public const STATUS_LOWONGAN = ['draft', 'dipublikasikan', 'ditutup'];

    public const STATUS_LAMARAN = ['dikirim', 'ditinjau', 'diproses', 'diterima', 'ditolak', 'ditarik'];

    public const STATUS_TAHAP = ['menunggu', 'berlangsung', 'lulus', 'tidak_lulus'];

    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /** 15 Sep 2026, atau 15 Sep 2026 09:14 bila $jam = true. */
    public static function tanggal(CarbonInterface|string|null $nilai, bool $jam = false): string
    {
        if (! $nilai) {
            return '-';
        }
        $t = $nilai instanceof CarbonInterface ? $nilai : Carbon::parse($nilai);

        return $t->day . ' ' . self::BULAN[$t->month - 1] . ' ' . $t->year . ($jam ? ' ' . $t->format('H:i') : '');
    }

    /** "tidak_lulus" jadi "Tidak lulus". */
    public static function label(?string $nilai): string
    {
        return $nilai === null ? '-' : ucfirst(str_replace('_', ' ', $nilai));
    }

    public static function level(?string $nilai): string
    {
        return self::LEVEL[$nilai] ?? '-';
    }

    public static function tipe(?string $nilai): string
    {
        return self::TIPE_PEKERJAAN[$nilai] ?? '-';
    }

    /** Skor 83.33 jadi "83" untuk tampilan ringkas. */
    public static function skor(?float $nilai): string
    {
        return $nilai === null ? '-' : number_format($nilai, 0, ',', '.');
    }

    /** Dua huruf awal nama. Gelar dan badan usaha (Dr., PT, CV) dilewati. */
    public static function inisial(?string $nama): string
    {
        $kata = array_values(array_filter(
            preg_split('/\s+/', trim((string) $nama)),
            fn ($k) => $k !== '' && ! preg_match('/^(dr|ir|prof|pt|cv|ud|tbk)\.?,?$/i', $k)
        ));

        return strtoupper(mb_substr($kata[0] ?? '?', 0, 1) . mb_substr($kata[1] ?? '', 0, 1));
    }

    /** Indeks warna 1-6 yang stabil untuk satu id, dipakai tile logo dan avatar. */
    public static function rona(int|string|null $id): int
    {
        return (crc32((string) $id) % 6) + 1;
    }
}
