<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaikan integritas dan efisiensi skema SIBUKER-PT.
 *
 * Migration ini menutup tiga celah integritas yang lolos dari skema awal
 * (SIBUKER_PT.sql), memindahkan aturan bisnis yang sebelumnya hanya dijaga
 * aplikasi ke dalam basis data, dan menambah indeks yang benar-benar dipakai
 * oleh query halaman daftar.
 *
 * Nama tabel dan kolom tidak diubah sama sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        // DDL di MySQL melakukan commit implisit, jadi migration ini tidak bisa dibungkus
        // transaksi. Supaya aman dijalankan ulang setelah gagal di tengah, setiap objek
        // yang dibuat di bawah dihapus lebih dulu bila sudah ada.
        $this->bersihkan();

        $this->viewVerifikasiTerbaru();
        $this->viewStatusKeahlian();
        $this->checkConstraint();
        $this->triggerBuktiKeahlian();
        $this->triggerVerifikasi();
        $this->indeks();
    }

    public function down(): void
    {
        // bersihkan() sudah menghapus trigger, CHECK, dan indeks tambahan, sekaligus
        // memulihkan indeks penopang foreign key sebelum yang lama dilepas.
        $this->bersihkan();

        // Kembalikan idx_verifikasi_bukti_terbaru ke bentuk sempit (bukti_id, id).
        // Sama seperti di up(), pengganti dibuat lebih dulu karena indeks ini menopang
        // fk_verifikasi_bukti.
        DB::statement('CREATE INDEX idx_verifikasi_bukti_sempit ON verifikasi (bukti_id, id)');
        DB::statement('DROP INDEX idx_verifikasi_bukti_terbaru ON verifikasi');
        DB::statement('ALTER TABLE verifikasi RENAME INDEX idx_verifikasi_bukti_sempit TO idx_verifikasi_bukti_terbaru');

        // Kembalikan view ke bentuk semula (SIBUKER_PT.sql).
        DB::statement('DROP VIEW IF EXISTS v_status_keahlian');
        DB::statement('DROP VIEW IF EXISTS v_verifikasi_terbaru');
        DB::statement(<<<'SQL'
            CREATE VIEW v_status_keahlian AS
            SELECT
                kk.id AS klaim_keahlian_id,
                kk.pencari_kerja_id,
                kk.keahlian_id,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM bukti_keahlian bk
                        JOIN verifikasi v ON v.id = bk.verifikasi_id
                        WHERE bk.klaim_keahlian_id = kk.id
                          AND v.id = (SELECT MAX(v2.id) FROM verifikasi v2 WHERE v2.bukti_id = v.bukti_id)
                          AND v.keputusan = 'disetujui'
                          AND (v.berlaku_sampai IS NULL OR v.berlaku_sampai >= CURRENT_DATE)
                    ) THEN TRUE
                    ELSE FALSE
                END AS terverifikasi
            FROM klaim_keahlian kk
            WHERE kk.aktif = TRUE
            SQL);
    }

    /** Hapus objek bentukan migration ini bila sudah ada, agar up() aman diulang. */
    private function bersihkan(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bukti_keahlian_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_bukti_keahlian_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_verifikasi_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_verifikasi_update');

        $check = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('CONSTRAINT_TYPE', 'CHECK')
            ->pluck('TABLE_NAME', 'CONSTRAINT_NAME');

        foreach ([
            'chk_verifikasi_waktu_keputusan', 'chk_verifikasi_masa_berlaku',
            'chk_lowongan_tanggal_publikasi', 'chk_lowongan_tanggal_tutup', 'chk_lowongan_urutan_tanggal',
        ] as $nama) {
            if ($tabel = $check->get($nama)) {
                DB::statement("ALTER TABLE {$tabel} DROP CHECK {$nama}");
            }
        }

        // Indeks penopang foreign key dipulihkan LEBIH DULU. Kalau tidak, menghapus
        // idx_lamaran_pencari_waktu / idx_bukti_pemilik akan ditolak MySQL (error 1553)
        // karena saat itu keduanya satu-satunya indeks yang menopang foreign key.
        foreach ([
            'fk_lamaran_pencari_kerja' => ['lamaran', 'pencari_kerja_id'],
            'fk_bukti_pengguna' => ['bukti', 'pengguna_id'],
        ] as $nama => [$tabel, $kolom]) {
            if (! $this->adaIndeks($tabel, $nama)) {
                DB::statement("CREATE INDEX {$nama} ON {$tabel} ({$kolom})");
            }
        }

        foreach ([
            'idx_lowongan_publik' => 'lowongan',
            'ft_lowongan_pencarian' => 'lowongan',
            'idx_lamaran_peringkat' => 'lamaran',
            'idx_lamaran_pencari_waktu' => 'lamaran',
            'idx_bukti_pemilik' => 'bukti',
            'idx_bukti_antrean' => 'bukti',
            'idx_klaim_pencari_aktif' => 'klaim_keahlian',
            'idx_verifikasi_bukti_cover' => 'verifikasi',
        ] as $nama => $tabel) {
            if ($this->adaIndeks($tabel, $nama)) {
                DB::statement("DROP INDEX {$nama} ON {$tabel}");
            }
        }
    }

    private function adaIndeks(string $tabel, string $nama): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tabel)
            ->where('INDEX_NAME', $nama)
            ->exists();
    }

    /**
     * Verifikasi yang berlaku untuk tiap bukti.
     *
     * Dua aturan yang sebelumnya hanya ada di aplikasi dipindahkan ke sini:
     * 1. Verifikasi yang dibuat SEBELUM berkas terakhir diunggah dianggap gugur,
     *    supaya mengganti PDF membatalkan hasil pemeriksaan lama.
     * 2. Dari verifikasi yang masih sah, hanya yang terbaru (id terbesar) yang dipakai.
     *
     * Catatan penting soal bentuk query. Versi dengan window function
     * ROW_NUMBER() OVER (PARTITION BY bukti_id) terlihat lebih rapi, tetapi memaksa
     * MySQL memakai derived table yang TIDAK dapat digabung (merge) ke query pemanggil.
     * Akibatnya seluruh tabel verifikasi dimaterialisasi lebih dulu walaupun query
     * pemanggil hanya butuh satu bukti. Pada data uji 45.000 baris verifikasi, membaca
     * status satu klaim memakan 541 ms dengan bentuk window function dan 0,43 ms dengan
     * bentuk subquery berkorelasi di bawah ini, karena bentuk ini mergeable sehingga
     * syarat bukti_id dapat didorong masuk dan dilayani indeks idx_verifikasi_bukti_terbaru.
     */
    private function viewVerifikasiTerbaru(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_verifikasi_terbaru');
        DB::statement(<<<'SQL'
            CREATE VIEW v_verifikasi_terbaru AS
            SELECT v.id, v.bukti_id, v.verifikator_id, v.keputusan, v.catatan,
                   v.diverifikasi_pada, v.berlaku_sampai, v.dibuat_pada
            FROM verifikasi v
            JOIN bukti b ON b.id = v.bukti_id
            WHERE v.dibuat_pada >= b.diunggah_pada
              AND v.id = (
                  SELECT MAX(v2.id)
                  FROM verifikasi v2
                  WHERE v2.bukti_id = v.bukti_id
                    AND v2.dibuat_pada >= b.diunggah_pada
              )
            SQL);
    }

    /**
     * Status centang biru. Perubahan dari skema awal:
     * - memakai v_verifikasi_terbaru, sehingga penggantian berkas mencabut centang biru;
     * - klaim pada keahlian yang dinonaktifkan admin tidak lagi ikut terhitung.
     */
    private function viewStatusKeahlian(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_status_keahlian');
        DB::statement(<<<'SQL'
            CREATE VIEW v_status_keahlian AS
            SELECT
                kk.id AS klaim_keahlian_id,
                kk.pencari_kerja_id,
                kk.keahlian_id,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM bukti_keahlian bk
                        JOIN v_verifikasi_terbaru vt ON vt.id = bk.verifikasi_id
                        WHERE bk.klaim_keahlian_id = kk.id
                          AND vt.keputusan = 'disetujui'
                          AND (vt.berlaku_sampai IS NULL OR vt.berlaku_sampai >= CURRENT_DATE)
                    ) THEN TRUE
                    ELSE FALSE
                END AS terverifikasi
            FROM klaim_keahlian kk
            JOIN keahlian k ON k.id = kk.keahlian_id
            WHERE kk.aktif = TRUE
              AND k.aktif = TRUE
            SQL);
    }

    /**
     * Aturan konsistensi antarkolom yang sebelumnya hanya dijaga controller.
     */
    private function checkConstraint(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE verifikasi ADD CONSTRAINT chk_verifikasi_waktu_keputusan CHECK (
                (keputusan = 'menunggu'  AND diverifikasi_pada IS NULL)
             OR (keputusan <> 'menunggu' AND diverifikasi_pada IS NOT NULL)
            )
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE verifikasi ADD CONSTRAINT chk_verifikasi_masa_berlaku CHECK (
                berlaku_sampai IS NULL OR keputusan = 'disetujui'
            )
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE lowongan ADD CONSTRAINT chk_lowongan_tanggal_publikasi CHECK (
                status <> 'dipublikasikan' OR dipublikasikan_pada IS NOT NULL
            )
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE lowongan ADD CONSTRAINT chk_lowongan_tanggal_tutup CHECK (
                status <> 'ditutup' OR ditutup_pada IS NOT NULL
            )
            SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE lowongan ADD CONSTRAINT chk_lowongan_urutan_tanggal CHECK (
                ditutup_pada IS NULL OR dipublikasikan_pada IS NULL
             OR ditutup_pada >= dipublikasikan_pada
            )
            SQL);
    }

    /**
     * bukti_keahlian menghubungkan verifikasi dengan klaim keahlian, tetapi foreign key
     * tidak bisa menyatakan bahwa keduanya harus milik orang yang sama. Tanpa trigger,
     * sertifikat milik pengguna A dapat memberi centang biru pada klaim milik pengguna B.
     * Trigger juga menolak penautan dari verifikasi yang belum disetujui.
     */
    private function triggerBuktiKeahlian(): void
    {
        foreach (['insert' => 'INSERT', 'update' => 'UPDATE'] as $akhiran => $peristiwa) {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_bukti_keahlian_{$akhiran}");
            DB::unprepared(<<<SQL
                CREATE TRIGGER trg_bukti_keahlian_{$akhiran}
                BEFORE {$peristiwa} ON bukti_keahlian
                FOR EACH ROW
                BEGIN
                    DECLARE pemilik_bukti BIGINT UNSIGNED;
                    DECLARE pemilik_klaim BIGINT UNSIGNED;
                    DECLARE keputusan_verifikasi VARCHAR(20);

                    SELECT b.pengguna_id, v.keputusan
                      INTO pemilik_bukti, keputusan_verifikasi
                      FROM verifikasi v
                      JOIN bukti b ON b.id = v.bukti_id
                     WHERE v.id = NEW.verifikasi_id;

                    SELECT pk.pengguna_id
                      INTO pemilik_klaim
                      FROM klaim_keahlian kk
                      JOIN pencari_kerja pk ON pk.id = kk.pencari_kerja_id
                     WHERE kk.id = NEW.klaim_keahlian_id;

                    IF pemilik_bukti <> pemilik_klaim THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'bukti_keahlian: klaim keahlian harus milik pemilik bukti yang sama';
                    END IF;

                    IF keputusan_verifikasi <> 'disetujui' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'bukti_keahlian: hanya verifikasi berkeputusan disetujui yang boleh membuktikan keahlian';
                    END IF;
                END
                SQL);
        }
    }

    /**
     * Seorang verifikator tidak boleh memeriksa sertifikat miliknya sendiri. Aturan ini
     * penting karena satu akun boleh memegang lebih dari satu peran, sehingga seorang
     * verifikator bisa sekaligus menjadi pencari kerja yang mengunggah sertifikat.
     * Sebelumnya aturan ini hanya dijaga PemeriksaanController, dan data awal pada seeder
     * sempat melanggarnya tanpa terdeteksi.
     */
    private function triggerVerifikasi(): void
    {
        foreach (['insert' => 'INSERT', 'update' => 'UPDATE'] as $akhiran => $peristiwa) {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_verifikasi_{$akhiran}");
            DB::unprepared(<<<SQL
                CREATE TRIGGER trg_verifikasi_{$akhiran}
                BEFORE {$peristiwa} ON verifikasi
                FOR EACH ROW
                BEGIN
                    DECLARE pemilik_bukti BIGINT UNSIGNED;
                    DECLARE pengguna_verifikator BIGINT UNSIGNED;

                    SELECT pengguna_id INTO pemilik_bukti FROM bukti WHERE id = NEW.bukti_id;
                    SELECT pengguna_id INTO pengguna_verifikator FROM verifikator WHERE id = NEW.verifikator_id;

                    IF pemilik_bukti = pengguna_verifikator THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'verifikasi: verifikator tidak boleh memeriksa sertifikat miliknya sendiri';
                    END IF;
                END
                SQL);
        }
    }

    /**
     * Indeks untuk query halaman daftar. Setiap indeks di bawah ini punya satu query
     * nyata di controller yang urutan kolomnya persis sama.
     */
    private function indeks(): void
    {
        // PublikController::lowongan() -> WHERE status = 'dipublikasikan' ORDER BY dipublikasikan_pada DESC
        DB::statement('CREATE INDEX idx_lowongan_publik ON lowongan (status, dipublikasikan_pada DESC)');

        // Pencarian kata kunci. Menggantikan LIKE '%kata%' yang tidak dapat memakai indeks.
        DB::statement('CREATE FULLTEXT INDEX ft_lowongan_pencarian ON lowongan (posisi, deskripsi)');

        // PelamarController::index() -> WHERE lowongan_id = ? ORDER BY skor_kecocokan DESC, dilamar_pada
        DB::statement('CREATE INDEX idx_lamaran_peringkat ON lamaran (lowongan_id, skor_kecocokan DESC, dilamar_pada)');

        // LamaranController::index() -> WHERE pencari_kerja_id = ? ORDER BY dilamar_pada DESC
        DB::statement('CREATE INDEX idx_lamaran_pencari_waktu ON lamaran (pencari_kerja_id, dilamar_pada DESC)');

        // SertifikatController::index() -> WHERE pengguna_id = ? ORDER BY diunggah_pada DESC
        DB::statement('CREATE INDEX idx_bukti_pemilik ON bukti (pengguna_id, diunggah_pada DESC)');

        // Verifikator\DashboardController::index() -> antrean ORDER BY diunggah_pada
        DB::statement('CREATE INDEX idx_bukti_antrean ON bukti (diunggah_pada)');

        // Kecocokan::hitung() dan profil -> WHERE pencari_kerja_id = ? AND aktif = 1
        DB::statement('CREATE INDEX idx_klaim_pencari_aktif ON klaim_keahlian (pencari_kerja_id, aktif)');

        // Jadikan indeks pencarian verifikasi terbaru sebagai covering index, supaya
        // v_verifikasi_terbaru tidak perlu membaca baris tabel sama sekali.
        //
        // idx_verifikasi_bukti_terbaru adalah satu-satunya indeks yang menopang
        // fk_verifikasi_bukti, jadi indeks pengganti harus dibuat LEBIH DULU sebelum
        // yang lama dihapus; kalau tidak, MySQL menolak dengan error 1553.
        DB::statement('CREATE INDEX idx_verifikasi_bukti_cover ON verifikasi (bukti_id, id DESC, keputusan, berlaku_sampai)');
        DB::statement('DROP INDEX idx_verifikasi_bukti_terbaru ON verifikasi');
        DB::statement('ALTER TABLE verifikasi RENAME INDEX idx_verifikasi_bukti_cover TO idx_verifikasi_bukti_terbaru');

        // Indeks satu kolom penopang foreign key sudah menjadi prefix indeks komposit
        // di atas, jadi hanya menambah biaya tulis tanpa pernah dipakai optimizer.
        //
        // Pada database baru InnoDB menghapusnya SENDIRI begitu indeks komposit dibuat,
        // karena indeks itu dibuat implisit oleh MySQL saat foreign key dideklarasikan.
        // Indeks yang dibuat pengguna secara eksplisit (misalnya oleh bersihkan() saat
        // migration diulang) tidak ikut terhapus, jadi penghapusannya dijaga kondisi.
        foreach (['fk_lamaran_pencari_kerja' => 'lamaran', 'fk_bukti_pengguna' => 'bukti'] as $nama => $tabel) {
            if ($this->adaIndeks($tabel, $nama)) {
                DB::statement("DROP INDEX {$nama} ON {$tabel}");
            }
        }
    }
};
