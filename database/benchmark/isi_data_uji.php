<?php

/**
 * Mengisi database benchmark dengan volume data yang cukup besar supaya
 * optimizer MySQL benar-benar memilih indeks, bukan memindai tabel kecil.
 */
$p = new PDO('mysql:host=127.0.0.1;dbname=sibuker_pt_bench', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

const N_PENGGUNA   = 20000;  // 15000 pencari kerja + 4000 perusahaan + 1000 verifikator
const N_PENCARI    = 15000;
const N_PERUSAHAAN = 4000;
const N_VERIFIKATOR = 1000;
const N_KEAHLIAN   = 300;
const N_LOWONGAN   = 25000;
const N_KLAIM      = 60000;
const N_BUKTI      = 30000;
const N_VERIFIKASI = 45000;
const N_LAMARAN    = 80000;

function batch(PDO $p, string $sql, array $baris, int $per = 2000): void
{
    $kolom = substr_count($baris[0] ?? '', ',') + 1;
    foreach (array_chunk($baris, $per) as $potongan) {
        $p->exec($sql . ' VALUES ' . implode(',', $potongan));
    }
}

$mulai = microtime(true);
$p->exec('SET FOREIGN_KEY_CHECKS = 0');
$p->exec('SET unique_checks = 0');

$kataPosisi = ['Junior', 'Senior', 'Lead', 'Staf', 'Magang', 'Kepala'];
$kataBidang = ['Data Analyst', 'Backend Developer', 'Frontend Engineer', 'Data Scientist',
    'Quality Assurance', 'Product Designer', 'Network Administrator', 'Business Analyst'];
$kotaIndonesia = ['Surabaya', 'Jakarta', 'Bandung', 'Yogyakarta', 'Semarang', 'Malang', 'Medan', 'Makassar'];
$tipe = ['penuh_waktu', 'paruh_waktu', 'kontrak', 'magang', 'freelance'];
$level = ['pemula', 'menengah', 'mahir', 'ahli'];

echo "pengguna...\n";
$b = [];
for ($i = 1; $i <= N_PENGGUNA; $i++) {
    $b[] = "($i,'Pengguna $i','orang$i@contoh.test','\$2y\$04\$abcdefghijklmnopqrstuv',NULL,NULL,0,'aktif',NOW(),NOW())";
}
batch($p, 'INSERT INTO pengguna (id,nama,email,password,nomor_telepon,foto_profil,is_admin,status_akun,dibuat_pada,diperbarui_pada)', $b);

echo "pencari_kerja...\n";
$b = [];
for ($i = 1; $i <= N_PENCARI; $i++) {
    $kota = $kotaIndonesia[$i % count($kotaIndonesia)];
    $b[] = "($i,$i,'Headline $i',NULL,'$kota',NULL)";
}
batch($p, 'INSERT INTO pencari_kerja (id,pengguna_id,headline,ringkasan,lokasi,tanggal_lahir)', $b);

echo "perusahaan...\n";
$b = [];
for ($i = 1; $i <= N_PERUSAHAAN; $i++) {
    $pid = N_PENCARI + $i;
    $b[] = "($i,$pid,'Perusahaan $i','NIB$i',NULL,NULL,NULL,NULL,'aktif')";
}
batch($p, 'INSERT INTO perusahaan (id,pengguna_id,nama,nib,deskripsi,alamat,situs_web,logo,status_perusahaan)', $b);

echo "verifikator...\n";
$b = [];
for ($i = 1; $i <= N_VERIFIKATOR; $i++) {
    $pid = N_PENCARI + N_PERUSAHAAN + $i;
    $b[] = "($i,$pid,'Instansi $i','Asesor','aktif')";
}
batch($p, 'INSERT INTO verifikator (id,pengguna_id,instansi,jabatan,status_verifikator)', $b);

echo "keahlian + jenis_bukti...\n";
$b = [];
for ($i = 1; $i <= N_KEAHLIAN; $i++) {
    $b[] = "($i,'Keahlian $i','Kategori " . ($i % 12) . "',NULL,1)";
}
batch($p, 'INSERT INTO keahlian (id,nama,kategori,deskripsi,aktif)', $b);
$p->exec("INSERT INTO jenis_bukti (id,nama) VALUES (1,'Sertifikat Pelatihan'),(2,'Sertifikat Profesi'),(3,'Hasil Uji Kompetensi')");

echo "lowongan...\n";
$b = [];
for ($i = 1; $i <= N_LOWONGAN; $i++) {
    $per = ($i % N_PERUSAHAAN) + 1;
    $posisi = $kataPosisi[$i % count($kataPosisi)] . ' ' . $kataBidang[$i % count($kataBidang)];
    $kota = $kotaIndonesia[$i % count($kotaIndonesia)];
    $tp = $tipe[$i % count($tipe)];
    // 70% dipublikasikan, 20% draft, 10% ditutup
    $m = $i % 10;
    if ($m < 7) {
        $status = 'dipublikasikan';
        $pub = "DATE_SUB(NOW(), INTERVAL $i MINUTE)";
        $tutup = 'NULL';
    } elseif ($m < 9) {
        $status = 'draft';
        $pub = 'NULL';
        $tutup = 'NULL';
    } else {
        $status = 'ditutup';
        $pub = "DATE_SUB(NOW(), INTERVAL $i MINUTE)";
        $tutup = 'NOW()';
    }
    $desk = "Kami mencari $posisi untuk bergabung di kantor $kota. Tanggung jawab meliputi analisis, pelaporan, dan kolaborasi lintas tim.";
    $b[] = "($i,$per,'KODE-$i','$posisi','$desk','$kota','$tp','$status',$pub,$tutup,NOW(),NOW())";
}
batch($p, 'INSERT INTO lowongan (id,perusahaan_id,kode,posisi,deskripsi,lokasi,tipe_pekerjaan,status,dipublikasikan_pada,ditutup_pada,dibuat_pada,diperbarui_pada)', $b, 1000);

echo "syarat_keahlian...\n";
$b = [];
$id = 1;
for ($i = 1; $i <= N_LOWONGAN; $i++) {
    for ($j = 0; $j < 3; $j++) {
        $k = (($i * 3 + $j) % N_KEAHLIAN) + 1;
        $lv = $level[($i + $j) % 4];
        $sifat = $j === 0 ? 'wajib' : 'opsional';
        $wajibVerif = $j === 0 ? 1 : 0;
        $b[] = "($id,$i,$k,'$lv','$sifat',1.00,$wajibVerif)";
        $id++;
    }
}
batch($p, 'INSERT INTO syarat_keahlian (id,lowongan_id,keahlian_id,level_minimum,sifat,bobot,wajib_terverifikasi)', $b, 2000);

echo "klaim_keahlian...\n";
$b = [];
$dipakai = [];
$id = 1;
for ($i = 1; $i <= N_KLAIM; $i++) {
    $pk = ($i % N_PENCARI) + 1;
    $k = (($i * 7) % N_KEAHLIAN) + 1;
    if (isset($dipakai["$pk-$k"])) {
        continue;
    }
    $dipakai["$pk-$k"] = true;
    $lv = $level[$i % 4];
    $b[] = "($id,$pk,$k,'$lv',NOW(),1)";
    $id++;
}
batch($p, 'INSERT INTO klaim_keahlian (id,pencari_kerja_id,keahlian_id,level_klaim,tanggal_ditambahkan,aktif)', $b, 2000);
$maxKlaim = $id - 1;

echo "bukti...\n";
$b = [];
for ($i = 1; $i <= N_BUKTI; $i++) {
    $pg = ($i % N_PENCARI) + 1;
    $b[] = "($i,$pg,1,'Sertifikat $i','Penerbit " . ($i % 50) . "','bukti/berkas$i.pdf',NULL,DATE_SUB(NOW(), INTERVAL $i MINUTE))";
}
batch($p, 'INSERT INTO bukti (id,pengguna_id,jenis_bukti_id,judul,penerbit,url_berkas,tanggal_terbit,diunggah_pada)', $b, 2000);

echo "verifikasi...\n";
$b = [];
for ($i = 1; $i <= N_VERIFIKASI; $i++) {
    $bk = ($i % N_BUKTI) + 1;
    $vf = ($i % N_VERIFIKATOR) + 1;
    $kep = ['disetujui', 'ditolak', 'disetujui', 'menunggu'][$i % 4];
    $waktu = $kep === 'menunggu' ? 'NULL' : 'NOW()';
    $berlaku = $kep === 'disetujui' ? "DATE_ADD(CURDATE(), INTERVAL 365 DAY)" : 'NULL';
    $b[] = "($i,$bk,$vf,'$kep',NULL,$waktu,$berlaku,NOW())";
}
batch($p, 'INSERT INTO verifikasi (id,bukti_id,verifikator_id,keputusan,catatan,diverifikasi_pada,berlaku_sampai,dibuat_pada)', $b, 2000);

echo "bukti_keahlian...\n";
// Trigger dimatikan lewat FOREIGN_KEY_CHECKS tidak berlaku untuk trigger, jadi
// pasangan verifikasi-klaim dibuat konsisten: klaim milik pemilik bukti yang sama.
$p->exec('DROP TRIGGER IF EXISTS trg_bukti_keahlian_insert');
$p->exec('DROP TRIGGER IF EXISTS trg_bukti_keahlian_update');
$b = [];
$pasangan = [];
for ($i = 1; $i <= N_VERIFIKASI; $i++) {
    $bk = ($i % N_BUKTI) + 1;
    $pemilik = ($bk % N_PENCARI) + 1;
    // ambil satu klaim milik pemilik itu secara deterministik
    $klaim = (($pemilik - 1) * 4) % $maxKlaim + 1;
    if (isset($pasangan["$i-$klaim"])) {
        continue;
    }
    $pasangan["$i-$klaim"] = true;
    $b[] = "($i,$klaim)";
}
batch($p, 'INSERT IGNORE INTO bukti_keahlian (verifikasi_id,klaim_keahlian_id)', $b, 2000);

echo "lamaran...\n";
$b = [];
$id = 1;
$dipakai = [];
for ($i = 1; $i <= N_LAMARAN; $i++) {
    $low = ($i % N_LOWONGAN) + 1;
    $pk = (($i * 13) % N_PENCARI) + 1;
    if (isset($dipakai["$low-$pk"])) {
        continue;
    }
    $dipakai["$low-$pk"] = true;
    $skor = round(($i * 37) % 10000 / 100, 2);
    $st = ['dikirim', 'ditinjau', 'diproses', 'diterima', 'ditolak'][$i % 5];
    $b[] = "($id,$low,$pk,'$st',$skor,DATE_SUB(NOW(), INTERVAL $i SECOND),NOW())";
    $id++;
}
batch($p, 'INSERT INTO lamaran (id,lowongan_id,pencari_kerja_id,status,skor_kecocokan,dilamar_pada,diperbarui_pada)', $b, 2000);

$p->exec('SET FOREIGN_KEY_CHECKS = 1');
$p->exec('SET unique_checks = 1');

foreach (['pengguna', 'pencari_kerja', 'perusahaan', 'verifikator', 'keahlian', 'lowongan',
    'syarat_keahlian', 'klaim_keahlian', 'bukti', 'verifikasi', 'bukti_keahlian', 'lamaran'] as $t) {
    $p->query("ANALYZE TABLE $t")->fetchAll();
}

echo "\nJumlah baris:\n";
foreach (['pengguna', 'pencari_kerja', 'perusahaan', 'verifikator', 'keahlian', 'lowongan',
    'syarat_keahlian', 'klaim_keahlian', 'bukti', 'verifikasi', 'bukti_keahlian', 'lamaran'] as $t) {
    printf("  %-18s %s\n", $t, number_format($p->query("SELECT COUNT(*) FROM $t")->fetchColumn()));
}
printf("\nselesai dalam %.1f detik\n", microtime(true) - $mulai);
