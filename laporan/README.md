# Laporan SIBUKER-PT

Berkas laporan: **`Laporan-SIBUKER-PT-Kelompok-F.docx`** (69 halaman, 34 gambar, 34 tabel).

Laporan ini dibangkitkan, bukan diketik manual. Seluruh tabel yang menggambarkan struktur
basis data (kamus data, daftar foreign key, indeks, dan constraint) dan seluruh diagram ERD
dibaca langsung dari database `sibuker_pt` yang berjalan. Karena itu isi laporan tidak dapat
berbeda dari basis data yang sebenarnya diimplementasikan.

## Setelah membuka di Microsoft Word

Daftar isi, daftar gambar, dan daftar tabel memakai field Word. Nomor halamannya sudah terisi,
tetapi bila ada perubahan isi, tekan **Ctrl+A lalu F9** untuk memperbaruinya.

## Isi folder

| Berkas | Keterangan |
|---|---|
| `Laporan-SIBUKER-PT-Kelompok-F.docx` | Laporan siap kumpul |
| `alat/buat_laporan.cjs` | Penyusun dokumen .docx |
| `alat/konten.cjs` | Seluruh teks naratif dan pemetaan aksi antarmuka ke tabel |
| `alat/buat_erd.php` | Membangkitkan sumber diagram Mermaid dari information_schema |
| `alat/ekspor_skema.php` | Mengekspor struktur basis data ke `skema.json` |
| `alat/potret_ui.cjs` | Mengambil 27 tangkapan layar antarmuka dengan Chrome |
| `sumber-erd/*.mmd` | Sumber diagram Mermaid |
| `gambar/erd/*.png` | Diagram hasil render |
| `gambar/ui/*.png` | Tangkapan layar antarmuka |
| `skema.json` | Struktur basis data hasil ekspor |

## Membangun ulang

Butuh MySQL berjalan dengan database `sibuker_pt` sudah di-seed, aplikasi berjalan di
`http://127.0.0.1:8000`, serta paket npm `docx` dan `puppeteer-core`.

```bash
php laporan/alat/ekspor_skema.php
```

```bash
php laporan/alat/buat_erd.php
```

Diagram Mermaid di `sumber-erd/` dirender menjadi PNG ke `gambar/erd/`. Tangkapan layar
antarmuka diperbarui dengan:

```bash
node laporan/alat/potret_ui.cjs
```

Terakhir, susun dokumennya:

```bash
node laporan/alat/buat_laporan.cjs
```

## Catatan

Tangkapan layar diambil memakai Chrome yang terpasang di sistem dengan direktori profil
sementara, sehingga sesi login Chrome milik pengguna tidak tersentuh.
