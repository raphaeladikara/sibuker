# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Laravel + Blade + Eloquent, MySQL 8.4 (Laragon). Berkas sertifikat disimpan di Laravel File Storage. Tidak memakai Node/Vite; CSS dan JS ditulis langsung di `public/`.

## Users

- **Pencari kerja**: lulusan vokasi, pekerja berpengalaman tanpa ijazah tinggi, atau siapa pun yang ingin dinilai dari keahlian. Mengisi profil, mencantumkan keahlian, mengunggah sertifikat PDF (opsional), lalu melamar.
- **Perusahaan**: tim HR yang membuka lowongan dan menentukan keahlian mana yang wajib, opsional, dan harus terverifikasi.
- **Verifikator**: asesor LSP, instruktur BLK, atau praktisi yang memeriksa keaslian sertifikat sesuai bidang kewenangannya.
- **Administrator**: menjaga data master (keahlian, jenis bukti) dan status akun.

## Product Purpose

Bursa kerja yang menilai kandidat dari keahlian yang terbukti, bukan hanya ijazah. Tugas UTS mata kuliah Basis Data, Kelompok F SD-A2, S1 Teknologi Sains Data, Universitas Airlangga. Mendukung SDG 8.

## Positioning

Centang biru melekat pada pasangan pencari kerja + keahlian, bukan pada profil. Statusnya dihitung dari hasil verifikasi terbaru yang masih berlaku (view `v_status_keahlian`), dan perusahaan memilih per syarat apakah centang biru itu wajib.

## Capabilities and Constraints

- Skema mengikuti `../SIBUKER_PT.sql` (15 tabel + 1 view). Nama tabel dan kolom berbahasa Indonesia dan tidak boleh diubah.
- Skor kecocokan = bobot syarat terpenuhi / total bobot x 100, disimpan saat melamar.
- Akun verifikator dibuat admin, tidak lewat pendaftaran.
- Satu akun bisa punya lebih dari satu peran.

## Brand Commitments

- Nama: SIBUKER.
- Referensi visual dari pengguna (2026-09-15): dua shot Dribbble "FINDIT" job board. Nav putih dengan penanda aktif garis indigo, strip pencarian bersegmen dengan tombol indigo penuh, kolom filter kiri, kartu lowongan putih dengan tag pastel, satu kartu unggulan indigo pekat. Pengguna minta tampilan yang tidak terasa buatan AI.

## Evidence on Hand

Data demo di `database/seeders/DatabaseSeeder.php` (nama orang, perusahaan, sertifikat) adalah fiktif untuk keperluan tugas. Tidak ada logo perusahaan, foto, atau testimoni asli.

## Product Principles

- Keahlian tanpa sertifikat tetap sah ditampilkan; centang biru hanya menambah kepercayaan.
- Tunjukkan alasan, bukan cuma skor: setiap syarat yang gagal punya penjelasan.
- Hak akses mengikuti kepemilikan data; tidak ada peran yang melihat data milik orang lain tanpa alasan.
