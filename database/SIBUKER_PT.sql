-- =========================================================
-- SIBUKER-PT - Skema Basis Data
-- Target: MySQL 8.0+ (diuji pada MySQL 8.4)
--
-- Berkas ini adalah rancangan fisik yang berlaku, hasil dari migration Laravel
-- di database/migrations. Perubahan terhadap revisi sebelumnya:
--   1. Aturan "verifikasi terbaru" dipindah ke view v_verifikasi_terbaru dan kini
--      memperhitungkan bukti.diunggah_pada, sehingga mengganti berkas PDF mencabut
--      centang biru sampai sertifikat diperiksa ulang.
--   2. v_status_keahlian tidak lagi menghitung klaim pada keahlian yang dinonaktifkan.
--   3. Trigger pada bukti_keahlian mencegah sertifikat milik satu pengguna memberi
--      centang biru pada klaim milik pengguna lain.
--   4. CHECK constraint tambahan pada verifikasi dan lowongan.
--   5. Indeks komposit dan FULLTEXT untuk query halaman daftar.
--
-- Konsep utama:
-- 1. Keahlian dapat dicantumkan tanpa sertifikat.
-- 2. Bukti diunggah sebagai berkas PDF.
-- 3. Verifikator memeriksa bukti dan menentukan keahlian yang terbukti.
-- 4. Perusahaan menentukan kebutuhan verifikasi untuk setiap keahlian lowongan.
-- =========================================================

CREATE DATABASE IF NOT EXISTS sibuker_pt
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE sibuker_pt;

SET FOREIGN_KEY_CHECKS = 0;

DROP TRIGGER IF EXISTS trg_bukti_keahlian_insert;
DROP TRIGGER IF EXISTS trg_bukti_keahlian_update;
DROP TRIGGER IF EXISTS trg_verifikasi_insert;
DROP TRIGGER IF EXISTS trg_verifikasi_update;

DROP VIEW IF EXISTS v_status_keahlian;
DROP VIEW IF EXISTS v_verifikasi_terbaru;

DROP TABLE IF EXISTS tahap_seleksi;
DROP TABLE IF EXISTS lamaran;
DROP TABLE IF EXISTS syarat_keahlian;
DROP TABLE IF EXISTS lowongan;
DROP TABLE IF EXISTS bukti_keahlian;
DROP TABLE IF EXISTS verifikasi;
DROP TABLE IF EXISTS bukti;
DROP TABLE IF EXISTS klaim_keahlian;
DROP TABLE IF EXISTS kewenangan_verifikator;
DROP TABLE IF EXISTS jenis_bukti;
DROP TABLE IF EXISTS keahlian;
DROP TABLE IF EXISTS perusahaan;
DROP TABLE IF EXISTS verifikator;
DROP TABLE IF EXISTS pencari_kerja;
DROP TABLE IF EXISTS pengguna;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- MODUL PENGGUNA DAN PROFIL
--
-- pengguna menyimpan akun. Peran tidak disimpan sebagai kolom, melainkan
-- ditentukan oleh keberadaan baris pada tabel spesialisasi (pencari_kerja,
-- verifikator, perusahaan) dan oleh kolom pengguna.is_admin. Dengan begitu satu
-- akun dapat memegang lebih dari satu peran, tetapi paling banyak satu profil
-- untuk setiap peran karena pengguna_id dibatasi UNIQUE.
-- =========================================================

CREATE TABLE pengguna (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    -- 191 karakter agar indeks UNIQUE muat dalam batas 767 byte pada utf8mb4.
    email VARCHAR(191) NOT NULL,
    password VARCHAR(255) NOT NULL,
    nomor_telepon VARCHAR(25) NULL,
    foto_profil VARCHAR(500) NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE,
    status_akun ENUM('aktif', 'nonaktif', 'ditangguhkan') NOT NULL DEFAULT 'aktif',
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_pengguna_email UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE pencari_kerja (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengguna_id BIGINT UNSIGNED NOT NULL,
    headline VARCHAR(200) NULL,
    ringkasan TEXT NULL,
    lokasi VARCHAR(150) NULL,
    tanggal_lahir DATE NULL,
    CONSTRAINT uq_pencari_kerja_pengguna UNIQUE (pengguna_id),
    CONSTRAINT fk_pencari_kerja_pengguna
        FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE verifikator (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengguna_id BIGINT UNSIGNED NOT NULL,
    instansi VARCHAR(200) NULL,
    jabatan VARCHAR(150) NULL,
    status_verifikator ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    CONSTRAINT uq_verifikator_pengguna UNIQUE (pengguna_id),
    CONSTRAINT fk_verifikator_pengguna
        FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Akun perusahaan memakai ON DELETE RESTRICT, bukan CASCADE. Menghapus akun yang
-- masih memiliki profil perusahaan akan ditolak database, supaya lowongan dan
-- lamaran yang menempel padanya tidak ikut lenyap tanpa disadari.
CREATE TABLE perusahaan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengguna_id BIGINT UNSIGNED NOT NULL,
    nama VARCHAR(200) NOT NULL,
    nib VARCHAR(50) NULL,
    deskripsi TEXT NULL,
    alamat TEXT NULL,
    situs_web VARCHAR(500) NULL,
    logo VARCHAR(500) NULL,
    status_perusahaan ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
    CONSTRAINT uq_perusahaan_pengguna UNIQUE (pengguna_id),
    -- UNIQUE pada kolom nullable: MySQL mengizinkan banyak baris NULL, jadi
    -- perusahaan yang belum mendaftarkan NIB tetap dapat disimpan.
    CONSTRAINT uq_perusahaan_nib UNIQUE (nib),
    CONSTRAINT fk_perusahaan_pengguna
        FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- MODUL KEAHLIAN DAN VERIFIKASI
--
-- Data master (keahlian, jenis_bukti) memakai ON DELETE RESTRICT supaya baris yang
-- sudah dirujuk transaksi tidak bisa dihapus; admin menonaktifkannya lewat kolom
-- aktif. Data turunan memakai ON DELETE CASCADE.
-- =========================================================

CREATE TABLE keahlian (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    kategori VARCHAR(120) NULL,
    deskripsi TEXT NULL,
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_keahlian_nama UNIQUE (nama)
) ENGINE=InnoDB;

CREATE TABLE jenis_bukti (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    CONSTRAINT uq_jenis_bukti_nama UNIQUE (nama)
) ENGINE=InnoDB;

-- Entitas asosiatif M:N antara verifikator dan keahlian.
CREATE TABLE kewenangan_verifikator (
    verifikator_id BIGINT UNSIGNED NOT NULL,
    keahlian_id BIGINT UNSIGNED NOT NULL,
    diberikan_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (verifikator_id, keahlian_id),
    CONSTRAINT fk_kewenangan_verifikator
        FOREIGN KEY (verifikator_id) REFERENCES verifikator(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_kewenangan_keahlian
        FOREIGN KEY (keahlian_id) REFERENCES keahlian(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE klaim_keahlian (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pencari_kerja_id BIGINT UNSIGNED NOT NULL,
    keahlian_id BIGINT UNSIGNED NOT NULL,
    level_klaim ENUM('pemula', 'menengah', 'mahir', 'ahli') NULL,
    tanggal_ditambahkan TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    aktif BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT uq_klaim_pencari_keahlian
        UNIQUE (pencari_kerja_id, keahlian_id),
    -- Melayani Kecocokan::hitung() dan halaman profil: WHERE pencari_kerja_id = ? AND aktif = 1
    INDEX idx_klaim_pencari_aktif (pencari_kerja_id, aktif),
    CONSTRAINT fk_klaim_pencari_kerja
        FOREIGN KEY (pencari_kerja_id) REFERENCES pencari_kerja(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_klaim_keahlian
        FOREIGN KEY (keahlian_id) REFERENCES keahlian(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- diunggah_pada bukan sekadar catatan waktu. Nilainya diperbarui setiap kali berkas
-- PDF diganti, dan dipakai v_verifikasi_terbaru untuk menggugurkan hasil pemeriksaan
-- yang dibuat sebelum berkas terakhir diunggah.
CREATE TABLE bukti (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pengguna_id BIGINT UNSIGNED NOT NULL,
    jenis_bukti_id BIGINT UNSIGNED NOT NULL,
    judul VARCHAR(200) NOT NULL,
    penerbit VARCHAR(200) NULL,
    url_berkas VARCHAR(500) NOT NULL,
    tanggal_terbit DATE NULL,
    diunggah_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Halaman "Sertifikat saya": WHERE pengguna_id = ? ORDER BY diunggah_pada DESC.
    -- Indeks ini sekaligus menopang fk_bukti_pengguna, jadi tidak perlu indeks terpisah.
    INDEX idx_bukti_pemilik (pengguna_id, diunggah_pada DESC),
    -- Antrean verifikator: ORDER BY diunggah_pada.
    INDEX idx_bukti_antrean (diunggah_pada),
    CONSTRAINT fk_bukti_pengguna
        FOREIGN KEY (pengguna_id) REFERENCES pengguna(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bukti_jenis
        FOREIGN KEY (jenis_bukti_id) REFERENCES jenis_bukti(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabel ini bersifat append-only: keputusan lama tidak pernah ditimpa, pemeriksaan
-- ulang menambah baris baru. Riwayat pemeriksaan karena itu selalu dapat ditelusuri.
CREATE TABLE verifikasi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bukti_id BIGINT UNSIGNED NOT NULL,
    verifikator_id BIGINT UNSIGNED NOT NULL,
    keputusan ENUM('menunggu', 'disetujui', 'ditolak') NOT NULL DEFAULT 'menunggu',
    catatan TEXT NULL,
    diverifikasi_pada TIMESTAMP NULL,
    berlaku_sampai DATE NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Covering index untuk v_verifikasi_terbaru: kolom keputusan dan berlaku_sampai
    -- ikut disimpan sehingga view tidak perlu membaca baris tabelnya sama sekali.
    INDEX idx_verifikasi_bukti_terbaru (bukti_id, id DESC, keputusan, berlaku_sampai),
    INDEX idx_verifikasi_verifikator (verifikator_id),
    -- Keputusan final wajib punya waktu pemeriksaan; keputusan yang ditunda tidak boleh punya.
    CONSTRAINT chk_verifikasi_waktu_keputusan CHECK (
        (keputusan = 'menunggu'  AND diverifikasi_pada IS NULL)
     OR (keputusan <> 'menunggu' AND diverifikasi_pada IS NOT NULL)
    ),
    -- Masa berlaku hanya bermakna pada verifikasi yang disetujui.
    CONSTRAINT chk_verifikasi_masa_berlaku CHECK (
        berlaku_sampai IS NULL OR keputusan = 'disetujui'
    ),
    CONSTRAINT fk_verifikasi_bukti
        FOREIGN KEY (bukti_id) REFERENCES bukti(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_verifikasi_verifikator
        FOREIGN KEY (verifikator_id) REFERENCES verifikator(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Entitas asosiatif M:N: satu hasil verifikasi dapat membuktikan beberapa klaim keahlian.
-- Aturan "klaim harus milik pemilik bukti" tidak dapat dinyatakan foreign key dan
-- karena itu dijaga trigger di bagian bawah berkas ini.
CREATE TABLE bukti_keahlian (
    verifikasi_id BIGINT UNSIGNED NOT NULL,
    klaim_keahlian_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (verifikasi_id, klaim_keahlian_id),
    INDEX idx_bukti_keahlian_klaim (klaim_keahlian_id),
    CONSTRAINT fk_bukti_keahlian_verifikasi
        FOREIGN KEY (verifikasi_id) REFERENCES verifikasi(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bukti_keahlian_klaim
        FOREIGN KEY (klaim_keahlian_id) REFERENCES klaim_keahlian(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- MODUL LOWONGAN DAN PERSYARATAN
-- =========================================================

CREATE TABLE lowongan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    perusahaan_id BIGINT UNSIGNED NOT NULL,
    kode VARCHAR(50) NOT NULL,
    posisi VARCHAR(180) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(150) NULL,
    tipe_pekerjaan ENUM('penuh_waktu', 'paruh_waktu', 'kontrak', 'magang', 'freelance')
        NOT NULL DEFAULT 'penuh_waktu',
    status ENUM('draft', 'dipublikasikan', 'ditutup') NOT NULL DEFAULT 'draft',
    dipublikasikan_pada TIMESTAMP NULL,
    ditutup_pada TIMESTAMP NULL,
    dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_lowongan_kode UNIQUE (kode),
    -- Halaman publik: WHERE status = 'dipublikasikan' ORDER BY dipublikasikan_pada DESC.
    INDEX idx_lowongan_publik (status, dipublikasikan_pada DESC),
    -- Pencarian kata kunci. Menggantikan LIKE '%kata%' yang tidak pernah dapat memakai
    -- indeks karena diawali wildcard.
    FULLTEXT INDEX ft_lowongan_pencarian (posisi, deskripsi),
    -- Status dan kolom tanggalnya harus sejalan.
    CONSTRAINT chk_lowongan_tanggal_publikasi CHECK (
        status <> 'dipublikasikan' OR dipublikasikan_pada IS NOT NULL
    ),
    CONSTRAINT chk_lowongan_tanggal_tutup CHECK (
        status <> 'ditutup' OR ditutup_pada IS NOT NULL
    ),
    CONSTRAINT chk_lowongan_urutan_tanggal CHECK (
        ditutup_pada IS NULL OR dipublikasikan_pada IS NULL
     OR ditutup_pada >= dipublikasikan_pada
    ),
    CONSTRAINT fk_lowongan_perusahaan
        FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE syarat_keahlian (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lowongan_id BIGINT UNSIGNED NOT NULL,
    keahlian_id BIGINT UNSIGNED NOT NULL,
    level_minimum ENUM('pemula', 'menengah', 'mahir', 'ahli') NULL,
    sifat ENUM('wajib', 'opsional') NOT NULL DEFAULT 'wajib',
    bobot DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    wajib_terverifikasi BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT uq_syarat_lowongan_keahlian UNIQUE (lowongan_id, keahlian_id),
    CONSTRAINT chk_syarat_bobot CHECK (bobot >= 0),
    CONSTRAINT fk_syarat_lowongan
        FOREIGN KEY (lowongan_id) REFERENCES lowongan(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_syarat_keahlian
        FOREIGN KEY (keahlian_id) REFERENCES keahlian(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================
-- MODUL LAMARAN DAN SELEKSI
-- =========================================================

-- skor_kecocokan sengaja disimpan (denormalisasi terkendali). Skor adalah potret saat
-- melamar; kalau dihitung ulang setiap kali dibaca, nilainya akan berubah ketika
-- pencari kerja menambah keahlian sesudah melamar, sehingga riwayat seleksi tidak
-- lagi dapat dipertanggungjawabkan.
CREATE TABLE lamaran (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lowongan_id BIGINT UNSIGNED NOT NULL,
    pencari_kerja_id BIGINT UNSIGNED NOT NULL,
    status ENUM('dikirim', 'ditinjau', 'diproses', 'diterima', 'ditolak', 'ditarik')
        NOT NULL DEFAULT 'dikirim',
    skor_kecocokan DECIMAL(5,2) NULL,
    dilamar_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_lamaran_lowongan_pencari
        UNIQUE (lowongan_id, pencari_kerja_id),
    -- Daftar pelamar: WHERE lowongan_id = ? ORDER BY skor_kecocokan DESC, dilamar_pada.
    INDEX idx_lamaran_peringkat (lowongan_id, skor_kecocokan DESC, dilamar_pada),
    -- Halaman "Lamaran saya": WHERE pencari_kerja_id = ? ORDER BY dilamar_pada DESC.
    -- Sekaligus menopang fk_lamaran_pencari_kerja.
    INDEX idx_lamaran_pencari_waktu (pencari_kerja_id, dilamar_pada DESC),
    CONSTRAINT chk_lamaran_skor
        CHECK (skor_kecocokan IS NULL OR skor_kecocokan BETWEEN 0 AND 100),
    CONSTRAINT fk_lamaran_lowongan
        FOREIGN KEY (lowongan_id) REFERENCES lowongan(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_lamaran_pencari_kerja
        FOREIGN KEY (pencari_kerja_id) REFERENCES pencari_kerja(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tahap_seleksi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lamaran_id BIGINT UNSIGNED NOT NULL,
    nama_tahap VARCHAR(120) NOT NULL,
    urutan SMALLINT UNSIGNED NOT NULL,
    status ENUM('menunggu', 'berlangsung', 'lulus', 'tidak_lulus')
        NOT NULL DEFAULT 'menunggu',
    jadwal DATETIME NULL,
    catatan TEXT NULL,
    diperbarui_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_tahap_lamaran_urutan UNIQUE (lamaran_id, urutan),
    CONSTRAINT chk_tahap_urutan CHECK (urutan > 0),
    CONSTRAINT fk_tahap_lamaran
        FOREIGN KEY (lamaran_id) REFERENCES lamaran(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- VIEW 1: VERIFIKASI TERBARU YANG MASIH SAH
--
-- Menghasilkan paling banyak satu baris per bukti. Dua aturan dijalankan di sini:
--   1. Verifikasi yang dibuat sebelum berkas terakhir diunggah dianggap gugur.
--   2. Dari verifikasi yang tersisa, hanya yang terbaru (id terbesar) yang berlaku.
--
-- Bentuk subquery berkorelasi dipilih secara sadar. Versi dengan window function
-- ROW_NUMBER() OVER (PARTITION BY bukti_id) lebih ringkas, tetapi memaksa MySQL
-- memakai derived table yang tidak dapat digabung ke query pemanggil, sehingga
-- seluruh tabel verifikasi dimaterialisasi walaupun hanya satu bukti yang dibutuhkan.
-- Pada data uji 45.000 baris verifikasi, membaca status satu klaim memakan 541 ms
-- dengan window function dan 0,43 ms dengan bentuk di bawah ini.
-- =========================================================

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
  );

-- =========================================================
-- VIEW 2: STATUS CENTANG BIRU
--
-- Status terverifikasi tidak disimpan sebagai kolom di tabel mana pun. Menyimpannya
-- akan menimbulkan anomali pembaruan, karena nilainya berubah sendiri saat masa
-- berlaku verifikasi lewat tanpa ada operasi tulis apa pun ke basis data.
-- =========================================================

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
  AND k.aktif = TRUE;

-- =========================================================
-- TRIGGER INTEGRITAS bukti_keahlian
--
-- Foreign key hanya dapat menyatakan bahwa verifikasi_id dan klaim_keahlian_id ada.
-- Foreign key tidak dapat menyatakan bahwa keduanya harus bermuara pada pengguna yang
-- sama. Tanpa trigger ini, satu baris INSERT dapat membuat sertifikat milik pengguna A
-- memberi centang biru pada klaim keahlian milik pengguna B.
-- =========================================================

DELIMITER $$

CREATE TRIGGER trg_bukti_keahlian_insert
BEFORE INSERT ON bukti_keahlian
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
END$$

CREATE TRIGGER trg_bukti_keahlian_update
BEFORE UPDATE ON bukti_keahlian
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
END$$

-- =========================================================
-- TRIGGER INTEGRITAS verifikasi
--
-- Satu akun boleh memegang lebih dari satu peran, sehingga seorang verifikator dapat
-- sekaligus menjadi pencari kerja yang mengunggah sertifikat. Verifikator tidak boleh
-- memeriksa sertifikat miliknya sendiri, dan aturan itu tidak dapat dinyatakan sebagai
-- foreign key maupun CHECK karena melibatkan dua tabel lain.
-- =========================================================

CREATE TRIGGER trg_verifikasi_insert
BEFORE INSERT ON verifikasi
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
END$$

CREATE TRIGGER trg_verifikasi_update
BEFORE UPDATE ON verifikasi
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
END$$

DELIMITER ;

-- Data awal jenis bukti.
INSERT INTO jenis_bukti (nama) VALUES
    ('Sertifikat Pelatihan'),
    ('Sertifikat Profesi'),
    ('Hasil Uji Kompetensi');
