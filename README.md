# SIBUKER-PT

Bursa kerja berbasis keahlian terverifikasi. Tugas UTS Basis Data, Kelompok F SD-A2, S1 Teknologi Sains Data, Universitas Airlangga.

Laravel 13 + Blade + Eloquent, MySQL 8.4. Skema mengikuti `SIBUKER_PT.sql`: 15 tabel dan view `v_status_keahlian` untuk centang biru.

## Menjalankan

Butuh PHP 8.3+, Composer, dan MySQL (Laragon cukup).

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database `sibuker_pt` (utf8mb4), sesuaikan `DB_*` di `.env`, lalu:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000.

## Akun demo

Kata sandi semua akun: `password`.

| Peran | Email |
|---|---|
| Pencari kerja | dimas@mail.test |
| Perusahaan | hr@nusantaradata.test |
| Verifikator | hendra@lsp-inf.test |
| Administrator | admin@sibuker.test |
| Pencari kerja + verifikator | maya@praktisi.test |

Data demo fiktif. Seeder juga membuat PDF sertifikat contoh di `storage/app/private/bukti`.

## Struktur

| Bagian | Lokasi |
|---|---|
| Migration (satu file per modul + view) | `database/migrations` |
| Skema fisik lengkap dalam satu berkas SQL | `database/SIBUKER_PT.sql` |
| Uji kelayakan basis data | `database/uji_kelayakan.php` |
| Benchmark query pada data volume besar | `database/benchmark` |
| Model Eloquent, satu per tabel | `app/Models` |
| Skor kecocokan | `app/Support/Kecocokan.php` |
| Middleware peran (`peran:pencari`, dst.) | `app/Http/Middleware/CekPeran.php` |
| Controller per peran | `app/Http/Controllers/{Pencari,Perusahaan,Verifikator,Admin}` |
| Tampilan dan CSS | `resources/views`, `public/css/sibuker.css` |

Status centang biru tidak disimpan di tabel. Aturan "verifikasi terbaru yang masih sah"
ditulis satu kali di view `v_verifikasi_terbaru`; `v_status_keahlian` dan
`Bukti::denganStatus()` sama-sama membacanya dari sana, sehingga aplikasi dan basis data
tidak bisa berbeda pendapat soal apakah sebuah keahlian masih terverifikasi.

Empat aturan yang tidak dapat dinyatakan foreign key maupun CHECK dijaga trigger:
sebuah sertifikat hanya boleh membuktikan keahlian milik pemiliknya sendiri, hanya
verifikasi berkeputusan disetujui yang boleh menautkan keahlian, dan seorang verifikator
tidak boleh memeriksa sertifikat miliknya sendiri.

## Test

Test memakai database MySQL terpisah, `sibuker_pt_test`, karena skema punya view, CHECK
constraint, dan trigger yang tidak didukung SQLite.

```bash
php artisan test
```

## Uji kelayakan basis data

Memeriksa struktur skema, integritas referensial, penegakan seluruh constraint dan
trigger, konsistensi data, pemakaian indeks, dan bentuk normal. Pengujian yang menulis
dibungkus transaksi dan dibatalkan kembali, jadi aman dijalankan pada database berisi.

```bash
php database/uji_kelayakan.php
```

## Benchmark

Mengukur pengaruh indeks dan bentuk view pada volume data besar (25.000 lowongan,
75.000 lamaran, 45.000 verifikasi). Buat database `sibuker_pt_bench` lebih dulu, jalankan
migration ke sana, lalu:

```bash
php database/benchmark/isi_data_uji.php
```

```bash
php database/benchmark/ukur_query.php baru
```
