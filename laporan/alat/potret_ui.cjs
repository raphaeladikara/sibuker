/**
 * Mengambil tangkapan layar seluruh halaman SIBUKER-PT untuk lampiran laporan.
 *
 * Dijalankan dengan Chrome yang sudah terpasang di sistem, memakai direktori profil
 * sementara sehingga sesi login Chrome milik pengguna tidak tersentuh sama sekali.
 *
 *   node database/erd/potret_ui.js [urlDasar] [folderTujuan]
 *
 * Aplikasi harus sudah berjalan (php artisan serve) dan database sudah di-seed.
 */
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const os = require('os');
const path = require('path');

const DASAR = process.argv[2] || 'http://127.0.0.1:8000';
const TUJUAN = process.argv[3] || path.join(__dirname, '..', 'gambar', 'ui');
const CHROME = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const LEBAR = 1280;
const TINGGI = 900;
const TINGGI_MAKS = 2000; // halaman yang lebih panjang dipotong agar proporsional di laporan

/** Daftar halaman per peran. Nama berkas dipakai langsung oleh pembangun dokumen. */
const RENCANA = [
  {
    peran: 'Publik (tanpa login)',
    akun: null,
    halaman: [
      ['publik-beranda', '/'],
      ['publik-lowongan', '/lowongan'],
      ['publik-lowongan-detail', '/lowongan/1'],
      ['publik-daftar', '/daftar'],
      ['publik-masuk', '/masuk'],
    ],
  },
  {
    peran: 'Pencari kerja',
    akun: 'dimas@mail.test',
    halaman: [
      ['pencari-dashboard', '/pencari/dashboard'],
      ['pencari-profil', '/pencari/profil'],
      ['pencari-keahlian', '/pencari/keahlian'],
      ['pencari-keahlian-form', '/pencari/keahlian/create'],
      ['pencari-sertifikat', '/pencari/sertifikat'],
      ['pencari-sertifikat-form', '/pencari/sertifikat/create'],
      ['pencari-cari-lowongan', '/pencari/lowongan'],
      ['pencari-lamaran', '/pencari/lamaran'],
      ['pencari-lamaran-detail', '/pencari/lamaran/1'],
    ],
  },
  {
    peran: 'Perusahaan',
    akun: 'hr@nusantaradata.test',
    halaman: [
      ['perusahaan-dashboard', '/perusahaan/dashboard'],
      ['perusahaan-lowongan', '/perusahaan/lowongan'],
      ['perusahaan-lowongan-form', '/perusahaan/lowongan/1/edit'],
      ['perusahaan-pelamar', '/perusahaan/lowongan/1/pelamar'],
      ['perusahaan-pelamar-detail', '/perusahaan/pelamar/1'],
    ],
  },
  {
    peran: 'Verifikator',
    akun: 'hendra@lsp-inf.test',
    halaman: [
      ['verifikator-dashboard', '/verifikator/dashboard'],
      ['verifikator-periksa', '/verifikator/periksa/2'],
      ['verifikator-riwayat', '/verifikator/riwayat'],
      ['verifikator-kewenangan', '/verifikator/kewenangan'],
    ],
  },
  {
    peran: 'Administrator',
    akun: 'admin@sibuker.test',
    halaman: [
      ['admin-dashboard', '/admin/dashboard'],
      ['admin-pengguna', '/admin/pengguna'],
      ['admin-verifikator-form', '/admin/verifikator/1/edit'],
      ['admin-keahlian', '/admin/keahlian'],
    ],
  },
];

(async () => {
  fs.mkdirSync(TUJUAN, { recursive: true });
  const profil = fs.mkdtempSync(path.join(os.tmpdir(), 'sibuker-potret-'));

  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    userDataDir: profil,
    defaultViewport: { width: LEBAR, height: TINGGI, deviceScaleFactor: 2 },
    args: ['--hide-scrollbars', '--force-device-scale-factor=2'],
  });

  const gagal = [];
  let berhasil = 0;

  for (const bagian of RENCANA) {
    const konteks = await browser.createBrowserContext();
    const page = await konteks.newPage();
    await page.setViewport({ width: LEBAR, height: TINGGI, deviceScaleFactor: 2 });

    if (bagian.akun) {
      await page.goto(DASAR + '/masuk', { waitUntil: 'networkidle2' });
      await page.type('input[type=email]', bagian.akun);
      await page.type('input[type=password]', 'password');
      // Tombol kirim pada form masuk tidak memakai type=submit (bawaan <button> di
      // dalam <form> sudah submit), jadi tombol dicari di dalam form itu sendiri.
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle2' }),
        page.click('#form-masuk button:not([type=button])'),
      ]);
      if (page.url().includes('/masuk')) {
        gagal.push(`${bagian.peran}: gagal masuk sebagai ${bagian.akun} (berhenti di ${page.url()})`);
        await konteks.close();
        continue;
      }
    }

    for (const [nama, jalur] of bagian.halaman) {
      try {
        const res = await page.goto(DASAR + jalur, { waitUntil: 'networkidle2' });
        if (res && res.status() >= 400) {
          gagal.push(`${nama}: HTTP ${res.status()} pada ${jalur}`);
          continue;
        }
        // Beri waktu bagi font dan gambar agar tidak terpotret setengah jadi.
        await page.evaluate(() => document.fonts && document.fonts.ready);
        await new Promise((r) => setTimeout(r, 350));

        const tinggiHalaman = await page.evaluate(() =>
          Math.max(document.body.scrollHeight, document.documentElement.scrollHeight)
        );
        const tinggiPotret = Math.min(tinggiHalaman, TINGGI_MAKS);

        await page.screenshot({
          path: path.join(TUJUAN, nama + '.png'),
          clip: { x: 0, y: 0, width: LEBAR, height: tinggiPotret },
        });
        berhasil++;
        console.log(`  ${nama}  ${LEBAR}x${tinggiPotret}${tinggiHalaman > TINGGI_MAKS ? ' (dipotong)' : ''}`);
      } catch (e) {
        gagal.push(`${nama}: ${e.message}`);
      }
    }
    await konteks.close();
  }

  await browser.close();
  fs.rmSync(profil, { recursive: true, force: true });

  console.log(`\n${berhasil} tangkapan layar tersimpan di ${TUJUAN}`);
  if (gagal.length) {
    console.log('\nGAGAL:');
    gagal.forEach((g) => console.log('  ' + g));
    process.exit(1);
  }
})();
