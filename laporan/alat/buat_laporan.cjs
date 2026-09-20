/**
 * Membangun berkas .docx laporan SIBUKER-PT.
 *
 *   NODE_PATH=<lokasi node_modules> node laporan/alat/buat_laporan.cjs
 *
 * Teks naratif diambil dari konten.cjs, sedangkan seluruh tabel yang menggambarkan
 * struktur basis data (kamus data, foreign key, indeks, constraint) dibangkitkan dari
 * laporan/skema.json. Dengan begitu isi laporan tidak dapat berbeda dari basis data
 * yang sebenarnya berjalan.
 */
const fs = require('fs');
const path = require('path');
const D = require('docx');
const K = require('./konten.cjs');

const AKAR = path.join(__dirname, '..');
const GBR_ERD = path.join(AKAR, 'gambar', 'erd');
const GBR_UI = path.join(AKAR, 'gambar', 'ui');
const skema = JSON.parse(fs.readFileSync(path.join(AKAR, 'skema.json'), 'utf8'));
const KELUARAN = path.join(AKAR, 'Laporan-SIBUKER-PT-Kelompok-F.docx');

// --- ukuran halaman A4 dengan margin skripsi (kiri 4 cm, sisanya 3 cm) ------
const A4_L = 11906;
const A4_T = 16838;
const M_KIRI = 2268;
const M_LAIN = 1701;
const LEBAR_ISI = A4_L - M_KIRI - M_LAIN;          // 7937 DXA
const LEBAR_ISI_PX = Math.floor((LEBAR_ISI / 1440) * 96);
const LEBAR_LANDSCAPE = A4_T - M_LAIN * 2;
const LEBAR_LANDSCAPE_PX = Math.floor((LEBAR_LANDSCAPE / 1440) * 96);

// Tinggi area cetak. Dipakai membatasi tinggi gambar agar tidak meluap ke halaman
// berikutnya. Disisakan ruang untuk baris keterangan gambar.
const TINGGI_ISI_PX = Math.floor(((A4_T - M_LAIN * 2) / 1440) * 96) - 150;
const TINGGI_LANDSCAPE_PX = Math.floor(((A4_L - M_LAIN * 2) / 1440) * 96) - 150;

const FONT = 'Times New Roman';

let noGambar = 0;
let noTabel = 0;

// Kumpulan keterangan gambar dan tabel, dipakai menyusun daftar gambar dan daftar
// tabel setelah seluruh badan laporan selesai dibentuk.
const daftarGambar = [];
const daftarTabel = [];

// --------------------------------------------------------------------------
// Pembantu
// --------------------------------------------------------------------------

/** Membaca lebar dan tinggi PNG dari header IHDR-nya. */
function ukuranPng(berkas) {
  const b = fs.readFileSync(berkas);
  if (b.length < 24 || b.toString('ascii', 12, 16) !== 'IHDR') {
    throw new Error('bukan PNG yang valid: ' + berkas);
  }
  return { lebar: b.readUInt32BE(16), tinggi: b.readUInt32BE(20), data: b };
}

function teks(isi, opsi = {}) {
  return new D.TextRun({ text: isi, font: FONT, size: opsi.size || 24, bold: opsi.bold, italics: opsi.italics });
}

/** Paragraf isi, rata kiri-kanan, spasi 1,5. */
function p(isi, opsi = {}) {
  return new D.Paragraph({
    alignment: opsi.alignment || D.AlignmentType.JUSTIFIED,
    spacing: { line: 360, after: opsi.after === undefined ? 120 : opsi.after },
    indent: opsi.indent,
    children: Array.isArray(isi) ? isi : [teks(isi, opsi)],
  });
}

/**
 * Judul bab ditulis dua baris, tetapi tetap dalam SATU paragraf berstyle Heading 1.
 * Bila dipecah menjadi dua paragraf, entri daftar isi hanya akan memuat "BAB I"
 * tanpa nama babnya, karena daftar isi mengambil teks per paragraf.
 */
function judulBab(nomor, nama) {
  return [
    new D.Paragraph({
      heading: D.HeadingLevel.HEADING_1,
      alignment: D.AlignmentType.CENTER,
      pageBreakBefore: true,
      spacing: { after: 360, line: 300 },
      children: [
        teks('BAB ' + nomor, { bold: true, size: 28 }),
        new D.TextRun({ text: ' ' + nama, font: FONT, size: 28, bold: true, break: 1 }),
      ],
    }),
  ];
}

function h2(nomor, nama) {
  return new D.Paragraph({
    heading: D.HeadingLevel.HEADING_2,
    spacing: { before: 280, after: 140 },
    children: [teks(nomor + '  ' + nama, { bold: true, size: 26 })],
  });
}

function h3(nomor, nama) {
  return new D.Paragraph({
    heading: D.HeadingLevel.HEADING_3,
    spacing: { before: 240, after: 120 },
    children: [teks(nomor + '  ' + nama, { bold: true, size: 24 })],
  });
}

function butir(daftar, bernomor = false) {
  return daftar.map(
    (t) =>
      new D.Paragraph({
        alignment: D.AlignmentType.JUSTIFIED,
        spacing: { line: 360, after: 80 },
        numbering: bernomor ? { reference: 'daftar-angka', level: 0 } : undefined,
        bullet: bernomor ? undefined : { level: 0 },
        children: [teks(t)],
      })
  );
}

/** Menyisipkan gambar beserta keterangannya, sekaligus menaikkan nomor gambar. */
function gambar(berkas, keterangan, lebarMaksPx = LEBAR_ISI_PX, tinggiMaksPx = TINGGI_ISI_PX) {
  const { lebar, tinggi, data } = ukuranPng(berkas);
  // Skala dibatasi lebar DAN tinggi. Tanpa batas tinggi, gambar yang tinggi akan
  // meluap ke halaman berikutnya dan meninggalkan halaman yang nyaris kosong.
  const skala = Math.min(1, lebarMaksPx / lebar, tinggiMaksPx / tinggi);
  noGambar++;
  daftarGambar.push({ no: noGambar, teks: 'Gambar 3.' + noGambar + '  ' + keterangan });
  return [
    new D.Paragraph({
      alignment: D.AlignmentType.CENTER,
      spacing: { before: 200, after: 80 },
      keepNext: true,
      children: [
        new D.ImageRun({
          data,
          type: 'png',
          transformation: { width: Math.round(lebar * skala), height: Math.round(tinggi * skala) },
        }),
      ],
    }),
    new D.Paragraph({
      style: 'CaptionGambar',
      alignment: D.AlignmentType.CENTER,
      spacing: { after: 240 },
      children: [
        new D.Bookmark({
          id: 'gbr' + noGambar,
          children: [teks('Gambar 3.' + noGambar + '  ' + keterangan, { size: 20 })],
        }),
      ],
    }),
  ];
}

function sel(isi, opsi = {}) {
  return new D.TableCell({
    width: { size: opsi.lebar, type: D.WidthType.DXA },
    shading: opsi.kepala ? { type: D.ShadingType.CLEAR, fill: 'E8EFFA' } : undefined,
    margins: { top: 60, bottom: 60, left: 90, right: 90 },
    verticalAlign: D.VerticalAlign.TOP,
    children: String(isi)
      .split('\n')
      .map(
        (baris) =>
          new D.Paragraph({
            alignment: opsi.kepala ? D.AlignmentType.CENTER : D.AlignmentType.LEFT,
            spacing: { line: 240, after: 0 },
            children: [teks(baris, { size: opsi.size || 19, bold: opsi.kepala })],
          })
      ),
  });
}

/** Menyisipkan tabel beserta keterangannya, sekaligus menaikkan nomor tabel. */
function tabel(lebarKolom, kepala, baris, keterangan, opsi = {}) {
  noTabel++;
  daftarTabel.push({ no: noTabel, teks: 'Tabel 3.' + noTabel + '  ' + keterangan });
  const total = lebarKolom.reduce((a, b) => a + b, 0);
  if (Math.abs(total - LEBAR_ISI) > 2) {
    throw new Error(`lebar kolom tabel "${keterangan}" berjumlah ${total}, seharusnya ${LEBAR_ISI}`);
  }
  return [
    new D.Paragraph({
      style: 'CaptionTabel',
      alignment: D.AlignmentType.LEFT,
      spacing: { before: 240, after: 80 },
      children: [
        new D.Bookmark({
          id: 'tbl' + noTabel,
          children: [teks('Tabel 3.' + noTabel + '  ' + keterangan, { size: 20 })],
        }),
      ],
    }),
    new D.Table({
      columnWidths: lebarKolom,
      width: { size: LEBAR_ISI, type: D.WidthType.DXA },
      layout: D.TableLayoutType.FIXED,
      rows: [
        new D.TableRow({
          tableHeader: true,
          children: kepala.map((t, i) => sel(t, { lebar: lebarKolom[i], kepala: true, size: opsi.size })),
        }),
        ...baris.map(
          (r) =>
            new D.TableRow({
              children: r.map((t, i) => sel(t, { lebar: lebarKolom[i], size: opsi.size })),
            })
        ),
      ],
    }),
    new D.Paragraph({ spacing: { after: 200 }, children: [teks('')] }),
  ];
}

/** Blok kode SQL dengan latar abu dan huruf monospace. */
function kode(barisSql) {
  return new D.Table({
    columnWidths: [LEBAR_ISI],
    width: { size: LEBAR_ISI, type: D.WidthType.DXA },
    layout: D.TableLayoutType.FIXED,
    rows: [
      new D.TableRow({
        children: [
          new D.TableCell({
            width: { size: LEBAR_ISI, type: D.WidthType.DXA },
            shading: { type: D.ShadingType.CLEAR, fill: 'F4F6F8' },
            margins: { top: 120, bottom: 120, left: 140, right: 140 },
            children: barisSql.map(
              (b) =>
                new D.Paragraph({
                  spacing: { line: 240, after: 0 },
                  children: [new D.TextRun({ text: b, font: 'Consolas', size: 17 })],
                })
            ),
          }),
        ],
      }),
    ],
  });
}

// --------------------------------------------------------------------------
// Halaman sampul
// --------------------------------------------------------------------------

function sampul() {
  const tengah = (t, opsi = {}) =>
    new D.Paragraph({
      alignment: D.AlignmentType.CENTER,
      spacing: { after: opsi.after === undefined ? 120 : opsi.after, line: 300 },
      children: [teks(t, opsi)],
    });

  const isi = [
    tengah(K.IDENTITAS.matkul, { bold: true, size: 26, after: 40 }),
    tengah(K.IDENTITAS.matkulNama, { bold: true, size: 26, after: 600 }),
    tengah(K.IDENTITAS.judul, { bold: true, size: 32, after: 80 }),
    tengah('(' + K.IDENTITAS.akronim + ')', { bold: true, size: 32, after: 700 }),
    tengah('Dosen Pengampu:', { size: 24, after: 40 }),
    ...K.IDENTITAS.dosen.map((d) => tengah(d, { size: 24, after: 500 })),
    tengah('Disusun oleh:', { size: 24, after: 40 }),
    tengah(K.IDENTITAS.kelompok, { bold: true, size: 24, after: 200 }),
  ];

  // Tabel anggota diletakkan di tengah dengan lebar lebih sempit dari badan teks.
  const lebarNama = 4400;
  const lebarNim = 2000;
  isi.push(
    new D.Table({
      columnWidths: [lebarNama, lebarNim],
      width: { size: lebarNama + lebarNim, type: D.WidthType.DXA },
      layout: D.TableLayoutType.FIXED,
      alignment: D.AlignmentType.CENTER,
      borders: {
        top: { style: D.BorderStyle.NONE }, bottom: { style: D.BorderStyle.NONE },
        left: { style: D.BorderStyle.NONE }, right: { style: D.BorderStyle.NONE },
        insideHorizontal: { style: D.BorderStyle.NONE }, insideVertical: { style: D.BorderStyle.NONE },
      },
      rows: K.IDENTITAS.anggota.map(
        ([nama, nim]) =>
          new D.TableRow({
            children: [
              new D.TableCell({
                width: { size: lebarNama, type: D.WidthType.DXA },
                margins: { top: 20, bottom: 20 },
                children: [new D.Paragraph({ spacing: { after: 0 }, children: [teks(nama, { size: 24 })] })],
              }),
              new D.TableCell({
                width: { size: lebarNim, type: D.WidthType.DXA },
                margins: { top: 20, bottom: 20 },
                children: [new D.Paragraph({ spacing: { after: 0 }, children: [teks(nim, { size: 24 })] })],
              }),
            ],
          })
      ),
    })
  );

  isi.push(new D.Paragraph({ spacing: { after: 700 }, children: [teks('')] }));
  K.IDENTITAS.institusi.forEach((b, i) =>
    isi.push(tengah(b, { bold: true, size: 26, after: i === K.IDENTITAS.institusi.length - 1 ? 0 : 60 }))
  );

  return isi;
}

// --------------------------------------------------------------------------
// Bagian depan: daftar isi, daftar gambar, daftar tabel
// --------------------------------------------------------------------------

/**
 * Satu baris pada daftar gambar atau daftar tabel: keterangan, titik-titik perata,
 * lalu nomor halaman berupa field PAGEREF yang menunjuk bookmark keterangan aslinya.
 * Cara ini tidak bergantung pada pencocokan nama style, berbeda dengan field TOC.
 */
function entriDaftar(keterangan, bookmark) {
  return new D.Paragraph({
    spacing: { line: 276, after: 60 },
    indent: { left: 560, hanging: 560 },
    children: [
      teks(keterangan, { size: 22 }),
      new D.TextRun({
        children: [
          new D.PositionalTab({
            alignment: D.PositionalTabAlignment.RIGHT,
            relativeTo: D.PositionalTabRelativeTo.MARGIN,
            leader: D.PositionalTabLeader.DOT,
          }),
        ],
      }),
      new D.SimpleField('PAGEREF ' + bookmark + ' \\h', '0'),
    ],
  });
}

function bagianDepan() {
  const judul = (t) =>
    new D.Paragraph({
      alignment: D.AlignmentType.CENTER,
      spacing: { after: 240 },
      pageBreakBefore: true,
      children: [teks(t, { bold: true, size: 28 })],
    });

  return [
    new D.Paragraph({
      alignment: D.AlignmentType.CENTER,
      spacing: { after: 240 },
      children: [teks('DAFTAR ISI', { bold: true, size: 28 })],
    }),
    new D.TableOfContents('Daftar Isi', { hyperlink: true, headingStyleRange: '1-3' }),

    judul('DAFTAR GAMBAR'),
    ...daftarGambar.map((g) => entriDaftar(g.teks, 'gbr' + g.no)),

    judul('DAFTAR TABEL'),
    ...daftarTabel.map((t) => entriDaftar(t.teks, 'tbl' + t.no)),

    new D.Paragraph({
      spacing: { before: 400 },
      alignment: D.AlignmentType.JUSTIFIED,
      children: [
        teks(
          'Catatan: daftar isi, daftar gambar, dan daftar tabel di atas dibuat sebagai field Microsoft Word. ' +
            'Tekan Ctrl+A lalu F9 pada Microsoft Word untuk mengisi nomor halamannya.',
          { italics: true, size: 20 }
        ),
      ],
    }),
  ];
}

// --------------------------------------------------------------------------
// BAB I dan BAB II
// --------------------------------------------------------------------------

function bab1() {
  return [
    ...judulBab('I', 'PENDAHULUAN'),
    h2('1.1', 'Latar Belakang'),
    ...K.BAB1.latar.map((t) => p(t)),
    h2('1.2', 'Mission Statement'),
    p([teks('"' + K.BAB1.mission + '"', { italics: true })]),
    h2('1.3', 'Mission Objective'),
    ...butir(K.BAB1.objectives, true),
  ];
}

function bab2() {
  const isi = [...judulBab('II', 'TINJAUAN PUSTAKA'), h2('2.1', 'Kajian Pustaka'), ...K.BAB2.kajian.map((t) => p(t)), h2('2.2', 'Landasan Teori')];
  K.BAB2.teori.forEach((t, i) => {
    isi.push(h3('2.2.' + (i + 1), t.judul));
    t.isi.forEach((x) => isi.push(p(x)));
    if (t.butir) isi.push(...butir(t.butir));
  });
  return isi;
}

// --------------------------------------------------------------------------
// BAB III
// --------------------------------------------------------------------------

function kamusData() {
  const isi = [
    h3('3.3.3', 'Kamus Data'),
    p(
      'Kamus data berikut memuat seluruh 15 tabel beserta ' +
        Object.values(skema.tabel).reduce((a, t) => a + t.kolom.length, 0) +
        ' kolomnya. Kolom Kunci berisi PRI untuk primary key, MUL untuk kolom yang menjadi awalan indeks, dan UNI untuk kolom bernilai unik. Isi tabel ini dibangkitkan langsung dari information_schema basis data yang berjalan, sehingga selalu sesuai dengan implementasinya.'
    ),
  ];

  const lebar = [1500, 1500, 620, 1100, 3217];
  Object.values(skema.tabel).forEach((t) => {
    isi.push(
      ...tabel(
        lebar,
        ['Kolom', 'Tipe data', 'Null', 'Bawaan', 'Keterangan'],
        t.kolom.map((k) => [k.nama, k.tipe, k.null, k.bawaan, k.keterangan]),
        'Kamus data tabel ' + t.nama,
        { size: 17 }
      )
    );
  });
  return isi;
}

function integritas() {
  const fkBaris = skema.fk.map((f) => [
    f.TABLE_NAME + '.' + f.COLUMN_NAME,
    f.REFERENCED_TABLE_NAME + '.' + f.REFERENCED_COLUMN_NAME,
    f.DELETE_RULE,
    f.DELETE_RULE === 'CASCADE'
      ? 'Data turunan ikut terhapus bersama induknya.'
      : 'Penghapusan induk ditolak selama masih dirujuk.',
  ]);

  const checkBaris = skema.check.map((c) => [
    c.CONSTRAINT_NAME,
    c.TABLE_NAME,
    c.CHECK_CLAUSE.replace(/_utf8mb4/g, '').replace(/`/g, ''),
  ]);

  return [
    h3('3.3.5', 'Integritas Referensial'),
    p(
      'Seluruh ' + skema.fk.length +
        ' foreign key memakai ON UPDATE CASCADE. Aturan ON DELETE dipilih berbeda-beda sesuai sifat datanya. Data turunan, yaitu data yang tidak bermakna tanpa induknya, memakai CASCADE. Data acuan yang dipakai bersama memakai RESTRICT, sehingga penghapusannya ditolak basis data selama masih dirujuk. Administrator menonaktifkan data acuan melalui kolom aktif alih-alih menghapusnya.'
    ),
    ...tabel(
      [2300, 2100, 1100, 2437],
      ['Kolom pada tabel anak', 'Merujuk ke', 'ON DELETE', 'Konsekuensi'],
      fkBaris,
      'Daftar foreign key beserta aturan penghapusannya',
      { size: 17 }
    ),

    h3('3.3.6', 'Aturan Integritas Tambahan'),
    p(
      'Foreign key hanya mampu menyatakan bahwa baris yang dirujuk memang ada. Aturan yang melibatkan hubungan antarkolom atau antartabel lain dinyatakan dengan CHECK constraint dan trigger, sehingga aturan tersebut tetap berlaku walaupun data ditulis dari luar aplikasi.'
    ),
    ...tabel(
      [2500, 1600, 3837],
      ['Nama constraint', 'Tabel', 'Aturan'],
      checkBaris,
      'Daftar CHECK constraint',
      { size: 17 }
    ),
    p(
      'Empat trigger melengkapi penegakan tersebut untuk aturan yang tidak dapat dinyatakan CHECK constraint, karena CHECK pada MySQL tidak boleh membaca tabel lain.'
    ),
    ...tabel(
      [2500, 1500, 3937],
      ['Trigger', 'Tabel', 'Aturan yang ditegakkan'],
      K.TRIGGER_PENJELASAN,
      'Daftar trigger beserta aturan yang ditegakkannya',
      { size: 17 }
    ),
  ];
}

function viewDanIndeks() {
  const isi = [
    h3('3.3.7', 'View'),
    p(
      'Dua view dipakai untuk menghitung status centang biru. Status tersebut sengaja tidak disimpan sebagai kolom, sebagaimana diuraikan pada pembahasan bentuk normal ketiga.'
    ),
  ];

  K.VIEW_PENJELASAN.forEach((v) => {
    isi.push(p([teks(v.nama, { bold: true }), teks('  —  ' + v.tujuan)]));
    isi.push(...butir(v.aturan));
  });

  isi.push(
    p(
      'Bentuk penulisan v_verifikasi_terbaru dipilih secara sadar. Penulisan dengan window function ROW_NUMBER terlihat lebih ringkas, tetapi memaksa MySQL memakai derived table yang tidak dapat digabungkan ke query pemanggil, sehingga seluruh tabel verifikasi dimaterialisasi walaupun yang dibutuhkan hanya satu sertifikat.'
    ),
    kode([
      'CREATE VIEW v_verifikasi_terbaru AS',
      'SELECT v.id, v.bukti_id, v.verifikator_id, v.keputusan, v.catatan,',
      '       v.diverifikasi_pada, v.berlaku_sampai, v.dibuat_pada',
      'FROM verifikasi v',
      'JOIN bukti b ON b.id = v.bukti_id',
      'WHERE v.dibuat_pada >= b.diunggah_pada',
      '  AND v.id = (',
      '      SELECT MAX(v2.id) FROM verifikasi v2',
      '      WHERE v2.bukti_id = v.bukti_id',
      '        AND v2.dibuat_pada >= b.diunggah_pada',
      '  );',
    ]),
    p(
      'View kedua membaca view pertama, sehingga aturan mengenai verifikasi mana yang berlaku hanya ditulis satu kali di seluruh sistem.',
      { after: 80 }
    ),
    kode([
      'CREATE VIEW v_status_keahlian AS',
      'SELECT kk.id AS klaim_keahlian_id, kk.pencari_kerja_id, kk.keahlian_id,',
      '    CASE WHEN EXISTS (',
      '        SELECT 1 FROM bukti_keahlian bk',
      '        JOIN v_verifikasi_terbaru vt ON vt.id = bk.verifikasi_id',
      '        WHERE bk.klaim_keahlian_id = kk.id',
      "          AND vt.keputusan = 'disetujui'",
      '          AND (vt.berlaku_sampai IS NULL OR vt.berlaku_sampai >= CURRENT_DATE)',
      '    ) THEN TRUE ELSE FALSE END AS terverifikasi',
      'FROM klaim_keahlian kk',
      'JOIN keahlian k ON k.id = kk.keahlian_id',
      'WHERE kk.aktif = TRUE AND k.aktif = TRUE;',
    ]),
    new D.Paragraph({ spacing: { after: 200 }, children: [teks('')] })
  );

  // --- indeks -------------------------------------------------------------
  const indeksPenting = skema.indeks.filter(
    (i) => i.INDEX_NAME !== 'PRIMARY' && !i.INDEX_NAME.startsWith('fk_')
  );
  const barisIndeks = indeksPenting.map((i) => [
    i.INDEX_NAME,
    i.TABLE_NAME,
    i.kolom,
    i.INDEX_TYPE === 'FULLTEXT' ? 'FULLTEXT' : i.NON_UNIQUE === 0 ? 'UNIQUE' : 'Biasa',
  ]);

  isi.push(
    h3('3.3.8', 'Indeks dan Optimasi Query'),
    p(
      'Indeks dirancang mengikuti query yang benar-benar dijalankan halaman daftar, dengan urutan kolom yang sama persis dengan urutan penyaringan dan pengurutannya. Indeks unik sekaligus berperan menegakkan aturan bisnis, misalnya satu pencari kerja tidak dapat melamar lowongan yang sama dua kali.'
    ),
    ...tabel(
      [2300, 1500, 2637, 1500],
      ['Nama indeks', 'Tabel', 'Kolom', 'Jenis'],
      barisIndeks,
      'Daftar indeks selain primary key dan indeks penopang foreign key',
      { size: 17 }
    ),
    p(K.BENCHMARK.keterangan),
    ...tabel(
      [2900, 1300, 1300, 2437],
      ['Query', 'Sebelum', 'Sesudah', 'Indeks yang dipakai'],
      K.BENCHMARK.baris,
      'Perbandingan waktu eksekusi query sebelum dan sesudah penambahan indeks',
      { size: 18 }
    ),
    p(K.BENCHMARK.catatanPencarian),
    p(K.BENCHMARK.catatanView)
  );

  return isi;
}

function normalisasi() {
  const isi = [
    h3('3.3.4', 'Normalisasi'),
    p(
      'Rancangan basis data SIBUKER-PT memenuhi bentuk normal ketiga. Bagian ini menguraikan pemenuhan setiap bentuk normal beserta contoh rancangan yang sengaja dihindari, agar alasan di balik pemisahan tabel dapat ditelusuri.'
    ),
  ];
  K.NORMALISASI.forEach((n) => {
    isi.push(p([teks(n.bentuk, { bold: true })], { after: 60 }));
    isi.push(
      ...tabel(
        [1700, 6237],
        ['Aspek', 'Uraian'],
        [
          ['Syarat', n.syarat],
          ['Rancangan yang dihindari', n.pelanggaran],
          ['Penerapan pada SIBUKER-PT', n.penerapan],
          ['Akibat bila dilanggar', n.akibat],
        ],
        'Pemenuhan ' + n.bentuk,
        { size: 18 }
      )
    );
  });
  isi.push(p([teks('Denormalisasi terkendali. ', { bold: true }), teks(K.DENORMALISASI)]));
  return isi;
}

function alurPeran() {
  const isi = [
    h2('3.4', 'Hak Akses dan Operasi Basis Data per Peran'),
    p(
      'Peran tidak disimpan sebagai satu kolom, melainkan ditentukan oleh keberadaan baris pada tabel spesialisasi. Sebuah akun berperan sebagai pencari kerja bila memiliki baris pada tabel pencari_kerja, sebagai perusahaan bila memiliki baris pada perusahaan, sebagai verifikator bila memiliki baris pada verifikator, dan sebagai administrator bila kolom pengguna.is_admin bernilai benar. Dengan cara ini satu akun dapat memegang lebih dari satu peran, misalnya seorang praktisi yang menjadi verifikator sekaligus pencari kerja.'
    ),
    p(
      'Setiap peran hanya dapat mengubah data miliknya sendiri. Rincian hak akses setiap peran terhadap setiap tabel ditunjukkan pada tabel berikut, dengan C untuk create, R untuk read, U untuk update, dan D untuk delete.'
    ),
    ...tabel(
      [1737, 1100, 1300, 1300, 1300, 1200],
      ['Tabel', 'Tamu', 'Pencari kerja', 'Perusahaan', 'Verifikator', 'Administrator'],
      K.MATRIKS_AKSES,
      'Matriks hak akses peran terhadap tabel',
      { size: 16 }
    ),
    p(
      'Subbab berikut menelusuri antarmuka setiap peran dan memetakan tombol serta aksi yang tersedia ke operasi basis data yang dijalankannya.'
    ),
  ];

  K.ALUR_PERAN.forEach((bagian, i) => {
    isi.push(h3('3.4.' + (i + 1), bagian.peran));
    isi.push(p(bagian.pengantar));
    bagian.gambar.forEach(([berkas, ket]) => {
      const jalur = path.join(GBR_UI, berkas + '.png');
      if (fs.existsSync(jalur)) isi.push(...gambar(jalur, ket));
      else console.warn('  gambar tidak ditemukan: ' + berkas);
    });
    isi.push(
      ...tabel(
        [1500, 1700, 1900, 1300, 1537],
        ['Halaman', 'Tombol atau aksi', 'Route', 'Operasi', 'Tabel yang terlibat'],
        bagian.aksi,
        'Pemetaan aksi antarmuka ke operasi basis data untuk peran ' + bagian.peran,
        { size: 16 }
      )
    );
  });

  return isi;
}

function pengujian() {
  return [
    h2('3.5', 'Pengujian'),
    h3('3.5.1', 'Uji Kelayakan Basis Data'),
    p(K.PENGUJIAN.kelayakan.pengantar),
    ...tabel(
      [2200, 900, 4837],
      ['Kelompok pemeriksaan', 'Jumlah', 'Cakupan'],
      K.PENGUJIAN.kelayakan.baris,
      'Rangkuman hasil uji kelayakan basis data',
      { size: 18 }
    ),
    h3('3.5.2', 'Pengujian Fungsional'),
    p(K.PENGUJIAN.fungsional.pengantar),
    ...tabel(
      [2700, 5237],
      ['Pengujian', 'Hal yang dipastikan'],
      K.PENGUJIAN.fungsional.baris,
      'Daftar pengujian fungsional beserta cakupannya',
      { size: 18 }
    ),
  ];
}

function bab3() {
  const isi = [
    ...judulBab('III', 'METODOLOGI'),

    h2('3.1', 'Deskripsi Sistem'),
    p(
      'SIBUKER-PT merupakan sistem berbasis web yang mempertemukan pencari kerja dengan perusahaan berdasarkan keahlian yang dimiliki. Pencari kerja dapat mencantumkan keahlian meskipun belum memiliki sertifikat, mengunggah sertifikat dalam bentuk PDF secara opsional, mencari dan menyaring lowongan, mengajukan lamaran, serta memantau proses seleksinya.'
    ),
    p(
      'Perusahaan membuat lowongan dan menentukan keahlian yang dibutuhkan, termasuk menetapkan apakah suatu keahlian wajib terverifikasi. Sertifikat yang diunggah diperiksa verifikator untuk memastikan keasliannya dan menentukan keahlian yang benar-benar dibuktikan. Keahlian yang telah disetujui memperoleh tanda centang biru pada profil pencari kerja. Dengan demikian pencarian kandidat dapat dilakukan secara lebih fleksibel karena tidak seluruh keahlian harus memiliki sertifikat.'
    ),
    p('SIBUKER-PT melibatkan empat peran pengguna yang sudah masuk, ditambah pengunjung tanpa akun.'),
    ...tabel(
      [1500, 2300, 4137],
      ['Peran', 'Cara memperoleh akses', 'Kemampuan utama'],
      K.PERAN,
      'Peran pengguna SIBUKER-PT',
      { size: 18 }
    ),

    h2('3.2', 'Arsitektur Sistem'),
    p(
      'SIBUKER-PT dirancang dengan arsitektur client-server. Antarmuka disusun menggunakan Blade, logika aplikasi ditangani Laravel 13 berbasis PHP, dan data disimpan pada MySQL 8.4. Berkas PDF sertifikat tidak disimpan di dalam basis data, melainkan pada Laravel File Storage, sedangkan basis data hanya menyimpan lokasi berkasnya.'
    ),
    ...gambar(path.join(GBR_ERD, 'arsitektur.png'), 'Alur permintaan pengguna dari peramban hingga basis data'),
    p(
      'Permintaan dari peramban diterima routing, diperiksa middleware peran, lalu diproses controller. Controller memakai model Eloquent untuk membaca atau menulis basis data, kemudian hasilnya disusun Blade menjadi halaman HTML. Permintaan yang tidak sesuai peran dihentikan pada lapisan middleware sebelum menyentuh basis data.'
    ),
    ...tabel(
      [1700, 2100, 4137],
      ['Komponen', 'Teknologi', 'Tanggung jawab'],
      K.KOMPONEN,
      'Komponen penyusun sistem beserta tanggung jawabnya',
      { size: 18 }
    ),

    h2('3.3', 'Perancangan Basis Data'),
    p(
      'Basis data dirancang dalam dua tingkat. ERD konseptual menggambarkan entitas dan relasinya tanpa detail teknis, sedangkan ERD fisik menggambarkan struktur tabel yang benar-benar diterapkan pada MySQL. Seluruh rancangan diwujudkan melalui migration Laravel dan tersedia pula sebagai satu berkas SQL utuh pada database/SIBUKER_PT.sql.'
    ),

    h3('3.3.1', 'ERD Konseptual'),
    p(
      'ERD konseptual menampilkan 13 entitas tanpa kolom, tipe data, maupun kunci. Dua tabel penghubung murni, yaitu kewenangan_verifikator dan bukti_keahlian, tidak ditampilkan sebagai entitas tersendiri melainkan dinyatakan sebagai relasi banyak ke banyak, karena keduanya tidak membawa atribut apa pun di luar pasangan kuncinya. Sebaliknya klaim_keahlian, syarat_keahlian, dan lamaran tetap ditampilkan sebagai entitas asosiatif karena ketiganya membawa atribut yang hanya bermakna pada hubungan itu sendiri, misalnya level klaim, bobot syarat, dan skor kecocokan.'
    ),
    ...gambar(path.join(GBR_ERD, 'konseptual.png'), 'ERD konseptual SIBUKER-PT'),
    ...tabel(
      [2100, 1500, 900, 3437],
      ['Relasi', 'Nama hubungan', 'Kardinalitas', 'Keterangan'],
      K.RELASI_KONSEPTUAL,
      'Daftar relasi antarentitas pada ERD konseptual',
      { size: 17 }
    ),

    h3('3.3.2', 'ERD Fisik'),
    p(
      'ERD fisik memuat 15 tabel beserta tipe data, primary key, foreign key, dan unique constraint. Agar tetap terbaca, diagram disajikan per modul. Diagram utuh yang memuat seluruh tabel sekaligus disertakan pada Lampiran A.'
    ),
  ];

  K.MODUL_ERD.forEach(([berkas, judul, penjelasan]) => {
    isi.push(p([teks(judul, { bold: true })], { after: 60 }));
    isi.push(p(penjelasan));
    isi.push(...gambar(path.join(GBR_ERD, berkas + '.png'), 'ERD fisik ' + judul.toLowerCase()));
  });

  isi.push(...kamusData());
  isi.push(...normalisasi());
  isi.push(...integritas());
  isi.push(...viewDanIndeks());
  isi.push(...alurPeran());
  isi.push(...pengujian());

  return isi;
}

function daftarPustaka() {
  return [
    new D.Paragraph({
      heading: D.HeadingLevel.HEADING_1,
      alignment: D.AlignmentType.CENTER,
      pageBreakBefore: true,
      spacing: { after: 360 },
      children: [teks('DAFTAR PUSTAKA', { bold: true, size: 28 })],
    }),
    ...K.PUSTAKA.map(
      (t) =>
        new D.Paragraph({
          alignment: D.AlignmentType.JUSTIFIED,
          spacing: { line: 300, after: 160 },
          indent: { left: 720, hanging: 720 },
          children: [teks(t)],
        })
    ),
  ];
}

// --------------------------------------------------------------------------
// Penyusunan dokumen
// --------------------------------------------------------------------------

function nomorHalaman(format) {
  return new D.Footer({
    children: [
      new D.Paragraph({
        alignment: D.AlignmentType.CENTER,
        children: [new D.TextRun({ children: [D.PageNumber.CURRENT], font: FONT, size: 20 })],
      }),
    ],
  });
}

const gayaJudulTersembunyi = {
  paragraphStyles: [
    {
      id: 'CaptionGambar',
      name: 'CaptionGambar',
      basedOn: 'Normal',
      next: 'Normal',
      quickFormat: true,
      run: { font: FONT, size: 20, italics: false },
      paragraph: { alignment: D.AlignmentType.CENTER },
    },
    {
      id: 'CaptionTabel',
      name: 'CaptionTabel',
      basedOn: 'Normal',
      next: 'Normal',
      quickFormat: true,
      run: { font: FONT, size: 20 },
      paragraph: { alignment: D.AlignmentType.LEFT },
    },
    { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 28, bold: true, color: '000000' } },
    { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 26, bold: true, color: '000000' } },
    { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true, run: { font: FONT, size: 24, bold: true, color: '000000' } },
  ],
  default: {
    document: { run: { font: FONT, size: 24 }, paragraph: { spacing: { line: 360 } } },
  },
};

// Badan laporan dibentuk LEBIH DULU, karena proses pembentukannya yang mengisi
// daftarGambar dan daftarTabel yang kemudian dipakai menyusun bagian depan.
const isiBadan = [...bab1(), ...bab2(), ...bab3(), ...daftarPustaka()];
const isiLampiran = [
  new D.Paragraph({
    heading: D.HeadingLevel.HEADING_1,
    alignment: D.AlignmentType.CENTER,
    spacing: { after: 240 },
    children: [teks('LAMPIRAN A  ERD FISIK LENGKAP', { bold: true, size: 28 })],
  }),
  ...gambar(
    path.join(GBR_ERD, 'fisik-lengkap.png'),
    'ERD fisik SIBUKER-PT secara utuh, 15 tabel dan 19 relasi',
    LEBAR_LANDSCAPE_PX,
    TINGGI_LANDSCAPE_PX
  ),
];
const isiDepan = bagianDepan();

const dokumen = new D.Document({
  creator: 'Kelompok F SD-A2 - S1 Teknologi Sains Data, Universitas Airlangga',
  title: K.IDENTITAS.judul,
  description: 'Laporan UTS Basis Data - SIBUKER-PT',
  styles: gayaJudulTersembunyi,
  numbering: {
    config: [
      {
        reference: 'daftar-angka',
        levels: [
          {
            level: 0,
            format: D.LevelFormat.DECIMAL,
            text: '%1.',
            alignment: D.AlignmentType.START,
            style: { paragraph: { indent: { left: 720, hanging: 360 } } },
          },
        ],
      },
    ],
  },
  sections: [
    // Sampul, tanpa nomor halaman.
    {
      properties: {
        page: { size: { width: A4_L, height: A4_T }, margin: { top: M_LAIN, right: M_LAIN, bottom: M_LAIN, left: M_KIRI } },
      },
      children: sampul(),
    },
    // Bagian depan, nomor halaman angka romawi kecil.
    {
      properties: {
        page: {
          size: { width: A4_L, height: A4_T },
          margin: { top: M_LAIN, right: M_LAIN, bottom: M_LAIN, left: M_KIRI },
          pageNumbers: { start: 1, formatType: D.NumberFormat.LOWER_ROMAN },
        },
      },
      footers: { default: nomorHalaman() },
      children: isiDepan,
    },
    // Badan laporan, nomor halaman angka biasa.
    {
      properties: {
        page: {
          size: { width: A4_L, height: A4_T },
          margin: { top: M_LAIN, right: M_LAIN, bottom: M_LAIN, left: M_KIRI },
          pageNumbers: { start: 1, formatType: D.NumberFormat.DECIMAL },
        },
      },
      footers: { default: nomorHalaman() },
      children: isiBadan,
    },
    // Lampiran ERD utuh, orientasi lanskap agar seluruh tabel muat.
    {
      properties: {
        page: {
          size: { width: A4_L, height: A4_T, orientation: D.PageOrientation.LANDSCAPE },
          margin: { top: M_LAIN, right: M_LAIN, bottom: M_LAIN, left: M_LAIN },
        },
      },
      footers: { default: nomorHalaman() },
      children: isiLampiran,
    },
  ],
});

D.Packer.toBuffer(dokumen).then((buf) => {
  fs.writeFileSync(KELUARAN, buf);
  console.log('Dokumen tersimpan: ' + KELUARAN);
  console.log('  ' + (buf.length / 1024 / 1024).toFixed(2) + ' MB, ' + noGambar + ' gambar, ' + noTabel + ' tabel');
});
