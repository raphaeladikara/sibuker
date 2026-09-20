<?php

/**
 * Uji kelayakan basis data SIBUKER-PT.
 *
 * Memeriksa integritas referensial, penegakan constraint, konsistensi antarkolom,
 * pemakaian indeks, dan bentuk normal. Dijalankan langsung terhadap database yang
 * sudah termigrasi:
 *
 *   php database/uji_kelayakan.php [nama_database]
 *
 * Seluruh pengujian yang menulis dibungkus transaksi dan dibatalkan kembali,
 * sehingga isi database tidak berubah.
 */

$db = $argv[1] ?? 'sibuker_pt';
$p = new PDO("mysql:host=127.0.0.1;dbname={$db}", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$lulus = 0;
$gagal = 0;
$bagian = '';

function judul(string $t): void
{
    echo "\n" . str_repeat('=', 78) . "\n$t\n" . str_repeat('=', 78) . "\n";
}

function periksa(string $nama, bool $ok, string $catatan = ''): void
{
    global $lulus, $gagal;
    $ok ? $lulus++ : $gagal++;
    printf("  [%s] %-58s %s\n", $ok ? 'OK  ' : 'GAGAL', $nama, $catatan);
}

/** Menjalankan tulis yang seharusnya ditolak database. */
function harusDitolak(PDO $p, string $nama, callable $aksi): void
{
    $p->beginTransaction();
    try {
        $aksi($p);
        periksa($nama, false, 'database menerima data yang seharusnya ditolak');
    } catch (PDOException $e) {
        $pesan = explode(':', $e->getMessage())[2] ?? '';
        periksa($nama, true, 'ditolak' . ($pesan ? ' -' . substr(trim($pesan), 0, 44) : ''));
    } finally {
        $p->rollBack();
    }
}

function hitung(PDO $p, string $sql): int
{
    return (int) $p->query($sql)->fetchColumn();
}

// ---------------------------------------------------------------------------
judul('1. STRUKTUR SKEMA');

$tabelWajib = ['pengguna', 'pencari_kerja', 'verifikator', 'perusahaan', 'keahlian',
    'jenis_bukti', 'kewenangan_verifikator', 'klaim_keahlian', 'bukti', 'verifikasi',
    'bukti_keahlian', 'lowongan', 'syarat_keahlian', 'lamaran', 'tahap_seleksi'];

$adaTabel = $p->query("SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='$db' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME <> 'migrations'")
    ->fetchAll(PDO::FETCH_COLUMN);

periksa('15 tabel utama ada', count(array_diff($tabelWajib, $adaTabel)) === 0, count($adaTabel) . ' tabel');

$view = $p->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='$db'")->fetchAll(PDO::FETCH_COLUMN);
periksa('view v_status_keahlian dan v_verifikasi_terbaru ada',
    count(array_diff(['v_status_keahlian', 'v_verifikasi_terbaru'], $view)) === 0);

$bukanInnoDb = $p->query("SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='$db' AND TABLE_TYPE='BASE TABLE' AND ENGINE <> 'InnoDB'")->fetchAll(PDO::FETCH_COLUMN);
periksa('semua tabel memakai InnoDB (mendukung FK dan transaksi)', $bukanInnoDb === []);

$bukanUtf8 = $p->query("SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='$db' AND TABLE_TYPE='BASE TABLE' AND TABLE_COLLATION <> 'utf8mb4_unicode_ci'")->fetchAll(PDO::FETCH_COLUMN);
periksa('semua tabel memakai utf8mb4_unicode_ci', $bukanUtf8 === []);

$tanpaPk = $p->query("SELECT t.TABLE_NAME FROM information_schema.TABLES t
    LEFT JOIN information_schema.TABLE_CONSTRAINTS c
      ON c.TABLE_SCHEMA=t.TABLE_SCHEMA AND c.TABLE_NAME=t.TABLE_NAME AND c.CONSTRAINT_TYPE='PRIMARY KEY'
    WHERE t.TABLE_SCHEMA='$db' AND t.TABLE_TYPE='BASE TABLE' AND c.CONSTRAINT_NAME IS NULL")->fetchAll(PDO::FETCH_COLUMN);
periksa('setiap tabel punya primary key', $tanpaPk === [], $tanpaPk ? implode(',', $tanpaPk) : '');

$jumlahFk = hitung($p, "SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='$db'");
periksa('foreign key terpasang', $jumlahFk >= 19, "$jumlahFk foreign key");

// Setiap FK wajib punya indeks penopang, kalau tidak setiap pengecekan FK memindai tabel.
$fkTanpaIndeks = [];
foreach ($p->query("SELECT k.TABLE_NAME, k.COLUMN_NAME, k.CONSTRAINT_NAME
    FROM information_schema.KEY_COLUMN_USAGE k
    JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
     AND r.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA
    WHERE k.TABLE_SCHEMA='$db'")->fetchAll(PDO::FETCH_ASSOC) as $fk) {
    $ada = hitung($p, "SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='{$fk['TABLE_NAME']}'
          AND COLUMN_NAME='{$fk['COLUMN_NAME']}' AND SEQ_IN_INDEX=1");
    if (! $ada) {
        $fkTanpaIndeks[] = $fk['CONSTRAINT_NAME'];
    }
}
periksa('setiap foreign key ditopang indeks', $fkTanpaIndeks === [], $fkTanpaIndeks ? implode(',', $fkTanpaIndeks) : '');

// ---------------------------------------------------------------------------
judul('2. INTEGRITAS REFERENSIAL (mencari baris yatim)');

$yatim = [
    'pencari_kerja -> pengguna' => 'SELECT COUNT(*) FROM pencari_kerja x LEFT JOIN pengguna y ON y.id=x.pengguna_id WHERE y.id IS NULL',
    'perusahaan -> pengguna' => 'SELECT COUNT(*) FROM perusahaan x LEFT JOIN pengguna y ON y.id=x.pengguna_id WHERE y.id IS NULL',
    'verifikator -> pengguna' => 'SELECT COUNT(*) FROM verifikator x LEFT JOIN pengguna y ON y.id=x.pengguna_id WHERE y.id IS NULL',
    'klaim_keahlian -> pencari_kerja' => 'SELECT COUNT(*) FROM klaim_keahlian x LEFT JOIN pencari_kerja y ON y.id=x.pencari_kerja_id WHERE y.id IS NULL',
    'klaim_keahlian -> keahlian' => 'SELECT COUNT(*) FROM klaim_keahlian x LEFT JOIN keahlian y ON y.id=x.keahlian_id WHERE y.id IS NULL',
    'bukti -> pengguna' => 'SELECT COUNT(*) FROM bukti x LEFT JOIN pengguna y ON y.id=x.pengguna_id WHERE y.id IS NULL',
    'verifikasi -> bukti' => 'SELECT COUNT(*) FROM verifikasi x LEFT JOIN bukti y ON y.id=x.bukti_id WHERE y.id IS NULL',
    'bukti_keahlian -> verifikasi' => 'SELECT COUNT(*) FROM bukti_keahlian x LEFT JOIN verifikasi y ON y.id=x.verifikasi_id WHERE y.id IS NULL',
    'lowongan -> perusahaan' => 'SELECT COUNT(*) FROM lowongan x LEFT JOIN perusahaan y ON y.id=x.perusahaan_id WHERE y.id IS NULL',
    'syarat_keahlian -> lowongan' => 'SELECT COUNT(*) FROM syarat_keahlian x LEFT JOIN lowongan y ON y.id=x.lowongan_id WHERE y.id IS NULL',
    'lamaran -> lowongan' => 'SELECT COUNT(*) FROM lamaran x LEFT JOIN lowongan y ON y.id=x.lowongan_id WHERE y.id IS NULL',
    'tahap_seleksi -> lamaran' => 'SELECT COUNT(*) FROM tahap_seleksi x LEFT JOIN lamaran y ON y.id=x.lamaran_id WHERE y.id IS NULL',
];
foreach ($yatim as $nama => $sql) {
    $n = hitung($p, $sql);
    periksa($nama, $n === 0, $n ? "$n baris yatim" : '');
}

// ---------------------------------------------------------------------------
judul('3. PENEGAKAN CONSTRAINT (percobaan tulis yang harus ditolak)');

$pengguna = hitung($p, 'SELECT MIN(id) FROM pengguna');
$lowongan = hitung($p, 'SELECT MIN(id) FROM lowongan');
$lamaran = hitung($p, 'SELECT MIN(id) FROM lamaran');
$bukti = hitung($p, 'SELECT MIN(id) FROM bukti');
$keahlianDipakai = hitung($p, 'SELECT keahlian_id FROM klaim_keahlian LIMIT 1');

harusDitolak($p, 'UNIQUE email pengguna', fn ($p) => $p->exec(
    "INSERT INTO pengguna (nama,email,password) SELECT 'Duplikat', email, 'x' FROM pengguna WHERE id=$pengguna"));

harusDitolak($p, 'UNIQUE satu profil pencari kerja per akun', fn ($p) => $p->exec(
    "INSERT INTO pencari_kerja (pengguna_id) SELECT pengguna_id FROM pencari_kerja LIMIT 1"));

harusDitolak($p, 'UNIQUE lamaran ganda pada lowongan yang sama', fn ($p) => $p->exec(
    "INSERT INTO lamaran (lowongan_id,pencari_kerja_id) SELECT lowongan_id,pencari_kerja_id FROM lamaran LIMIT 1"));

harusDitolak($p, 'UNIQUE klaim keahlian ganda', fn ($p) => $p->exec(
    "INSERT INTO klaim_keahlian (pencari_kerja_id,keahlian_id) SELECT pencari_kerja_id,keahlian_id FROM klaim_keahlian LIMIT 1"));

harusDitolak($p, 'FK lamaran ke lowongan yang tidak ada', fn ($p) => $p->exec(
    "INSERT INTO lamaran (lowongan_id,pencari_kerja_id) VALUES (999999,1)"));

harusDitolak($p, 'RESTRICT hapus keahlian yang masih dipakai', fn ($p) => $p->exec(
    "DELETE FROM keahlian WHERE id=$keahlianDipakai"));

harusDitolak($p, 'RESTRICT hapus jenis_bukti yang masih dipakai', fn ($p) => $p->exec(
    "DELETE FROM jenis_bukti WHERE id=(SELECT jenis_bukti_id FROM bukti LIMIT 1)"));

harusDitolak($p, 'CHECK skor kecocokan di luar 0-100', fn ($p) => $p->exec(
    "UPDATE lamaran SET skor_kecocokan=150 WHERE id=$lamaran"));

harusDitolak($p, 'CHECK bobot syarat negatif', fn ($p) => $p->exec(
    "UPDATE syarat_keahlian SET bobot=-1 WHERE lowongan_id=$lowongan"));

harusDitolak($p, 'CHECK urutan tahap seleksi nol', fn ($p) => $p->exec(
    "UPDATE tahap_seleksi SET urutan=0 WHERE id=(SELECT MIN(id) FROM (SELECT id FROM tahap_seleksi) t)"));

harusDitolak($p, 'CHECK keputusan final tanpa waktu pemeriksaan', fn ($p) => $p->exec(
    "UPDATE verifikasi SET diverifikasi_pada=NULL WHERE keputusan='disetujui'"));

harusDitolak($p, 'CHECK masa berlaku pada verifikasi yang ditolak', fn ($p) => $p->exec(
    "UPDATE verifikasi SET berlaku_sampai='2030-01-01' WHERE keputusan='ditolak'"));

harusDitolak($p, 'CHECK lowongan dipublikasikan tanpa tanggal publikasi', fn ($p) => $p->exec(
    "UPDATE lowongan SET dipublikasikan_pada=NULL WHERE status='dipublikasikan'"));

harusDitolak($p, 'CHECK tanggal tutup mendahului tanggal publikasi', fn ($p) => $p->exec(
    "UPDATE lowongan SET ditutup_pada='2000-01-01', dipublikasikan_pada='2020-01-01', status='ditutup' WHERE id=$lowongan"));

harusDitolak($p, 'TRIGGER verifikator memeriksa sertifikat sendiri', function ($p) {
    $v = $p->query("SELECT vf.id verifikator, b.id bukti FROM verifikator vf
        JOIN bukti b ON b.pengguna_id = vf.pengguna_id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $p->exec("INSERT INTO verifikasi (bukti_id,verifikator_id,keputusan,diverifikasi_pada)
        VALUES ({$v['bukti']},{$v['verifikator']},'disetujui',NOW())");
});

harusDitolak($p, 'TRIGGER bukti_keahlian lintas pemilik', function ($p) {
    $v = $p->query("SELECT v.id, b.pengguna_id FROM verifikasi v JOIN bukti b ON b.id=v.bukti_id WHERE v.keputusan='disetujui' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $k = $p->query("SELECT kk.id FROM klaim_keahlian kk JOIN pencari_kerja pk ON pk.id=kk.pencari_kerja_id WHERE pk.pengguna_id <> {$v['pengguna_id']} LIMIT 1")->fetchColumn();
    $p->exec("INSERT INTO bukti_keahlian (verifikasi_id,klaim_keahlian_id) VALUES ({$v['id']},$k)");
});

harusDitolak($p, 'TRIGGER bukti_keahlian dari verifikasi belum disetujui', function ($p) {
    $v = $p->query("SELECT v.id, b.pengguna_id FROM verifikasi v JOIN bukti b ON b.id=v.bukti_id WHERE v.keputusan<>'disetujui' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $k = $p->query("SELECT kk.id FROM klaim_keahlian kk JOIN pencari_kerja pk ON pk.id=kk.pencari_kerja_id WHERE pk.pengguna_id = {$v['pengguna_id']} LIMIT 1")->fetchColumn();
    $p->exec("INSERT INTO bukti_keahlian (verifikasi_id,klaim_keahlian_id) VALUES ({$v['id']},$k)");
});

// ---------------------------------------------------------------------------
judul('4. KONSISTENSI DATA YANG ADA');

$konsisten = [
    'tidak ada verifikasi final tanpa waktu pemeriksaan' => "SELECT COUNT(*) FROM verifikasi WHERE keputusan<>'menunggu' AND diverifikasi_pada IS NULL",
    'tidak ada masa berlaku pada keputusan bukan disetujui' => "SELECT COUNT(*) FROM verifikasi WHERE berlaku_sampai IS NOT NULL AND keputusan<>'disetujui'",
    'tidak ada bukti_keahlian lintas pemilik' => "SELECT COUNT(*) FROM bukti_keahlian bk JOIN verifikasi v ON v.id=bk.verifikasi_id JOIN bukti b ON b.id=v.bukti_id JOIN klaim_keahlian kk ON kk.id=bk.klaim_keahlian_id JOIN pencari_kerja pk ON pk.id=kk.pencari_kerja_id WHERE pk.pengguna_id<>b.pengguna_id",
    'tidak ada lowongan dipublikasikan tanpa tanggal' => "SELECT COUNT(*) FROM lowongan WHERE status='dipublikasikan' AND dipublikasikan_pada IS NULL",
    'tidak ada akun tanpa peran apa pun' => "SELECT COUNT(*) FROM pengguna p WHERE p.is_admin=0 AND NOT EXISTS(SELECT 1 FROM pencari_kerja WHERE pengguna_id=p.id) AND NOT EXISTS(SELECT 1 FROM perusahaan WHERE pengguna_id=p.id) AND NOT EXISTS(SELECT 1 FROM verifikator WHERE pengguna_id=p.id)",
    'tidak ada verifikator memeriksa sertifikatnya sendiri' => "SELECT COUNT(*) FROM verifikasi v JOIN bukti b ON b.id=v.bukti_id JOIN verifikator vf ON vf.id=v.verifikator_id WHERE vf.pengguna_id=b.pengguna_id",
    'tidak ada verifikasi di luar kewenangan verifikator' => "SELECT COUNT(*) FROM bukti_keahlian bk JOIN verifikasi v ON v.id=bk.verifikasi_id JOIN klaim_keahlian kk ON kk.id=bk.klaim_keahlian_id WHERE NOT EXISTS (SELECT 1 FROM kewenangan_verifikator kv WHERE kv.verifikator_id=v.verifikator_id AND kv.keahlian_id=kk.keahlian_id)",
];
foreach ($konsisten as $nama => $sql) {
    $n = hitung($p, $sql);
    periksa($nama, $n === 0, $n ? "$n baris menyimpang" : '');
}

// ---------------------------------------------------------------------------
judul('5. PERILAKU VIEW CENTANG BIRU');

$p->beginTransaction();
$klaimVerif = hitung($p, 'SELECT klaim_keahlian_id FROM v_status_keahlian WHERE terverifikasi=1 LIMIT 1');
periksa('ada klaim terverifikasi sebagai titik awal', $klaimVerif > 0, "klaim #$klaimVerif");

$buktiKlaim = hitung($p, "SELECT v.bukti_id FROM bukti_keahlian bk JOIN verifikasi v ON v.id=bk.verifikasi_id WHERE bk.klaim_keahlian_id=$klaimVerif LIMIT 1");

$p->exec("UPDATE bukti SET diunggah_pada=DATE_ADD(NOW(), INTERVAL 1 SECOND) WHERE id=$buktiKlaim");
periksa('mengganti berkas mencabut centang biru',
    hitung($p, "SELECT terverifikasi FROM v_status_keahlian WHERE klaim_keahlian_id=$klaimVerif") === 0);
periksa('sertifikat yang berkasnya diganti hilang dari v_verifikasi_terbaru',
    hitung($p, "SELECT COUNT(*) FROM v_verifikasi_terbaru WHERE bukti_id=$buktiKlaim") === 0);
$p->rollBack();

$p->beginTransaction();
$keahlianVerif = hitung($p, "SELECT keahlian_id FROM v_status_keahlian WHERE klaim_keahlian_id=$klaimVerif");
$p->exec("UPDATE keahlian SET aktif=0 WHERE id=$keahlianVerif");
periksa('keahlian yang dinonaktifkan hilang dari view',
    hitung($p, "SELECT COUNT(*) FROM v_status_keahlian WHERE klaim_keahlian_id=$klaimVerif") === 0);
$p->rollBack();

$p->beginTransaction();
$p->exec("UPDATE verifikasi SET berlaku_sampai=DATE_SUB(CURDATE(), INTERVAL 1 DAY) WHERE keputusan='disetujui'");
periksa('verifikasi kedaluwarsa tidak lagi memberi centang biru',
    hitung($p, 'SELECT COUNT(*) FROM v_status_keahlian WHERE terverifikasi=1') === 0);
$p->rollBack();

$p->beginTransaction();
$p->exec("UPDATE klaim_keahlian SET aktif=0 WHERE id=$klaimVerif");
periksa('klaim nonaktif tidak muncul di view',
    hitung($p, "SELECT COUNT(*) FROM v_status_keahlian WHERE klaim_keahlian_id=$klaimVerif") === 0);
$p->rollBack();

periksa('view tetap utuh setelah semua rollback',
    hitung($p, "SELECT terverifikasi FROM v_status_keahlian WHERE klaim_keahlian_id=$klaimVerif") === 1);

// ---------------------------------------------------------------------------
judul('6. PEMAKAIAN INDEKS PADA QUERY HALAMAN UTAMA');

$rencana = [
    'daftar lowongan publik' => ["SELECT id FROM lowongan WHERE status='dipublikasikan' ORDER BY dipublikasikan_pada DESC LIMIT 9", 'idx_lowongan_publik'],
    'pencarian kata kunci' => ["SELECT id FROM lowongan WHERE MATCH(posisi,deskripsi) AGAINST('+data*' IN BOOLEAN MODE)", 'ft_lowongan_pencarian'],
    'peringkat pelamar' => ["SELECT id FROM lamaran WHERE lowongan_id=$lowongan ORDER BY skor_kecocokan DESC, dilamar_pada", 'idx_lamaran_peringkat'],
    'lamaran milik pencari kerja' => ['SELECT id FROM lamaran WHERE pencari_kerja_id=1 ORDER BY dilamar_pada DESC', 'idx_lamaran_pencari_waktu'],
    'sertifikat milik pengguna' => ['SELECT id FROM bukti WHERE pengguna_id=2 ORDER BY diunggah_pada DESC', 'idx_bukti_pemilik'],
    'klaim aktif milik pencari kerja' => ['SELECT id FROM klaim_keahlian WHERE pencari_kerja_id=1 AND aktif=1', 'idx_klaim_pencari_aktif'],
    'verifikasi terbaru per bukti' => ["SELECT keputusan FROM v_verifikasi_terbaru WHERE bukti_id=$bukti", 'idx_verifikasi_bukti_terbaru'],
];

// Tabel demo sangat kecil, jadi optimizer boleh memilih pemindaian penuh karena memang
// lebih murah. Yang diuji di sini adalah indeksnya TERSEDIA bagi optimizer (kolom possible_keys),
// bukan bahwa indeks itu wajib dipakai pada data sekecil ini.
foreach ($rencana as $nama => [$sql, $indeks]) {
    $baris = $p->query('EXPLAIN ' . $sql)->fetchAll(PDO::FETCH_ASSOC);
    $kandidat = implode(',', array_filter(array_merge(
        array_column($baris, 'key'),
        array_column($baris, 'possible_keys')
    )));
    $dipakai = in_array($indeks, array_filter(array_column($baris, 'key')), true);
    periksa($nama, str_contains($kandidat, $indeks), $dipakai ? "dipakai: $indeks" : "tersedia: $indeks");
}

// ---------------------------------------------------------------------------
judul('7. BENTUK NORMAL');

// 1NF: tidak ada kolom yang menyimpan daftar bernilai jamak. Diperiksa dengan mencari
// nilai yang mengandung pemisah daftar pada kolom teks pendek.
$kolomTeks = $p->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='$db' AND DATA_TYPE='varchar' AND TABLE_NAME <> 'migrations'
      AND COLUMN_NAME NOT IN ('deskripsi','ringkasan','alamat','catatan','url_berkas','situs_web','foto_profil','logo','headline','nama','judul','posisi')")
    ->fetchAll(PDO::FETCH_ASSOC);
$melanggar1nf = [];
foreach ($kolomTeks as $k) {
    $n = hitung($p, "SELECT COUNT(*) FROM `{$k['TABLE_NAME']}` WHERE `{$k['COLUMN_NAME']}` REGEXP '^[^,;]+[,;] *[^,;]+$'");
    if ($n > 0) {
        $melanggar1nf[] = "{$k['TABLE_NAME']}.{$k['COLUMN_NAME']}";
    }
}
periksa('1NF: tidak ada kolom berisi daftar bernilai jamak', $melanggar1nf === [],
    $melanggar1nf ? implode(', ', $melanggar1nf) : count($kolomTeks) . ' kolom diperiksa');

// 2NF: hanya relevan pada tabel berkunci majemuk. Tabel asosiatif SIBUKER-PT harus
// tidak punya atribut bukan kunci yang bergantung pada sebagian kunci.
$kunciMajemuk = [];
foreach (['kewenangan_verifikator', 'bukti_keahlian'] as $t) {
    $kolom = $p->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$t'")->fetchColumn();
    $pk = $p->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$t' AND CONSTRAINT_NAME='PRIMARY'")->fetchColumn();
    // Atribut bukan kunci yang boleh ada hanyalah stempel waktu pemberian relasi itu sendiri,
    // yang bergantung pada SELURUH kunci, bukan sebagiannya.
    $kunciMajemuk[$t] = ($kolom - $pk) <= 1;
}
periksa('2NF: tabel asosiatif tanpa ketergantungan parsial',
    ! in_array(false, $kunciMajemuk, true), implode(', ', array_keys($kunciMajemuk)));

// 3NF: atribut turunan tidak disimpan sebagai kolom. Status centang biru adalah
// contoh utamanya - nilainya dihitung view, bukan disimpan.
$kolomStatus = hitung($p, "SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='$db' AND TABLE_NAME IN ('klaim_keahlian','pencari_kerja')
      AND COLUMN_NAME IN ('terverifikasi','is_verified','status_verifikasi','centang_biru')");
periksa('3NF: status terverifikasi tidak disimpan sebagai kolom', $kolomStatus === 0);

$kolomJumlah = hitung($p, "SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='$db' AND COLUMN_NAME REGEXP 'jumlah_|total_|count_'");
periksa('3NF: tidak ada kolom agregat yang disimpan', $kolomJumlah === 0);

// Denormalisasi yang disengaja harus tetap tercatat.
$adaSkor = hitung($p, "SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='lamaran' AND COLUMN_NAME='skor_kecocokan'");
periksa('denormalisasi terkendali: lamaran.skor_kecocokan ada dan disengaja', $adaSkor === 1,
    'potret skor saat melamar');

// ---------------------------------------------------------------------------
judul('RINGKASAN');
printf("  lulus : %d\n  gagal : %d\n\n  %s\n", $lulus, $gagal,
    $gagal === 0 ? 'Seluruh pengujian kelayakan terpenuhi.' : 'Ada pengujian yang gagal, periksa daftar di atas.');

exit($gagal === 0 ? 0 : 1);
