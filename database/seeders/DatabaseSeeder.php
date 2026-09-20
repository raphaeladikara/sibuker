<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Data demo SIBUKER-PT. Semua orang, perusahaan, dan sertifikat di sini fiktif.
 * Kata sandi semua akun: password
 *
 * Status centang biru TIDAK diisi di sini. Nilainya muncul sendiri dari view
 * v_status_keahlian berdasarkan tabel verifikasi dan bukti_keahlian.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sandi = Hash::make('password');

        DB::table('pengguna')->insert(array_map(fn ($b) => $b + ['password' => $sandi], [
            ['id' => 1, 'nama' => 'Admin SIBUKER', 'email' => 'admin@sibuker.test', 'nomor_telepon' => '081200000001', 'is_admin' => true, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-01 08:00'],
            ['id' => 2, 'nama' => 'Dimas Prasetyo', 'email' => 'dimas@mail.test', 'nomor_telepon' => '081234567802', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-02 09:10'],
            ['id' => 3, 'nama' => 'Salsabila Putri', 'email' => 'salsa@mail.test', 'nomor_telepon' => '081234567803', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-02 13:25'],
            ['id' => 4, 'nama' => 'Yohanes Kurniawan', 'email' => 'yohanes@mail.test', 'nomor_telepon' => '081234567804', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-03 10:00'],
            ['id' => 5, 'nama' => 'Nadia Rahmawati', 'email' => 'nadia@mail.test', 'nomor_telepon' => '081234567805', 'is_admin' => false, 'status_akun' => 'nonaktif', 'dibuat_pada' => '2026-09-04 11:40'],
            ['id' => 6, 'nama' => 'Dr. Hendra Wijaya', 'email' => 'hendra@lsp-inf.test', 'nomor_telepon' => '081234567806', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-01 08:30'],
            ['id' => 7, 'nama' => 'Maya Anggraini', 'email' => 'maya@praktisi.test', 'nomor_telepon' => '081234567807', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-01 09:00'],
            ['id' => 8, 'nama' => 'Rizky Firmansyah', 'email' => 'hr@nusantaradata.test', 'nomor_telepon' => '081234567808', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-01 10:15'],
            ['id' => 9, 'nama' => 'Clara Tanoto', 'email' => 'rekrut@arunika.test', 'nomor_telepon' => '081234567809', 'is_admin' => false, 'status_akun' => 'aktif', 'dibuat_pada' => '2026-09-05 14:00'],
            ['id' => 10, 'nama' => 'Bagus Santoso', 'email' => 'hrd@samudralog.test', 'nomor_telepon' => '081234567810', 'is_admin' => false, 'status_akun' => 'ditangguhkan', 'dibuat_pada' => '2026-09-06 15:30'],
        ]));

        DB::table('pencari_kerja')->insert([
            ['id' => 1, 'pengguna_id' => 2, 'headline' => 'Data Analyst | SQL & Python', 'ringkasan' => 'Lulusan SMK TKJ. Belajar analisis data sendiri dan lewat pelatihan vokasi, sehari-hari membuat dashboard penjualan.', 'lokasi' => 'Surabaya', 'tanggal_lahir' => '2003-04-12'],
            ['id' => 2, 'pengguna_id' => 3, 'headline' => 'Web Developer (Laravel)', 'ringkasan' => 'Membangun aplikasi web Laravel dan MySQL untuk UMKM sejak 2024.', 'lokasi' => 'Sidoarjo', 'tanggal_lahir' => '2002-11-03'],
            ['id' => 3, 'pengguna_id' => 4, 'headline' => 'Operator CNC bersertifikat', 'ringkasan' => 'Tujuh tahun di pemesinan CNC untuk komponen otomotif.', 'lokasi' => 'Gresik', 'tanggal_lahir' => '1996-02-20'],
            ['id' => 4, 'pengguna_id' => 5, 'headline' => 'Staf Akuntansi', 'ringkasan' => 'Menyusun laporan keuangan bulanan dan rekonsiliasi bank.', 'lokasi' => 'Malang', 'tanggal_lahir' => '2000-07-09'],
            ['id' => 5, 'pengguna_id' => 7, 'headline' => 'UI/UX Designer', 'ringkasan' => 'Praktisi desain produk digital. Juga verifikator untuk bidang desain.', 'lokasi' => 'Surabaya', 'tanggal_lahir' => '1994-05-17'],
        ]);

        DB::table('verifikator')->insert([
            ['id' => 1, 'pengguna_id' => 6, 'instansi' => 'LSP Informatika Nusantara', 'jabatan' => 'Asesor Kompetensi', 'status_verifikator' => 'aktif'],
            ['id' => 2, 'pengguna_id' => 7, 'instansi' => 'Praktisi Independen', 'jabatan' => 'Lead Product Designer', 'status_verifikator' => 'aktif'],
            ['id' => 3, 'pengguna_id' => 1, 'instansi' => 'BLK Manufaktur Jawa Timur', 'jabatan' => 'Instruktur Senior', 'status_verifikator' => 'nonaktif'],
        ]);

        DB::table('perusahaan')->insert([
            ['id' => 1, 'pengguna_id' => 8, 'nama' => 'PT Nusantara Data Solusi', 'nib' => '9120001234567', 'deskripsi' => 'Konsultan analitik data untuk ritel dan logistik.', 'alamat' => 'Jl. Raya Darmo No. 45, Surabaya', 'situs_web' => 'https://nusantaradata.test', 'status_perusahaan' => 'aktif'],
            ['id' => 2, 'pengguna_id' => 9, 'nama' => 'CV Arunika Digital', 'nib' => '9120007654321', 'deskripsi' => 'Studio pengembangan aplikasi web dan mobile.', 'alamat' => 'Jl. Kertajaya Indah No. 12, Surabaya', 'situs_web' => 'https://arunika.test', 'status_perusahaan' => 'aktif'],
            ['id' => 3, 'pengguna_id' => 10, 'nama' => 'PT Samudra Logistik Timur', 'nib' => '9120005551112', 'deskripsi' => 'Logistik dan pergudangan.', 'alamat' => 'Kawasan Industri Gresik Blok C-7', 'situs_web' => null, 'status_perusahaan' => 'nonaktif'],
        ]);

        DB::table('keahlian')->insert([
            ['id' => 1, 'nama' => 'SQL', 'kategori' => 'Teknologi Informasi', 'deskripsi' => 'Menulis kueri, join, agregasi, dan subquery di basis data relasional.', 'aktif' => true],
            ['id' => 2, 'nama' => 'Python', 'kategori' => 'Teknologi Informasi', 'deskripsi' => 'Python untuk otomasi dan analisis data.', 'aktif' => true],
            ['id' => 3, 'nama' => 'Laravel', 'kategori' => 'Teknologi Informasi', 'deskripsi' => 'Aplikasi web dengan framework Laravel.', 'aktif' => true],
            ['id' => 4, 'nama' => 'Visualisasi Data', 'kategori' => 'Teknologi Informasi', 'deskripsi' => 'Dashboard dan grafik yang mudah dibaca.', 'aktif' => true],
            ['id' => 5, 'nama' => 'Desain UI/UX', 'kategori' => 'Desain', 'deskripsi' => 'Riset pengguna, wireframe, dan prototipe antarmuka.', 'aktif' => true],
            ['id' => 6, 'nama' => 'Pemrograman CNC', 'kategori' => 'Manufaktur', 'deskripsi' => 'Menyusun G-code dan mengoperasikan mesin CNC.', 'aktif' => true],
            ['id' => 7, 'nama' => 'K3 Industri', 'kategori' => 'Manufaktur', 'deskripsi' => 'Keselamatan dan kesehatan kerja di pabrik.', 'aktif' => true],
            ['id' => 8, 'nama' => 'Akuntansi Dasar', 'kategori' => 'Keuangan', 'deskripsi' => 'Jurnal, buku besar, dan laporan keuangan sederhana.', 'aktif' => false],
        ]);

        // Tiga baris pertama sama dengan data awal di SIBUKER_PT.sql.
        DB::table('jenis_bukti')->insert([
            ['id' => 1, 'nama' => 'Sertifikat Pelatihan'],
            ['id' => 2, 'nama' => 'Sertifikat Profesi'],
            ['id' => 3, 'nama' => 'Hasil Uji Kompetensi'],
            ['id' => 4, 'nama' => 'Surat Rekomendasi'],
        ]);

        DB::table('kewenangan_verifikator')->insert([
            ['verifikator_id' => 1, 'keahlian_id' => 1, 'diberikan_pada' => '2026-09-01 09:00'],
            ['verifikator_id' => 1, 'keahlian_id' => 2, 'diberikan_pada' => '2026-09-01 09:00'],
            ['verifikator_id' => 1, 'keahlian_id' => 3, 'diberikan_pada' => '2026-09-01 09:00'],
            ['verifikator_id' => 1, 'keahlian_id' => 4, 'diberikan_pada' => '2026-09-01 09:00'],
            ['verifikator_id' => 2, 'keahlian_id' => 5, 'diberikan_pada' => '2026-09-01 09:30'],
            // Desain UI/UX sengaja diberikan ke dua verifikator. Maya (verifikator 2) juga
            // seorang pencari kerja yang punya sertifikat UI/UX sendiri, dan dia tidak boleh
            // memeriksa miliknya sendiri. Tanpa verifikator kedua, sertifikatnya tidak akan
            // pernah bisa diperiksa siapa pun.
            ['verifikator_id' => 1, 'keahlian_id' => 5, 'diberikan_pada' => '2026-09-01 09:30'],
            ['verifikator_id' => 3, 'keahlian_id' => 6, 'diberikan_pada' => '2026-09-01 10:00'],
            ['verifikator_id' => 3, 'keahlian_id' => 7, 'diberikan_pada' => '2026-09-01 10:00'],
        ]);

        DB::table('klaim_keahlian')->insert([
            ['id' => 1, 'pencari_kerja_id' => 1, 'keahlian_id' => 1, 'level_klaim' => 'mahir', 'tanggal_ditambahkan' => '2026-09-03 08:00', 'aktif' => true],
            ['id' => 2, 'pencari_kerja_id' => 1, 'keahlian_id' => 2, 'level_klaim' => 'menengah', 'tanggal_ditambahkan' => '2026-09-03 08:05', 'aktif' => true],
            ['id' => 3, 'pencari_kerja_id' => 1, 'keahlian_id' => 4, 'level_klaim' => 'mahir', 'tanggal_ditambahkan' => '2026-09-04 07:50', 'aktif' => true],
            ['id' => 4, 'pencari_kerja_id' => 2, 'keahlian_id' => 3, 'level_klaim' => 'mahir', 'tanggal_ditambahkan' => '2026-09-05 12:00', 'aktif' => true],
            ['id' => 5, 'pencari_kerja_id' => 2, 'keahlian_id' => 1, 'level_klaim' => 'menengah', 'tanggal_ditambahkan' => '2026-09-05 12:02', 'aktif' => true],
            ['id' => 6, 'pencari_kerja_id' => 3, 'keahlian_id' => 6, 'level_klaim' => 'ahli', 'tanggal_ditambahkan' => '2026-09-06 09:00', 'aktif' => true],
            ['id' => 7, 'pencari_kerja_id' => 3, 'keahlian_id' => 7, 'level_klaim' => 'menengah', 'tanggal_ditambahkan' => '2026-09-06 09:03', 'aktif' => true],
            ['id' => 8, 'pencari_kerja_id' => 4, 'keahlian_id' => 8, 'level_klaim' => 'mahir', 'tanggal_ditambahkan' => '2026-09-07 10:00', 'aktif' => false],
            ['id' => 9, 'pencari_kerja_id' => 5, 'keahlian_id' => 5, 'level_klaim' => 'ahli', 'tanggal_ditambahkan' => '2026-09-02 19:30', 'aktif' => true],
        ]);

        $bukti = [
            ['id' => 1, 'pengguna_id' => 2, 'jenis_bukti_id' => 2, 'judul' => 'Sertifikat Kompetensi Associate Data Analyst', 'penerbit' => 'LSP Informatika Nusantara', 'url_berkas' => 'bukti/sertifikat-data-analyst-dimas.pdf', 'tanggal_terbit' => '2026-05-20', 'diunggah_pada' => '2026-09-03 10:12'],
            ['id' => 2, 'pengguna_id' => 2, 'jenis_bukti_id' => 1, 'judul' => 'Pelatihan Dashboard dengan Looker Studio', 'penerbit' => 'BLK Surabaya', 'url_berkas' => 'bukti/pelatihan-dashboard-dimas.pdf', 'tanggal_terbit' => '2026-07-11', 'diunggah_pada' => '2026-09-04 08:40'],
            ['id' => 3, 'pengguna_id' => 3, 'jenis_bukti_id' => 3, 'judul' => 'Uji Kompetensi Junior Web Developer', 'penerbit' => 'LSP Informatika Nusantara', 'url_berkas' => 'bukti/ujikom-web-salsa.pdf', 'tanggal_terbit' => '2026-06-02', 'diunggah_pada' => '2026-09-05 13:05'],
            ['id' => 4, 'pengguna_id' => 4, 'jenis_bukti_id' => 2, 'judul' => 'Sertifikat Operator CNC Madya', 'penerbit' => 'BNSP', 'url_berkas' => 'bukti/cnc-madya-yohanes.pdf', 'tanggal_terbit' => '2025-11-14', 'diunggah_pada' => '2026-09-06 09:21'],
            ['id' => 5, 'pengguna_id' => 7, 'jenis_bukti_id' => 1, 'judul' => 'Google UX Design Certificate', 'penerbit' => 'Coursera', 'url_berkas' => 'bukti/ux-maya.pdf', 'tanggal_terbit' => '2025-03-30', 'diunggah_pada' => '2026-09-02 19:47'],
            ['id' => 6, 'pengguna_id' => 3, 'jenis_bukti_id' => 1, 'judul' => 'Pelatihan MySQL untuk Pengembang Web', 'penerbit' => 'Dicoding Indonesia', 'url_berkas' => 'bukti/mysql-salsa.pdf', 'tanggal_terbit' => '2026-08-18', 'diunggah_pada' => '2026-09-14 21:03'],
        ];
        DB::table('bukti')->insert($bukti);

        // Berkas PDF contoh supaya pratinjau di halaman verifikator bisa dibuka.
        $pemilik = DB::table('pengguna')->pluck('nama', 'id');
        foreach ($bukti as $b) {
            Storage::disk('local')->put($b['url_berkas'], $this->pdfContoh($b['penerbit'], $b['judul'], $pemilik[$b['pengguna_id']], $b['tanggal_terbit']));
        }

        DB::table('verifikasi')->insert([
            ['id' => 1, 'bukti_id' => 1, 'verifikator_id' => 1, 'keputusan' => 'disetujui', 'catatan' => 'Nomor sertifikat cocok dengan registri LSP.', 'diverifikasi_pada' => '2026-09-04 14:00', 'berlaku_sampai' => '2029-05-20', 'dibuat_pada' => '2026-09-03 10:15'],
            ['id' => 2, 'bukti_id' => 3, 'verifikator_id' => 1, 'keputusan' => 'disetujui', 'catatan' => 'Nilai praktik Laravel memenuhi standar.', 'diverifikasi_pada' => '2026-09-06 10:30', 'berlaku_sampai' => '2029-06-02', 'dibuat_pada' => '2026-09-05 13:10'],
            ['id' => 3, 'bukti_id' => 4, 'verifikator_id' => 3, 'keputusan' => 'disetujui', 'catatan' => null, 'diverifikasi_pada' => '2026-09-07 08:00', 'berlaku_sampai' => '2028-11-14', 'dibuat_pada' => '2026-09-06 09:25'],
            // Sertifikat UI/UX milik Maya diperiksa Dr. Hendra (verifikator 1), bukan oleh
            // Maya sendiri (verifikator 2). Aturan itu ditegakkan controller sekaligus
            // trigger trg_verifikasi_bukan_milik_sendiri di database.
            ['id' => 4, 'bukti_id' => 5, 'verifikator_id' => 1, 'keputusan' => 'ditolak', 'catatan' => 'Nama pada sertifikat berbeda dengan nama akun. Unggah ulang dengan sertifikat atas nama sendiri.', 'diverifikasi_pada' => '2026-09-03 09:00', 'berlaku_sampai' => null, 'dibuat_pada' => '2026-09-02 19:50'],
            ['id' => 5, 'bukti_id' => 2, 'verifikator_id' => 1, 'keputusan' => 'menunggu', 'catatan' => null, 'diverifikasi_pada' => null, 'berlaku_sampai' => null, 'dibuat_pada' => '2026-09-04 08:45'],
        ]);

        DB::table('bukti_keahlian')->insert([
            ['verifikasi_id' => 1, 'klaim_keahlian_id' => 1],
            ['verifikasi_id' => 1, 'klaim_keahlian_id' => 2],
            ['verifikasi_id' => 2, 'klaim_keahlian_id' => 4],
            ['verifikasi_id' => 3, 'klaim_keahlian_id' => 6],
        ]);

        DB::table('lowongan')->insert([
            ['id' => 1, 'perusahaan_id' => 1, 'kode' => 'NDS-2026-001', 'posisi' => 'Junior Data Analyst', 'deskripsi' => "Mengolah data transaksi klien ritel, menulis kueri SQL, dan menyusun dashboard mingguan untuk tim penjualan.\n\nTidak ada syarat ijazah. Kami menilai dari keahlian yang bisa kamu tunjukkan.", 'lokasi' => 'Surabaya', 'tipe_pekerjaan' => 'penuh_waktu', 'status' => 'dipublikasikan', 'dipublikasikan_pada' => '2026-09-08 09:00', 'ditutup_pada' => null, 'dibuat_pada' => '2026-09-07 16:00'],
            ['id' => 2, 'perusahaan_id' => 2, 'kode' => 'ARD-2026-004', 'posisi' => 'Backend Developer Laravel', 'deskripsi' => 'Mengembangkan API dan panel admin untuk aplikasi klien dengan Laravel dan MySQL. Kontrak 12 bulan, bisa diperpanjang.', 'lokasi' => 'Surabaya (Hybrid)', 'tipe_pekerjaan' => 'kontrak', 'status' => 'dipublikasikan', 'dipublikasikan_pada' => '2026-09-09 10:00', 'ditutup_pada' => null, 'dibuat_pada' => '2026-09-09 08:30'],
            ['id' => 3, 'perusahaan_id' => 2, 'kode' => 'ARD-2026-005', 'posisi' => 'Magang UI/UX Designer', 'deskripsi' => 'Membantu tim produk menyusun wireframe dan prototipe.', 'lokasi' => 'Remote', 'tipe_pekerjaan' => 'magang', 'status' => 'draft', 'dipublikasikan_pada' => null, 'ditutup_pada' => null, 'dibuat_pada' => '2026-09-10 11:00'],
            ['id' => 4, 'perusahaan_id' => 1, 'kode' => 'NDS-2026-000', 'posisi' => 'Data Entry Freelance', 'deskripsi' => 'Input data survei lapangan.', 'lokasi' => 'Remote', 'tipe_pekerjaan' => 'freelance', 'status' => 'ditutup', 'dipublikasikan_pada' => '2026-08-01 09:00', 'ditutup_pada' => '2026-08-31 17:00', 'dibuat_pada' => '2026-07-30 10:00'],
        ]);

        DB::table('syarat_keahlian')->insert([
            ['id' => 1, 'lowongan_id' => 1, 'keahlian_id' => 1, 'level_minimum' => 'menengah', 'sifat' => 'wajib', 'bobot' => 3.00, 'wajib_terverifikasi' => true],
            ['id' => 2, 'lowongan_id' => 1, 'keahlian_id' => 2, 'level_minimum' => 'pemula', 'sifat' => 'wajib', 'bobot' => 2.00, 'wajib_terverifikasi' => false],
            ['id' => 3, 'lowongan_id' => 1, 'keahlian_id' => 4, 'level_minimum' => 'menengah', 'sifat' => 'opsional', 'bobot' => 1.00, 'wajib_terverifikasi' => true],
            ['id' => 4, 'lowongan_id' => 2, 'keahlian_id' => 3, 'level_minimum' => 'mahir', 'sifat' => 'wajib', 'bobot' => 3.00, 'wajib_terverifikasi' => true],
            ['id' => 5, 'lowongan_id' => 2, 'keahlian_id' => 1, 'level_minimum' => 'menengah', 'sifat' => 'wajib', 'bobot' => 2.00, 'wajib_terverifikasi' => false],
            ['id' => 6, 'lowongan_id' => 3, 'keahlian_id' => 5, 'level_minimum' => 'pemula', 'sifat' => 'wajib', 'bobot' => 1.00, 'wajib_terverifikasi' => false],
        ]);

        DB::table('lamaran')->insert([
            ['id' => 1, 'lowongan_id' => 1, 'pencari_kerja_id' => 1, 'status' => 'diproses', 'skor_kecocokan' => 83.33, 'dilamar_pada' => '2026-09-09 09:14'],
            ['id' => 2, 'lowongan_id' => 1, 'pencari_kerja_id' => 2, 'status' => 'ditinjau', 'skor_kecocokan' => 0.00, 'dilamar_pada' => '2026-09-10 16:02'],
            ['id' => 3, 'lowongan_id' => 2, 'pencari_kerja_id' => 2, 'status' => 'diterima', 'skor_kecocokan' => 100.00, 'dilamar_pada' => '2026-09-09 20:31'],
        ]);

        DB::table('tahap_seleksi')->insert([
            ['id' => 1, 'lamaran_id' => 1, 'nama_tahap' => 'Seleksi berkas', 'urutan' => 1, 'status' => 'lulus', 'jadwal' => '2026-09-10 09:00', 'catatan' => 'Klaim SQL sudah terverifikasi.'],
            ['id' => 2, 'lamaran_id' => 1, 'nama_tahap' => 'Tes studi kasus SQL', 'urutan' => 2, 'status' => 'berlangsung', 'jadwal' => '2026-09-16 10:00', 'catatan' => null],
            ['id' => 3, 'lamaran_id' => 1, 'nama_tahap' => 'Wawancara user', 'urutan' => 3, 'status' => 'menunggu', 'jadwal' => null, 'catatan' => null],
            ['id' => 4, 'lamaran_id' => 3, 'nama_tahap' => 'Seleksi berkas', 'urutan' => 1, 'status' => 'lulus', 'jadwal' => '2026-09-10 13:00', 'catatan' => null],
            ['id' => 5, 'lamaran_id' => 3, 'nama_tahap' => 'Live coding', 'urutan' => 2, 'status' => 'lulus', 'jadwal' => '2026-09-12 14:00', 'catatan' => 'Membuat CRUD API dalam 60 menit.'],
            ['id' => 6, 'lamaran_id' => 3, 'nama_tahap' => 'Wawancara HR', 'urutan' => 3, 'status' => 'lulus', 'jadwal' => '2026-09-14 10:00', 'catatan' => 'Ditawari kontrak 12 bulan.'],
        ]);
    }

    /** PDF satu halaman tanpa library, cukup untuk pratinjau di browser. */
    private function pdfContoh(string $penerbit, string $judul, string $pemilik, string $tanggal): string
    {
        $aman = fn ($s) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
        $baris = [
            ['F1', 11, 72, 690, strtoupper($penerbit)],
            ['F2', 32, 72, 630, 'SERTIFIKAT'],
            ['F1', 12, 72, 585, 'diberikan kepada'],
            ['F2', 22, 72, 552, $pemilik],
            ['F1', 13, 72, 512, $judul],
            ['F1', 11, 72, 470, 'Terbit ' . $tanggal],
            ['F1', 8, 72, 96, 'Berkas contoh untuk demo SIBUKER-PT. Bukan dokumen resmi.'],
        ];

        $isi = "0.19 0.2 0.79 RG 3 w 48 72 499 680 re S\n";
        foreach ($baris as [$font, $ukuran, $x, $y, $teks]) {
            $isi .= "BT /{$font} {$ukuran} Tf {$x} {$y} Td (" . $aman($teks) . ") Tj ET\n";
        }

        $objek = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
            '<< /Length ' . strlen($isi) . " >>\nstream\n" . $isi . 'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $posisi = [];
        foreach ($objek as $i => $o) {
            $posisi[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$o}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objek) + 1) . "\n0000000000 65535 f \n";
        foreach ($posisi as $p) {
            $pdf .= sprintf("%010d 00000 n \n", $p);
        }

        return $pdf . "trailer\n<< /Size " . (count($objek) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }
}
