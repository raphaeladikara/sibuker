<?php

/**
 * Membangkitkan sumber diagram Mermaid untuk ERD SIBUKER-PT langsung dari
 * information_schema, sehingga diagram dijamin sama dengan basis data yang berjalan
 * dan tidak bisa basi ketika skema berubah.
 *
 *   php database/erd/buat_erd.php [nama_database]
 *
 * Keluaran ditulis ke database/erd/*.mmd
 */

$db = $argv[1] ?? 'sibuker_pt';
$p = new PDO("mysql:host=127.0.0.1;dbname={$db}", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$keluaran = __DIR__ . "/../sumber-erd";

// ---------------------------------------------------------------------------
// Baca struktur dari information_schema
// ---------------------------------------------------------------------------

$tabel = $p->query("SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='$db' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME <> 'migrations'
    ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);

$kolom = [];
foreach ($p->query("SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, ORDINAL_POSITION
    FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME <> 'migrations'
    ORDER BY TABLE_NAME, ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC) as $k) {
    $kolom[$k['TABLE_NAME']][] = $k;
}

// Foreign key beserta aturan ON DELETE
$fk = $p->query("SELECT k.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME,
        k.CONSTRAINT_NAME, r.DELETE_RULE
    FROM information_schema.KEY_COLUMN_USAGE k
    JOIN information_schema.REFERENTIAL_CONSTRAINTS r
      ON r.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND r.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA
    WHERE k.TABLE_SCHEMA='$db' ORDER BY k.TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC);

// Kolom yang UNIQUE sendirian dihitung sebagai unique key pada diagram
$unik = [];
foreach ($p->query("SELECT s.TABLE_NAME, s.COLUMN_NAME FROM information_schema.STATISTICS s
    WHERE s.TABLE_SCHEMA='$db' AND s.NON_UNIQUE=0 AND s.INDEX_NAME <> 'PRIMARY'
      AND (SELECT COUNT(*) FROM information_schema.STATISTICS t
           WHERE t.TABLE_SCHEMA=s.TABLE_SCHEMA AND t.TABLE_NAME=s.TABLE_NAME
             AND t.INDEX_NAME=s.INDEX_NAME) = 1")->fetchAll(PDO::FETCH_ASSOC) as $u) {
    $unik[$u['TABLE_NAME']][$u['COLUMN_NAME']] = true;
}

$kolomFk = [];
foreach ($fk as $f) {
    $kolomFk[$f['TABLE_NAME']][$f['COLUMN_NAME']] = true;
}

// ---------------------------------------------------------------------------
// Pengelompokan modul dan nama relasi, sesuai pembagian di laporan
// ---------------------------------------------------------------------------

$modul = [
    'pengguna' => ['judul' => 'Modul Pengguna dan Profil', 'tabel' => ['pengguna', 'pencari_kerja', 'verifikator', 'perusahaan']],
    'keahlian' => ['judul' => 'Modul Keahlian dan Verifikasi', 'tabel' => ['keahlian', 'jenis_bukti', 'kewenangan_verifikator', 'klaim_keahlian', 'bukti', 'verifikasi', 'bukti_keahlian']],
    'lowongan' => ['judul' => 'Modul Lowongan dan Persyaratan', 'tabel' => ['perusahaan', 'lowongan', 'syarat_keahlian', 'keahlian']],
    'lamaran' => ['judul' => 'Modul Lamaran dan Seleksi', 'tabel' => ['lowongan', 'pencari_kerja', 'lamaran', 'tahap_seleksi']],
];

// Nama relasi dibaca dari nama constraint agar tetap sinkron bila FK berubah.
$namaRelasi = [
    'fk_pencari_kerja_pengguna' => 'memiliki profil',
    'fk_verifikator_pengguna' => 'memiliki profil',
    'fk_perusahaan_pengguna' => 'memiliki profil',
    'fk_kewenangan_verifikator' => 'diberi kewenangan',
    'fk_kewenangan_keahlian' => 'mencakup',
    'fk_klaim_pencari_kerja' => 'mencantumkan',
    'fk_klaim_keahlian' => 'mengacu',
    'fk_bukti_pengguna' => 'mengunggah',
    'fk_bukti_jenis' => 'berjenis',
    'fk_verifikasi_bukti' => 'diperiksa lewat',
    'fk_verifikasi_verifikator' => 'memutuskan',
    'fk_bukti_keahlian_verifikasi' => 'membuktikan',
    'fk_bukti_keahlian_klaim' => 'dibuktikan oleh',
    'fk_lowongan_perusahaan' => 'membuka',
    'fk_syarat_lowongan' => 'menetapkan',
    'fk_syarat_keahlian' => 'mensyaratkan',
    'fk_lamaran_lowongan' => 'menerima',
    'fk_lamaran_pencari_kerja' => 'mengirim',
    'fk_tahap_lamaran' => 'melalui',
];

/** Relasi 1:1 bila kolom FK-nya juga UNIQUE; selain itu 1:N. */
function kardinalitas(array $f, array $unik): string
{
    $satuSatu = isset($unik[$f['TABLE_NAME']][$f['COLUMN_NAME']]);

    return $satuSatu ? '||--o|' : '||--o{';
}

/** Tipe data diringkas agar diagram tetap terbaca. */
function tipeRingkas(array $k): string
{
    $t = $k['DATA_TYPE'];
    if ($t === 'enum') {
        return 'enum';
    }
    if (in_array($t, ['varchar', 'char'], true)) {
        preg_match('/\((\d+)\)/', $k['COLUMN_TYPE'], $m);

        return 'varchar_' . ($m[1] ?? '');
    }
    if ($t === 'decimal') {
        return 'decimal';
    }
    if ($t === 'tinyint') {
        return 'boolean';
    }

    return $t;
}

function penandaKunci(array $k, array $kolomFk, array $unik): string
{
    $tanda = [];
    if ($k['COLUMN_KEY'] === 'PRI') {
        $tanda[] = 'PK';
    }
    if (isset($kolomFk[$k['TABLE_NAME']][$k['COLUMN_NAME']])) {
        $tanda[] = 'FK';
    }
    if (isset($unik[$k['TABLE_NAME']][$k['COLUMN_NAME']]) && $k['COLUMN_KEY'] !== 'PRI') {
        $tanda[] = 'UK';
    }

    return $tanda ? ' ' . implode(',', $tanda) : '';
}

// ---------------------------------------------------------------------------
// ERD FISIK - satu berkas per modul supaya tetap terbaca saat dicetak
// ---------------------------------------------------------------------------

function blokEntitas(string $t, array $kolom, array $kolomFk, array $unik): string
{
    $baris = "    {$t} {\n";
    foreach ($kolom[$t] as $k) {
        $nama = $k['COLUMN_NAME'];
        $tipe = tipeRingkas($k);
        $baris .= "        {$tipe} {$nama}" . penandaKunci($k, $kolomFk, $unik) . "\n";
    }

    return $baris . "    }\n";
}

foreach ($modul as $kunci => $m) {
    $mmd = "erDiagram\n";
    foreach ($m['tabel'] as $t) {
        $mmd .= blokEntitas($t, $kolom, $kolomFk, $unik);
    }
    foreach ($fk as $f) {
        if (! in_array($f['TABLE_NAME'], $m['tabel'], true) || ! in_array($f['REFERENCED_TABLE_NAME'], $m['tabel'], true)) {
            continue;
        }
        $label = $namaRelasi[$f['CONSTRAINT_NAME']] ?? 'terhubung';
        $mmd .= sprintf("    %s %s %s : \"%s\"\n",
            $f['REFERENCED_TABLE_NAME'], kardinalitas($f, $unik), $f['TABLE_NAME'], $label);
    }
    file_put_contents("$keluaran/fisik-$kunci.mmd", $mmd);
    echo "fisik-$kunci.mmd (" . count($m['tabel']) . " entitas)\n";
}

// ERD fisik utuh
$mmd = "erDiagram\n";
foreach ($tabel as $t) {
    $mmd .= blokEntitas($t, $kolom, $kolomFk, $unik);
}
foreach ($fk as $f) {
    $label = $namaRelasi[$f['CONSTRAINT_NAME']] ?? 'terhubung';
    $mmd .= sprintf("    %s %s %s : \"%s\"\n",
        $f['REFERENCED_TABLE_NAME'], kardinalitas($f, $unik), $f['TABLE_NAME'], $label);
}
file_put_contents("$keluaran/fisik-lengkap.mmd", $mmd);
echo 'fisik-lengkap.mmd (' . count($tabel) . " entitas, " . count($fk) . " relasi)\n";

// ---------------------------------------------------------------------------
// ERD KONSEPTUAL
//
// Tingkat konseptual tidak menampilkan kolom, tipe data, maupun kunci.
//
// Aturan yang dipakai untuk memutuskan apakah sebuah tabel penghubung tampil sebagai
// entitas tersendiri: tabel penghubung MURNI, yaitu yang tidak punya atribut apa pun di
// luar pasangan kuncinya, diruntuhkan menjadi relasi M:N langsung. Tabel penghubung yang
// MEMBAWA atribut tetap tampil sebagai entitas asosiatif, karena atribut itu adalah
// fakta yang hanya ada pada hubungannya, bukan pada salah satu entitas induknya.
//
//   kewenangan_verifikator -> diruntuhkan (hanya sepasang kunci + stempel waktu)
//   bukti_keahlian         -> diruntuhkan (hanya sepasang kunci)
//   klaim_keahlian         -> entitas (punya level_klaim, aktif)
//   syarat_keahlian        -> entitas (punya level_minimum, sifat, bobot, wajib_terverifikasi)
//   lamaran                -> entitas (punya status, skor_kecocokan, tanggal)
// ---------------------------------------------------------------------------

$konseptual = <<<'MMD'
erDiagram
    PENGGUNA ||--o| PENCARI_KERJA : "memiliki profil"
    PENGGUNA ||--o| PERUSAHAAN : "memiliki profil"
    PENGGUNA ||--o| VERIFIKATOR : "memiliki profil"

    PENCARI_KERJA ||--o{ KLAIM_KEAHLIAN : "mencantumkan"
    KEAHLIAN ||--o{ KLAIM_KEAHLIAN : "diklaim pada"

    PENGGUNA ||--o{ BUKTI : "mengunggah"
    JENIS_BUKTI ||--o{ BUKTI : "mengelompokkan"
    BUKTI ||--o{ VERIFIKASI : "diperiksa lewat"
    VERIFIKATOR ||--o{ VERIFIKASI : "memutuskan"
    VERIFIKASI }o--o{ KLAIM_KEAHLIAN : "membuktikan"
    VERIFIKATOR }o--o{ KEAHLIAN : "berwenang atas"

    PERUSAHAAN ||--o{ LOWONGAN : "membuka"
    LOWONGAN ||--o{ SYARAT_KEAHLIAN : "menetapkan"
    KEAHLIAN ||--o{ SYARAT_KEAHLIAN : "dipakai pada"

    LOWONGAN ||--o{ LAMARAN : "menerima"
    PENCARI_KERJA ||--o{ LAMARAN : "mengirim"
    LAMARAN ||--o{ TAHAP_SELEKSI : "memiliki"

MMD;
file_put_contents("$keluaran/konseptual.mmd", $konseptual);
echo "konseptual.mmd\n";

echo "\nSelesai. Sumber diagram ada di database/erd/\n";
