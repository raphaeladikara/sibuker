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
| Model Eloquent, satu per tabel | `app/Models` |
| Skor kecocokan | `app/Support/Kecocokan.php` |
| Middleware peran (`peran:pencari`, dst.) | `app/Http/Middleware/CekPeran.php` |
| Controller per peran | `app/Http/Controllers/{Pencari,Perusahaan,Verifikator,Admin}` |
| Tampilan dan CSS | `resources/views`, `public/css/sibuker.css` |

Status centang biru tidak disimpan di tabel. Scope `KlaimKeahlian::denganStatus()` membacanya dari `v_status_keahlian`, dan `Bukti::denganStatus()` mengambil keputusan dari baris `verifikasi` terbaru.

## Test

Test memakai database MySQL terpisah, `sibuker_pt_test`, karena skema punya view dan CHECK constraint yang tidak didukung SQLite.

```bash
php artisan test
```
