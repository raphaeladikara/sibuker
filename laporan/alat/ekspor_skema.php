<?php

/**
 * Mengekspor struktur basis data ke JSON untuk dipakai pembangun dokumen laporan.
 * Kamus data, daftar foreign key, indeks, dan constraint pada laporan dibangkitkan
 * dari berkas ini, sehingga isi laporan tidak mungkin berbeda dengan basis data.
 *
 *   php database/erd/ekspor_skema.php [nama_database]
 */

$db = $argv[1] ?? 'sibuker_pt';
$p = new PDO("mysql:host=127.0.0.1;dbname={$db}", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

/** Keterangan kolom ditulis manual karena tidak ada di information_schema. */
$keterangan = [
    'pengguna.id' => 'Kunci utama akun.',
    'pengguna.nama' => 'Nama lengkap pemilik akun.',
    'pengguna.email' => 'Alamat surel, dipakai untuk masuk. Unik.',
    'pengguna.password' => 'Kata sandi yang sudah di-hash bcrypt.',
    'pengguna.nomor_telepon' => 'Nomor kontak, opsional.',
    'pengguna.foto_profil' => 'Lokasi berkas foto pada penyimpanan publik.',
    'pengguna.is_admin' => 'Penanda peran administrator.',
    'pengguna.status_akun' => 'aktif, nonaktif, atau ditangguhkan. Selain aktif tidak dapat masuk.',
    'pengguna.dibuat_pada' => 'Waktu akun dibuat.',
    'pengguna.diperbarui_pada' => 'Waktu perubahan terakhir, diisi otomatis.',

    'pencari_kerja.id' => 'Kunci utama profil pencari kerja.',
    'pencari_kerja.pengguna_id' => 'Akun pemilik profil. Unik, sehingga satu akun hanya punya satu profil.',
    'pencari_kerja.headline' => 'Ringkasan satu baris pada profil.',
    'pencari_kerja.ringkasan' => 'Deskripsi diri.',
    'pencari_kerja.lokasi' => 'Kota domisili.',
    'pencari_kerja.tanggal_lahir' => 'Tanggal lahir, opsional.',

    'verifikator.id' => 'Kunci utama profil verifikator.',
    'verifikator.pengguna_id' => 'Akun pemilik profil. Unik.',
    'verifikator.instansi' => 'Lembaga tempat verifikator bernaung.',
    'verifikator.jabatan' => 'Jabatan pada lembaga tersebut.',
    'verifikator.status_verifikator' => 'aktif atau nonaktif. Nonaktif tidak menerima antrean.',

    'perusahaan.id' => 'Kunci utama profil perusahaan.',
    'perusahaan.pengguna_id' => 'Akun pemilik profil. Unik.',
    'perusahaan.nama' => 'Nama perusahaan.',
    'perusahaan.nib' => 'Nomor Induk Berusaha. Unik bila diisi.',
    'perusahaan.deskripsi' => 'Profil singkat perusahaan.',
    'perusahaan.alamat' => 'Alamat kantor.',
    'perusahaan.situs_web' => 'Tautan situs resmi.',
    'perusahaan.logo' => 'Lokasi berkas logo.',
    'perusahaan.status_perusahaan' => 'aktif atau nonaktif. Nonaktif tidak dapat mempublikasikan lowongan.',

    'keahlian.id' => 'Kunci utama data acuan keahlian.',
    'keahlian.nama' => 'Nama keahlian. Unik agar tidak ada duplikat penulisan.',
    'keahlian.kategori' => 'Pengelompokan keahlian.',
    'keahlian.deskripsi' => 'Penjelasan keahlian.',
    'keahlian.aktif' => 'Keahlian nonaktif tidak dapat dipilih lagi, tetapi klaim lama tetap tersimpan.',

    'jenis_bukti.id' => 'Kunci utama data acuan jenis bukti.',
    'jenis_bukti.nama' => 'Nama jenis bukti, misalnya Sertifikat Profesi. Unik.',

    'kewenangan_verifikator.verifikator_id' => 'Bagian kunci utama gabungan. Verifikator pemilik kewenangan.',
    'kewenangan_verifikator.keahlian_id' => 'Bagian kunci utama gabungan. Keahlian yang boleh diperiksa.',
    'kewenangan_verifikator.diberikan_pada' => 'Waktu kewenangan diberikan administrator.',

    'klaim_keahlian.id' => 'Kunci utama klaim keahlian.',
    'klaim_keahlian.pencari_kerja_id' => 'Pemilik klaim.',
    'klaim_keahlian.keahlian_id' => 'Keahlian yang diklaim.',
    'klaim_keahlian.level_klaim' => 'pemula, menengah, mahir, atau ahli. Diisi sendiri oleh pencari kerja.',
    'klaim_keahlian.tanggal_ditambahkan' => 'Waktu klaim dicantumkan.',
    'klaim_keahlian.aktif' => 'Klaim nonaktif disembunyikan dari profil tanpa dihapus.',

    'bukti.id' => 'Kunci utama sertifikat.',
    'bukti.pengguna_id' => 'Akun yang mengunggah sertifikat.',
    'bukti.jenis_bukti_id' => 'Jenis bukti.',
    'bukti.judul' => 'Judul sertifikat.',
    'bukti.penerbit' => 'Lembaga penerbit sertifikat.',
    'bukti.url_berkas' => 'Lokasi berkas PDF pada Laravel File Storage, bukan isi berkasnya.',
    'bukti.tanggal_terbit' => 'Tanggal sertifikat diterbitkan.',
    'bukti.diunggah_pada' => 'Waktu berkas terakhir diunggah. Diperbarui saat berkas diganti, dan dipakai view untuk menggugurkan hasil pemeriksaan lama.',

    'verifikasi.id' => 'Kunci utama satu kali pemeriksaan.',
    'verifikasi.bukti_id' => 'Sertifikat yang diperiksa.',
    'verifikasi.verifikator_id' => 'Verifikator yang memutuskan.',
    'verifikasi.keputusan' => 'menunggu, disetujui, atau ditolak.',
    'verifikasi.catatan' => 'Alasan keputusan. Wajib diisi bila ditolak.',
    'verifikasi.diverifikasi_pada' => 'Waktu keputusan final diambil. Kosong selama keputusan masih menunggu.',
    'verifikasi.berlaku_sampai' => 'Batas akhir masa berlaku centang biru. Kosong berarti tanpa batas.',
    'verifikasi.dibuat_pada' => 'Waktu baris pemeriksaan dibuat.',

    'bukti_keahlian.verifikasi_id' => 'Bagian kunci utama gabungan. Hasil pemeriksaan yang membuktikan.',
    'bukti_keahlian.klaim_keahlian_id' => 'Bagian kunci utama gabungan. Klaim keahlian yang dibuktikan.',

    'lowongan.id' => 'Kunci utama lowongan.',
    'lowongan.perusahaan_id' => 'Perusahaan pemilik lowongan.',
    'lowongan.kode' => 'Kode lowongan. Unik.',
    'lowongan.posisi' => 'Nama posisi yang dibuka.',
    'lowongan.deskripsi' => 'Uraian pekerjaan.',
    'lowongan.lokasi' => 'Lokasi penempatan.',
    'lowongan.tipe_pekerjaan' => 'penuh_waktu, paruh_waktu, kontrak, magang, atau freelance.',
    'lowongan.status' => 'draft, dipublikasikan, atau ditutup. Hanya yang dipublikasikan tampil di halaman publik.',
    'lowongan.dipublikasikan_pada' => 'Waktu publikasi. Wajib terisi bila status dipublikasikan.',
    'lowongan.ditutup_pada' => 'Waktu penutupan. Wajib terisi bila status ditutup.',
    'lowongan.dibuat_pada' => 'Waktu lowongan dibuat.',
    'lowongan.diperbarui_pada' => 'Waktu perubahan terakhir.',

    'syarat_keahlian.id' => 'Kunci utama persyaratan.',
    'syarat_keahlian.lowongan_id' => 'Lowongan yang mensyaratkan.',
    'syarat_keahlian.keahlian_id' => 'Keahlian yang disyaratkan.',
    'syarat_keahlian.level_minimum' => 'Level terendah yang diterima. Kosong berarti level apa pun.',
    'syarat_keahlian.sifat' => 'wajib atau opsional.',
    'syarat_keahlian.bobot' => 'Bobot syarat dalam perhitungan skor kecocokan.',
    'syarat_keahlian.wajib_terverifikasi' => 'Bila benar, klaim hanya dianggap memenuhi bila bercentang biru.',

    'lamaran.id' => 'Kunci utama lamaran.',
    'lamaran.lowongan_id' => 'Lowongan yang dilamar.',
    'lamaran.pencari_kerja_id' => 'Pelamar.',
    'lamaran.status' => 'dikirim, ditinjau, diproses, diterima, ditolak, atau ditarik.',
    'lamaran.skor_kecocokan' => 'Potret skor kecocokan saat melamar, dalam persen.',
    'lamaran.dilamar_pada' => 'Waktu lamaran dikirim.',
    'lamaran.diperbarui_pada' => 'Waktu perubahan status terakhir.',

    'tahap_seleksi.id' => 'Kunci utama tahap seleksi.',
    'tahap_seleksi.lamaran_id' => 'Lamaran yang dijalani.',
    'tahap_seleksi.nama_tahap' => 'Nama tahap, misalnya Wawancara HR.',
    'tahap_seleksi.urutan' => 'Urutan tahap, dimulai dari 1.',
    'tahap_seleksi.status' => 'menunggu, berlangsung, lulus, atau tidak_lulus.',
    'tahap_seleksi.jadwal' => 'Waktu pelaksanaan tahap.',
    'tahap_seleksi.catatan' => 'Catatan hasil tahap.',
    'tahap_seleksi.diperbarui_pada' => 'Waktu perubahan terakhir.',
];

$hasil = ['tabel' => [], 'fk' => [], 'indeks' => [], 'check' => [], 'view' => [], 'trigger' => []];

$daftarTabel = $p->query("SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='$db' AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME <> 'migrations'
    ORDER BY TABLE_NAME")->fetchAll(PDO::FETCH_COLUMN);

foreach ($daftarTabel as $t) {
    $kolom = [];
    foreach ($p->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
        FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='$t'
        ORDER BY ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC) as $k) {
        $bawaan = $k['COLUMN_DEFAULT'];
        if ($bawaan === null) {
            $bawaan = $k['IS_NULLABLE'] === 'YES' ? 'NULL' : '-';
        }
        if (str_contains((string) $k['EXTRA'], 'auto_increment')) {
            $bawaan = 'auto increment';
        }
        $kolom[] = [
            'nama' => $k['COLUMN_NAME'],
            'tipe' => $k['COLUMN_TYPE'],
            'null' => $k['IS_NULLABLE'] === 'YES' ? 'Ya' : 'Tidak',
            'bawaan' => $bawaan,
            'kunci' => $k['COLUMN_KEY'],
            'keterangan' => $keterangan["$t.{$k['COLUMN_NAME']}"] ?? '',
        ];
    }
    $hasil['tabel'][$t] = ['nama' => $t, 'kolom' => $kolom,
        'jumlah_baris' => (int) $p->query("SELECT COUNT(*) FROM `$t`")->fetchColumn()];
}

foreach ($p->query("SELECT k.CONSTRAINT_NAME, k.TABLE_NAME, k.COLUMN_NAME,
        k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, r.UPDATE_RULE, r.DELETE_RULE
    FROM information_schema.KEY_COLUMN_USAGE k
    JOIN information_schema.REFERENTIAL_CONSTRAINTS r
      ON r.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND r.CONSTRAINT_SCHEMA = k.TABLE_SCHEMA
    WHERE k.TABLE_SCHEMA='$db' ORDER BY k.TABLE_NAME, k.CONSTRAINT_NAME")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    $hasil['fk'][] = $f;
}

foreach ($p->query("SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, INDEX_TYPE,
        GROUP_CONCAT(CONCAT(COLUMN_NAME, IF(COLLATION='D',' DESC','')) ORDER BY SEQ_IN_INDEX) AS kolom
    FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='$db' AND TABLE_NAME <> 'migrations'
    GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE, INDEX_TYPE
    ORDER BY TABLE_NAME, INDEX_NAME")->fetchAll(PDO::FETCH_ASSOC) as $i) {
    $hasil['indeks'][] = $i;
}

foreach ($p->query("SELECT c.CONSTRAINT_NAME, t.TABLE_NAME, c.CHECK_CLAUSE
    FROM information_schema.CHECK_CONSTRAINTS c
    JOIN information_schema.TABLE_CONSTRAINTS t ON t.CONSTRAINT_NAME = c.CONSTRAINT_NAME
     AND t.CONSTRAINT_SCHEMA = c.CONSTRAINT_SCHEMA
    WHERE c.CONSTRAINT_SCHEMA='$db' ORDER BY t.TABLE_NAME")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $hasil['check'][] = $c;
}

foreach ($p->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA='$db'")->fetchAll(PDO::FETCH_COLUMN) as $v) {
    $hasil['view'][] = $v;
}

foreach ($p->query("SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE, ACTION_TIMING
    FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA='$db' ORDER BY TRIGGER_NAME")->fetchAll(PDO::FETCH_ASSOC) as $tr) {
    $hasil['trigger'][] = $tr;
}

$berkas = __DIR__ . '/../skema.json';
file_put_contents($berkas, json_encode($hasil, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

printf("%s\n  %d tabel, %d kolom, %d foreign key, %d indeks, %d check, %d view, %d trigger\n",
    $berkas, count($hasil['tabel']),
    array_sum(array_map(fn ($t) => count($t['kolom']), $hasil['tabel'])),
    count($hasil['fk']), count($hasil['indeks']), count($hasil['check']),
    count($hasil['view']), count($hasil['trigger']));

// Peringatkan bila ada kolom yang belum punya keterangan, supaya kamus data tidak bolong.
$kosong = [];
foreach ($hasil['tabel'] as $t => $d) {
    foreach ($d['kolom'] as $k) {
        if ($k['keterangan'] === '') {
            $kosong[] = "$t.{$k['nama']}";
        }
    }
}
echo $kosong ? "\nPERINGATAN, keterangan kosong:\n  " . implode("\n  ", $kosong) . "\n" : "\nSemua kolom punya keterangan.\n";
