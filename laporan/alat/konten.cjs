/**
 * Isi naratif laporan SIBUKER-PT.
 *
 * Berkas ini hanya berisi teks dan data pemetaan. Angka yang berasal dari basis data
 * (kamus data, foreign key, indeks, constraint) tidak ditulis di sini. Semuanya dibaca
 * buat_laporan.cjs dari laporan/skema.json supaya isinya tidak pernah basi.
 */

const IDENTITAS = {
  judul: 'SISTEM INFORMASI BURSA KERJA BERBASIS PORTOFOLIO DAN KEAHLIAN TERVERIFIKASI',
  akronim: 'SIBUKER-PT',
  matkul: 'Laporan Tugas Ujian Tengah Semester',
  matkulNama: 'Mata Kuliah Basis Data',
  dosen: ['Dr. Aziz Fajar, S.Kom., M.Sc.'],
  kelompok: 'Kelompok F, Kelas SD-A2',
  anggota: [
    ['Arif Putra Feriza', '164231093'],
    ['I Wayan Satya Dharma Sadhana', '164241032'],
    ['Raphael Angelo Adikara Purnama', '164241055'],
    ['Nadia Aulia Larasati', '164241074'],
    ['Greg Ezra Sebastian Sitorus', '164241077'],
    ['Aulia Rachmat Aditya', '164241080'],
  ],
  institusi: [
    'PROGRAM STUDI S1 TEKNOLOGI SAINS DATA',
    'FAKULTAS TEKNOLOGI MAJU DAN MULTIDISIPLIN',
    'UNIVERSITAS AIRLANGGA',
    'SURABAYA',
    '2026',
  ],
};

// ---------------------------------------------------------------------------
// BAB I
// ---------------------------------------------------------------------------

const BAB1 = {
  latar: [
    'Perusahaan sekarang makin banyak menilai pelamar dari keterampilannya, bukan cuma dari ijazah. Masalahnya, keterampilan yang ditulis pelamar di CV biasanya hanya klaim sendiri tanpa bukti apa pun. Perusahaan jadi sulit membedakan mana keterampilan yang benar-benar ada buktinya dan mana yang belum.',
    'Sertifikat bisa dipakai sebagai bukti, tetapi tidak semua orang punya sertifikat untuk setiap kemampuannya. Banyak keterampilan yang didapat dari pengalaman kerja, proyek pribadi, pelatihan singkat, atau belajar sendiri. Jadi sistemnya harus tetap membolehkan orang mencantumkan keterampilan tanpa sertifikat, tetapi memberi penanda khusus untuk keterampilan yang sudah ada bukti sahnya.',
    'Masalah ini nyambung dengan Sustainable Development Goal (SDG) 8, terutama soal memperluas kesempatan kerja yang layak dan inklusif bagi orang dengan latar belakang pendidikan dan pengalaman yang beragam. Rekrutmen yang transparan dan berbasis bukti membantu perusahaan menyeleksi lebih objektif. Di sisi lain, pencari kerja juga punya peluang lebih adil untuk menunjukkan kemampuannya walaupun tidak punya sertifikat formal.',
    'Dari masalah itu kami merancang SIBUKER-PT, yaitu Sistem Informasi Bursa Kerja Berbasis Portofolio Terverifikasi. Di sistem ini pengguna bisa mencantumkan keterampilan apa saja dan mengunggah sertifikat kalau punya. Sertifikatnya diperiksa verifikator, lalu keterampilan yang terbukti mendapat tanda centang biru. Perusahaan bisa menentukan keterampilan mana yang wajib terverifikasi pada lowongannya, dan pencari kerja bisa menyaring lowongan berdasarkan syarat itu. Data keterampilan, sertifikat, verifikasi, lowongan, dan lamaran disatukan dalam satu basis data supaya proses rekrutmennya lebih transparan dan tidak kaku.',
  ],
  mission:
    'Mewujudkan sistem bursa kerja berbasis keterampilan yang fleksibel, inklusif, dan terpercaya melalui pengelolaan profil keahlian, verifikasi sertifikat, pencocokan persyaratan lowongan, serta penyediaan informasi rekrutmen yang terintegrasi bagi pencari kerja dan perusahaan.',
  objectives: [
    'Menyediakan pengelolaan profil pencari kerja yang mencakup data pribadi, keterampilan, portofolio, dan sertifikat pendukung dalam satu tempat.',
    'Menyediakan mekanisme verifikasi sertifikat untuk memberi centang biru pada keterampilan yang ada buktinya, tanpa mewajibkan sertifikat untuk semua keterampilan.',
    'Memudahkan perusahaan mengelola lowongan dan menentukan keterampilan yang wajib atau opsional beserta kebutuhan verifikasinya.',
    'Mencocokkan keterampilan pencari kerja dengan syarat lowongan, serta menyediakan filter berdasarkan keterampilan dan status verifikasinya.',
    'Menyatukan proses lamaran dan seleksi supaya perkembangan tiap lamaran bisa disimpan dan dipantau.',
    'Mendukung rekrutmen yang lebih transparan dan inklusif dengan menjadikan keterampilan beserta buktinya sebagai bahan utama penilaian.',
  ],
};

// ---------------------------------------------------------------------------
// BAB II
// ---------------------------------------------------------------------------

const BAB2 = {
  kajian: [
    'Sistem informasi bursa kerja sudah pernah dikembangkan di beberapa penelitian. Rayhan dan Utami (2024) membuat sistem informasi Bursa Kerja Khusus berbasis web untuk mengelola lowongan dan data pencari kerja. Hasilnya menunjukkan sistem yang terintegrasi memudahkan penyampaian informasi lowongan dan pengelolaan rekrutmen.',
    'Untuk verifikasi dokumen, Alfina dan Syafrinal (2022) membahas sistem verifikasi ijazah digital yang memastikan dokumennya asli. Restaldo dan Beeh (2022) menunjukkan Laravel bisa dipakai membangun sistem informasi berbasis web dengan struktur kode yang rapi. Kedua penelitian itu kami jadikan acuan untuk bagian pemeriksaan sertifikat dan pembuatan aplikasinya.',
    'SIBUKER-PT melanjutkan konsep tersebut dengan menambahkan pencocokan lowongan berbasis keahlian. Pencari kerja boleh mencantumkan keahlian tanpa sertifikat, tetapi bisa mengunggah sertifikat PDF sebagai bukti. Verifikator lalu memeriksa keaslian sertifikat dan menentukan keahlian mana yang benar-benar dibuktikan. Perusahaan bisa menetapkan apakah tiap keahlian di lowongannya harus terverifikasi atau tidak.',
  ],
  teori: [
    {
      judul: 'Basis Data dan Sistem Manajemen Basis Data',
      isi: [
        'Basis data adalah kumpulan data yang saling berhubungan dan disusun terstruktur supaya bisa dipakai pengguna maupun aplikasi. Pengelolaannya lewat Database Management System (DBMS), yaitu perangkat lunak untuk mendefinisikan, menyimpan, mengubah, mengambil, dan mengatur akses ke data (Connolly dan Begg, 2015).',
        'DBMS membantu mengurangi duplikasi data, menjaga konsistensi, dan mengatur hubungan antardata. Di SIBUKER-PT, basis data dipakai menyatukan data pengguna, keahlian, sertifikat, verifikasi, perusahaan, lowongan, lamaran, dan tahapan seleksi. Kami memilih MySQL karena mendukung model relasional, menyediakan foreign key, CHECK constraint, view, dan trigger untuk menjaga integritas, serta cocok dengan framework Laravel yang kami pakai.',
      ],
    },
    {
      judul: 'Model Data Relasional',
      isi: [
        'Model data relasional menyimpan data dalam bentuk tabel berisi baris dan kolom. Satu tabel menggambarkan satu entitas, dan satu baris berisi satu data dari entitas itu. Hubungan antartabel dibuat dengan primary key dan foreign key (Codd, 1970).',
        'Model ini cocok untuk SIBUKER-PT karena hubungan datanya jelas. Pencari kerja terhubung ke klaim keahlian, perusahaan terhubung ke lowongan, dan lowongan terhubung ke syarat keahlian. Model relasional juga memungkinkan pemasangan aturan integritas untuk mencegah data yang tidak konsisten.',
      ],
    },
    {
      judul: 'Entity Relationship Diagram',
      isi: [
        'Entity Relationship Diagram (ERD) adalah notasi untuk menggambarkan entitas, atribut, dan relasi dalam sebuah basis data (Elmasri dan Navathe, 2016). ERD dipakai untuk memahami struktur data sebelum basis datanya benar-benar dibuat.',
        'Kami memakai dua tingkat ERD. ERD konseptual menggambarkan entitas dan relasinya saja, tanpa kolom, tipe data, maupun kunci, supaya bisa dibaca orang yang bukan orang teknis. ERD fisik menggambarkan struktur tabel yang benar-benar dipakai di MySQL, lengkap dengan tipe data, primary key, foreign key, dan unique constraint.',
      ],
      butir: [
        'Entitas, yaitu objek yang datanya disimpan, misalnya pengguna, keahlian, bukti, lowongan, dan lamaran.',
        'Atribut, yaitu informasi yang menjelaskan entitas, misalnya nama dan email pada pengguna.',
        'Primary key, yaitu atribut yang membedakan tiap baris dalam satu tabel.',
        'Foreign key, yaitu atribut yang menghubungkan satu tabel ke tabel lain.',
        'Kardinalitas, yaitu banyaknya keterhubungan antarentitas, misalnya satu perusahaan bisa membuka banyak lowongan.',
        'Entitas asosiatif, yaitu entitas yang dipakai untuk memecah hubungan banyak ke banyak. Di SIBUKER-PT perannya dipegang bukti_keahlian, kewenangan_verifikator, syarat_keahlian, dan lamaran.',
      ],
    },
    {
      judul: 'Normalisasi',
      isi: [
        'Normalisasi adalah proses menyusun struktur relasi supaya redundansi data berkurang dan anomali penyisipan, pembaruan, serta penghapusan hilang (Codd, 1970; Connolly dan Begg, 2015). Bentuk normal pertama (1NF) mensyaratkan tiap atribut bernilai atomik dan tidak mengandung kelompok berulang. Bentuk normal kedua (2NF) mensyaratkan 1NF sudah terpenuhi dan tidak ada atribut bukan kunci yang bergantung pada sebagian primary key majemuk. Bentuk normal ketiga (3NF) mensyaratkan 2NF sudah terpenuhi dan tidak ada ketergantungan transitif antaratribut bukan kunci.',
        'Rancangan basis data SIBUKER-PT sudah memenuhi 3NF. Buktinya beserta contoh rancangan yang kami hindari ada di subbab 3.3.4.',
      ],
    },
    {
      judul: 'Keahlian dan Verifikasi Sertifikat',
      isi: [
        'Keahlian adalah kemampuan seseorang mengerjakan pekerjaan atau aktivitas tertentu. Keahlian bisa didapat dari pendidikan formal, pelatihan, pengalaman kerja, proyek pribadi, sampai belajar sendiri. Karena itu tidak semua keahlian harus punya sertifikat untuk bisa dicantumkan di profil.',
        'Di SIBUKER-PT, pencari kerja mencantumkan keahlian lewat tabel klaim_keahlian. Sertifikat boleh diunggah dalam bentuk PDF lewat tabel bukti, sifatnya opsional. Verifikator lalu memeriksa keaslian sertifikat dan menentukan keahlian mana yang memang dibuktikan dokumen itu. Satu sertifikat bisa membuktikan beberapa keahlian sekaligus lewat tabel penghubung bukti_keahlian.',
        'Status centang biru tidak diisi sendiri oleh pencari kerja. Statusnya dihitung dari hasil verifikasi terbaru yang disetujui dan masih berlaku. Kalau buktinya ditolak, masa berlakunya habis, atau berkas PDF-nya diganti setelah diperiksa, keahlian itu tetap muncul di profil tetapi tanpa centang biru. Jadi keahlian tanpa sertifikat tetap punya tempat, tetapi tidak disamakan dengan yang sudah terbukti.',
        'Portofolio di SIBUKER-PT bukan satu tabel tersendiri. Portofolio terbentuk dari gabungan data profil, keahlian, dan sertifikat yang saling terhubung di dalam basis data.',
      ],
    },
    {
      judul: 'Pencocokan Berbasis Keahlian',
      isi: [
        'Pencocokan berbasis keahlian adalah proses membandingkan keahlian yang dimiliki pencari kerja dengan syarat sebuah lowongan. Cara ini membuat sistem menilai kandidat dari kemampuannya, bukan sekadar dari kata kunci judul pekerjaan atau latar pendidikan.',
        'Tiap lowongan bisa punya beberapa syarat lewat tabel syarat_keahlian. Perusahaan menentukan keahlian yang dibutuhkan, level minimum, sifat wajib atau opsional, bobot, dan apakah perlu terverifikasi. Keahlian yang wajib terverifikasi hanya dihitung memenuhi syarat kalau centang birunya masih berlaku. Keahlian yang tidak mewajibkan verifikasi cukup dipenuhi klaim biasa.',
        'Hasil perbandingan itu dipakai menghitung skor kecocokan. Rumusnya: jumlah bobot syarat yang terpenuhi dibagi total bobot semua syarat, lalu dikali seratus. Nilainya disimpan di kolom lamaran.skor_kecocokan saat lamaran dikirim.',
      ],
    },
    {
      judul: 'Arsitektur Model-View-Controller dan Framework Laravel',
      isi: [
        'Model-View-Controller (MVC) adalah pola arsitektur yang membagi aplikasi jadi tiga lapisan. Model mengurus data dan aturan bisnis, view mengurus tampilan, dan controller menjembatani masukan pengguna dengan pemrosesan di model. Pembagian ini membuat kode lebih mudah dibaca dan dirawat, apalagi kalau relasi datanya banyak.',
        'Laravel adalah framework PHP yang memakai pola MVC. Laravel menyediakan Eloquent ORM untuk pemetaan objek ke tabel, migration untuk mengelola versi skema basis data, seeder untuk mengisi data contoh, dan middleware untuk membatasi hak akses. Restaldo dan Beeh (2022) menyimpulkan fitur-fitur itu mempercepat pembuatan sistem informasi berbasis basis data relasional sekaligus menjaga struktur datanya tetap konsisten. Di penelitian ini migration kami pakai untuk mewujudkan rancangan fisik basis data, dan middleware kami pakai untuk membatasi kewenangan antarperan.',
      ],
    },
    {
      judul: 'Keterkaitan dengan Sustainable Development Goals',
      isi: [
        'Sustainable Development Goals adalah agenda pembangunan global dari Perserikatan Bangsa-Bangsa. Tujuan kedelapan atau SDG 8 fokus pada pertumbuhan ekonomi yang inklusif, kesempatan kerja produktif, dan pekerjaan yang layak. Target 8.5 secara khusus menyebut tercapainya pekerjaan penuh dan produktif serta pekerjaan layak bagi semua orang.',
        'SIBUKER-PT membantu pencari kerja menunjukkan keahlian yang didapat dari berbagai jalur, termasuk pelatihan, pengalaman, dan belajar mandiri. Verifikasi sertifikat memberi informasi tambahan soal keahlian yang sudah terbukti. Syarat lowongan yang fleksibel juga membuat perusahaan tetap bisa menerima kandidat dengan keahlian yang belum terverifikasi.',
      ],
    },
  ],
};

// ---------------------------------------------------------------------------
// BAB III
// ---------------------------------------------------------------------------

const PERAN = [
  ['Tamu', 'Tanpa login', 'Melihat beranda, mencari dan membuka detail lowongan yang sudah dipublikasikan, mendaftar, dan masuk.'],
  ['Pencari kerja', 'Mendaftar sendiri lewat halaman Daftar', 'Mengelola profil, mencantumkan keahlian, mengunggah sertifikat PDF, melihat status centang biru, mencari lowongan beserta skor kecocokan, melamar, lalu memantau atau menarik lamaran.'],
  ['Perusahaan', 'Mendaftar sendiri, status aktifnya diatur administrator', 'Mengelola profil perusahaan, membuat lowongan dan syarat keahlian, mempublikasikan atau menutup lowongan, meninjau pelamar, mengubah status lamaran, dan mengatur tahap seleksi.'],
  ['Verifikator', 'Dibuat administrator beserta kewenangannya', 'Melihat antrean sertifikat sesuai kewenangan, memeriksa berkas, memutuskan disetujui atau ditolak, memilih keahlian yang terbukti, dan menentukan masa berlaku.'],
  ['Administrator', 'Ditandai kolom pengguna.is_admin', 'Mengelola akun pengguna, verifikator beserta kewenangannya, status perusahaan, serta data acuan keahlian dan jenis bukti.'],
];

const KOMPONEN = [
  ['Antarmuka', 'Blade, HTML, CSS, JavaScript', 'Menyusun halaman yang dilihat pengguna. Tidak mengakses basis data secara langsung.'],
  ['Routing', 'routes/web.php', 'Memetakan URL ke controller. Dibagi lima kelompok: publik, pencari kerja, perusahaan, verifikator, dan administrator.'],
  ['Kontrol akses', 'Middleware auth dan peran', 'Mengecek apakah akun yang login punya profil peran yang dibutuhkan. Permintaan yang tidak berhak dihentikan dengan kode 403.'],
  ['Logika aplikasi', 'Controller dan kelas Kecocokan', 'Memvalidasi masukan, menjalankan aturan bisnis, dan menghitung skor kecocokan.'],
  ['Akses data', 'Model Eloquent', 'Mengubah operasi objek jadi perintah SQL. Satu model mewakili satu tabel.'],
  ['Penyimpanan data', 'MySQL 8.4', 'Menyimpan semua data sistem dan menjaga integritasnya lewat foreign key, CHECK constraint, view, dan trigger.'],
  ['Penyimpanan berkas', 'Laravel File Storage', 'Menyimpan berkas PDF sertifikat. Basis data hanya menyimpan lokasi berkasnya di kolom bukti.url_berkas.'],
];

const RELASI_KONSEPTUAL = [
  ['Pengguna dan Pencari Kerja', 'Memiliki profil', '1 : 1', 'Satu akun paling banyak punya satu profil pencari kerja.'],
  ['Pengguna dan Perusahaan', 'Memiliki profil', '1 : 1', 'Satu akun paling banyak punya satu profil perusahaan.'],
  ['Pengguna dan Verifikator', 'Memiliki profil', '1 : 1', 'Satu akun paling banyak punya satu profil verifikator.'],
  ['Pencari Kerja dan Klaim Keahlian', 'Mencantumkan', '1 : N', 'Satu pencari kerja bisa mencantumkan banyak keahlian.'],
  ['Keahlian dan Klaim Keahlian', 'Diklaim pada', '1 : N', 'Satu keahlian bisa diklaim banyak pencari kerja.'],
  ['Pengguna dan Bukti', 'Mengunggah', '1 : N', 'Satu akun bisa mengunggah banyak sertifikat.'],
  ['Jenis Bukti dan Bukti', 'Mengelompokkan', '1 : N', 'Satu jenis bukti bisa dipakai banyak sertifikat.'],
  ['Bukti dan Verifikasi', 'Diperiksa lewat', '1 : N', 'Satu sertifikat bisa punya beberapa riwayat pemeriksaan.'],
  ['Verifikator dan Verifikasi', 'Memutuskan', '1 : N', 'Satu verifikator bisa melakukan banyak pemeriksaan.'],
  ['Verifikator dan Keahlian', 'Berwenang atas', 'M : N', 'Diwujudkan tabel kewenangan_verifikator.'],
  ['Verifikasi dan Klaim Keahlian', 'Membuktikan', 'M : N', 'Diwujudkan tabel bukti_keahlian. Satu sertifikat bisa membuktikan beberapa keahlian.'],
  ['Perusahaan dan Lowongan', 'Membuka', '1 : N', 'Satu perusahaan bisa membuka banyak lowongan.'],
  ['Lowongan dan Syarat Keahlian', 'Menetapkan', '1 : N', 'Satu lowongan bisa menetapkan banyak syarat keahlian.'],
  ['Keahlian dan Syarat Keahlian', 'Dipakai pada', '1 : N', 'Satu keahlian bisa dipakai di banyak lowongan.'],
  ['Lowongan dan Lamaran', 'Menerima', '1 : N', 'Satu lowongan bisa menerima banyak lamaran.'],
  ['Pencari Kerja dan Lamaran', 'Mengirim', '1 : N', 'Satu pencari kerja bisa mengirim banyak lamaran.'],
  ['Lamaran dan Tahap Seleksi', 'Memiliki', '1 : N', 'Satu lamaran bisa punya beberapa tahap seleksi berurutan.'],
];

const MODUL_ERD = [
  ['fisik-pengguna', 'Modul pengguna dan profil', 'Tabel pengguna jadi pusat akun. Tiga tabel spesialisasi menyimpan atribut yang cuma relevan buat perannya masing-masing. Kolom pengguna_id di ketiganya berstatus UNIQUE, jadi satu akun paling banyak punya satu profil untuk tiap peran.'],
  ['fisik-keahlian', 'Modul keahlian dan verifikasi', 'Klaim keahlian menghubungkan pencari kerja dengan data acuan keahlian. Sertifikat disimpan di tabel bukti, hasil pemeriksaannya di tabel verifikasi, lalu keahlian yang benar-benar terbukti dihubungkan lewat tabel penghubung bukti_keahlian.'],
  ['fisik-lowongan', 'Modul lowongan dan persyaratan', 'Perusahaan membuka lowongan, dan tiap lowongan menetapkan syarat keahlian beserta level minimum, sifat, bobot, dan kebutuhan verifikasinya.'],
  ['fisik-lamaran', 'Modul lamaran dan seleksi', 'Lamaran menghubungkan lowongan dengan pencari kerja sekaligus menyimpan skor kecocokan saat itu. Perkembangan seleksinya dicatat di tahap_seleksi yang diurutkan lewat kolom urutan.'],
];

const NORMALISASI = [
  {
    bentuk: 'Bentuk Normal Pertama (1NF)',
    syarat: 'Tiap atribut bernilai atomik dan tidak mengandung kelompok berulang.',
    pelanggaran: 'Menyimpan keahlian sebagai satu kolom teks di profil pencari kerja, misalnya kolom keahlian berisi "SQL, Python, Visualisasi Data".',
    penerapan: 'Keahlian dipisah ke tabel acuan keahlian, lalu kepemilikannya dicatat satu baris per keahlian di tabel klaim_keahlian. Dari 15 tabel yang ada, tidak ada satu kolom pun yang menyimpan daftar bernilai jamak.',
    akibat: 'Kalau tidak dipisah, mencari semua pelamar yang bisa SQL harus lewat pencocokan teks. Memperbaiki satu salah tulis nama keahlian juga berarti mengubah semua baris profil yang memuatnya.',
  },
  {
    bentuk: 'Bentuk Normal Kedua (2NF)',
    syarat: 'Sudah 1NF, dan tidak ada atribut bukan kunci yang cuma bergantung pada sebagian primary key majemuk.',
    pelanggaran: 'Menambah kolom nama_keahlian di tabel kewenangan_verifikator yang kuncinya gabungan (verifikator_id, keahlian_id). Kolom itu cuma bergantung pada keahlian_id, padahal itu baru sebagian kunci.',
    penerapan: 'Tabel berkunci gabungan di SIBUKER-PT ada dua, yaitu kewenangan_verifikator dan bukti_keahlian. Tabel bukti_keahlian tidak punya atribut bukan kunci sama sekali. Tabel kewenangan_verifikator cuma punya diberikan_pada, dan kolom itu bergantung pada seluruh kunci karena menyatakan kapan pasangan verifikator dan keahlian itu ditetapkan.',
    akibat: 'Kalau dilanggar, satu nama keahlian akan tersimpan berulang di banyak baris kewenangan. Begitu namanya diperbaiki, muncul anomali pembaruan.',
  },
  {
    bentuk: 'Bentuk Normal Ketiga (3NF)',
    syarat: 'Sudah 2NF, dan tidak ada atribut bukan kunci yang bergantung pada atribut bukan kunci lain.',
    pelanggaran: 'Menambah kolom terverifikasi di tabel klaim_keahlian. Nilainya bukan fakta yang berdiri sendiri, melainkan turunan dari keputusan verifikasi terbaru dan masa berlakunya.',
    penerapan: 'Status centang biru tidak disimpan di tabel mana pun. Nilainya dihitung saat dibaca lewat view v_status_keahlian. Kolom agregat seperti jumlah pelamar atau total lowongan juga tidak kami simpan.',
    akibat: 'Kolom turunan seperti itu bisa jadi salah dengan sendirinya. Begitu tanggal di kolom verifikasi.berlaku_sampai lewat, centang birunya seharusnya padam, padahal saat itu tidak ada operasi tulis apa pun ke basis data. Dengan view, statusnya selalu dihitung dari keadaan terbaru.',
  },
];

const DENORMALISASI =
  'Ada satu denormalisasi yang kami lakukan dengan sengaja, yaitu kolom lamaran.skor_kecocokan. Secara teori nilai itu bisa dihitung ulang kapan saja dari tabel syarat_keahlian dan klaim_keahlian. Tetapi skor kecocokan adalah potret keadaan saat lamaran dikirim. Kalau dihitung ulang tiap kali dibaca, nilainya berubah ketika pencari kerja menambah keahlian atau dapat centang biru baru setelah melamar, sehingga riwayat seleksinya tidak bisa dipertanggungjawabkan lagi. Jadi kolom ini menyimpan fakta lama, bukan menyalin fakta yang masih berlaku.';

const VIEW_PENJELASAN = [
  {
    nama: 'v_verifikasi_terbaru',
    tujuan: 'menghasilkan paling banyak satu baris per sertifikat, yaitu hasil pemeriksaan yang masih berlaku.',
    aturan: [
      'Verifikasi yang dibuat sebelum berkas PDF terakhir diunggah dianggap gugur. Aturan ini bikin penggantian berkas otomatis mencabut hasil pemeriksaan lama.',
      'Dari verifikasi yang tersisa, cuma yang terbaru, yaitu yang id-nya terbesar, yang dianggap berlaku.',
    ],
  },
  {
    nama: 'v_status_keahlian',
    tujuan: 'menghasilkan status centang biru untuk tiap klaim keahlian yang aktif.',
    aturan: [
      'Klaim dianggap terverifikasi kalau terhubung ke verifikasi terbaru yang berkeputusan disetujui lewat tabel bukti_keahlian.',
      'Verifikasi yang masa berlakunya sudah lewat tidak lagi memberi centang biru.',
      'Klaim pada keahlian yang dinonaktifkan administrator tidak ikut ditampilkan.',
    ],
  },
];

const TRIGGER_PENJELASAN = [
  ['trg_bukti_keahlian_insert dan trg_bukti_keahlian_update', 'bukti_keahlian', 'Menolak penautan sertifikat milik satu pengguna ke klaim keahlian milik pengguna lain. Juga menolak penautan dari verifikasi yang belum disetujui. Foreign key cuma bisa memastikan kedua baris yang dirujuk memang ada, tetapi tidak bisa memastikan keduanya milik orang yang sama.'],
  ['trg_verifikasi_insert dan trg_verifikasi_update', 'verifikasi', 'Menolak verifikator yang memeriksa sertifikatnya sendiri. Aturan ini perlu karena satu akun boleh merangkap jadi verifikator sekaligus pencari kerja.'],
];

const BENCHMARK = {
  keterangan:
    'Kami menguji kinerjanya pakai basis data uji berisi 25.000 lowongan, 75.000 lamaran, 45.000 verifikasi, dan 30.000 sertifikat. Tiap query dijalankan lima kali setelah pemanasan, lalu diambil rata-ratanya. Kolom sebelum adalah waktu pada rancangan awal tanpa indeks komposit, dan kolom sesudah adalah waktu pada rancangan akhir.',
  baris: [
    ['Menampilkan daftar lowongan publik', '61,92 ms', '0,34 ms', 'idx_lowongan_publik'],
    ['Mencari lowongan berdasarkan kata kunci', '84,07 ms', '0,51 ms', 'ft_lowongan_pencarian'],
    ['Menghitung keahlian bercentang biru', '1.265,07 ms', '300,53 ms', 'idx_verifikasi_bukti_terbaru'],
    ['Mengurutkan pelamar berdasarkan skor', '0,64 ms', '0,23 ms', 'idx_lamaran_peringkat'],
    ['Menampilkan lamaran milik pencari kerja', '0,88 ms', '0,10 ms', 'idx_lamaran_pencari_waktu'],
    ['Menampilkan sertifikat milik pengguna', '0,82 ms', '0,09 ms', 'idx_bukti_pemilik'],
  ],
  catatanPencarian:
    'Angka pencarian kata kunci di atas diambil dari kasus terburuk, yaitu kata yang tidak ketemu sama sekali sehingga seluruh tabel harus dipindai kalau pakai LIKE. Untuk kata yang sangat umum, LIKE justru lebih cepat karena berhenti begitu sudah dapat cukup baris. Kami tetap memilih indeks FULLTEXT karena yang dibatasi adalah waktu kasus terburuknya, bukan karena selalu lebih cepat di semua kasus.',
  catatanView:
    'Cara menulis view juga berpengaruh besar. Versi v_verifikasi_terbaru yang pakai window function ROW_NUMBER memaksa MySQL memmaterialisasi seluruh tabel verifikasi, padahal query pemanggilnya cuma butuh satu sertifikat. Membaca status satu klaim makan waktu 541 ms dengan cara itu, dibanding 0,43 ms dengan subquery berkorelasi yang akhirnya kami pakai.',
};

// Matriks hak akses. C = create, R = read, U = update, D = delete.
const MATRIKS_AKSES = [
  ['pengguna', 'C (daftar)', 'R U (akun sendiri)', 'R U (akun sendiri)', 'R U (akun sendiri)', 'C R U D'],
  ['pencari_kerja', 'C (daftar)', 'R U (milik sendiri)', 'tidak ada', 'tidak ada', 'R'],
  ['perusahaan', 'C (daftar)', 'R (profil publik)', 'R U (milik sendiri)', 'tidak ada', 'R U (status)'],
  ['verifikator', 'tidak ada', 'tidak ada', 'tidak ada', 'R (milik sendiri)', 'C R U D'],
  ['keahlian', 'R', 'R', 'R', 'R', 'C R U D'],
  ['jenis_bukti', 'tidak ada', 'R', 'tidak ada', 'R', 'C R U D'],
  ['kewenangan_verifikator', 'tidak ada', 'tidak ada', 'tidak ada', 'R (milik sendiri)', 'C R U D'],
  ['klaim_keahlian', 'tidak ada', 'C R U D (milik sendiri)', 'R (pelamar saja)', 'R (dalam kewenangan)', 'R'],
  ['bukti', 'tidak ada', 'C R U D (milik sendiri)', 'tidak ada', 'R (dalam antrean)', 'R'],
  ['verifikasi', 'tidak ada', 'R (milik sendiri)', 'tidak ada', 'C R (dalam kewenangan)', 'R'],
  ['bukti_keahlian', 'tidak ada', 'R (milik sendiri)', 'tidak ada', 'C (saat menyetujui)', 'R'],
  ['lowongan', 'R (dipublikasikan)', 'R (dipublikasikan)', 'C R U D (milik sendiri)', 'tidak ada', 'R'],
  ['syarat_keahlian', 'R', 'R', 'C R D (lowongan sendiri)', 'tidak ada', 'R'],
  ['lamaran', 'tidak ada', 'C R U (tarik)', 'R U (status)', 'tidak ada', 'R'],
  ['tahap_seleksi', 'tidak ada', 'R (lamaran sendiri)', 'C R U D (pelamarnya)', 'tidak ada', 'R'],
];

/**
 * Pemetaan tombol dan aksi di antarmuka ke operasi basis data.
 * Tiap bagian jadi satu subbab berisi tangkapan layar dan satu tabel.
 */
const ALUR_PERAN = [
  {
    peran: 'Tamu dan Halaman Publik',
    pengantar:
      'Pengunjung tanpa akun bisa menelusuri lowongan yang sudah dipublikasikan. Semua aksi di kelompok ini cuma membaca data, kecuali pendaftaran akun yang menulis ke dua tabel sekaligus.',
    gambar: [
      ['publik-beranda', 'Halaman beranda SIBUKER-PT'],
      ['publik-lowongan', 'Halaman daftar lowongan beserta panel filter'],
      ['publik-lowongan-detail', 'Halaman detail lowongan beserta syarat keahlian'],
      ['publik-daftar', 'Halaman pendaftaran akun'],
      ['publik-masuk', 'Halaman masuk'],
    ],
    aksi: [
      ['Beranda', 'Membuka halaman', 'GET /', 'SELECT', 'lowongan, perusahaan, syarat_keahlian, keahlian, v_status_keahlian'],
      ['Beranda', 'Jelajahi lowongan', 'GET /lowongan', 'SELECT', 'lowongan, perusahaan'],
      ['Daftar lowongan', 'Kotak pencarian', 'GET /lowongan?q=', 'SELECT dengan MATCH AGAINST', 'lowongan, perusahaan'],
      ['Daftar lowongan', 'Filter tipe pekerjaan', 'GET /lowongan?tipe[]=', 'SELECT dengan WHERE IN', 'lowongan'],
      ['Daftar lowongan', 'Filter keahlian', 'GET /lowongan?keahlian=', 'SELECT dengan EXISTS', 'lowongan, syarat_keahlian'],
      ['Daftar lowongan', 'Filter tanpa centang biru wajib', 'GET /lowongan?verifikasi=tidak_wajib', 'SELECT dengan NOT EXISTS', 'lowongan, syarat_keahlian'],
      ['Daftar lowongan', 'Lihat detail', 'GET /lowongan/{id}', 'SELECT', 'lowongan, perusahaan, syarat_keahlian, keahlian, lamaran'],
      ['Daftar akun', 'Daftar', 'POST /daftar', 'INSERT', 'pengguna, lalu pencari_kerja atau perusahaan'],
      ['Masuk', 'Masuk', 'POST /masuk', 'SELECT', 'pengguna'],
    ],
  },
  {
    peran: 'Pencari Kerja',
    pengantar:
      'Pencari kerja mengelola portofolionya sendiri. Satu hal yang perlu diperhatikan: mengganti berkas PDF sertifikat akan memperbarui kolom bukti.diunggah_pada, dan pembaruan itu otomatis mencabut centang biru sampai sertifikatnya diperiksa ulang verifikator.',
    gambar: [
      ['pencari-dashboard', 'Ringkasan pencari kerja'],
      ['pencari-profil', 'Halaman ubah profil pencari kerja'],
      ['pencari-keahlian', 'Daftar keahlian beserta status centang biru'],
      ['pencari-keahlian-form', 'Formulir tambah keahlian'],
      ['pencari-sertifikat', 'Daftar sertifikat beserta status pemeriksaan'],
      ['pencari-sertifikat-form', 'Formulir unggah sertifikat'],
      ['pencari-cari-lowongan', 'Halaman cari lowongan beserta skor kecocokan'],
      ['pencari-lamaran', 'Daftar lamaran yang sudah dikirim'],
      ['pencari-lamaran-detail', 'Detail lamaran beserta tahap seleksi'],
    ],
    aksi: [
      ['Ringkasan', 'Membuka halaman', 'GET /pencari/dashboard', 'SELECT', 'pencari_kerja, klaim_keahlian, v_status_keahlian, bukti, v_verifikasi_terbaru, lamaran'],
      ['Profil', 'Simpan perubahan', 'PUT /pencari/profil', 'UPDATE', 'pengguna, pencari_kerja'],
      ['Keahlian', 'Tambah keahlian', 'POST /pencari/keahlian', 'INSERT', 'klaim_keahlian'],
      ['Keahlian', 'Ubah level', 'PUT /pencari/keahlian/{id}', 'UPDATE', 'klaim_keahlian'],
      ['Keahlian', 'Hapus', 'DELETE /pencari/keahlian/{id}', 'DELETE', 'klaim_keahlian, lalu bukti_keahlian ikut terhapus lewat ON DELETE CASCADE'],
      ['Sertifikat', 'Unggah sertifikat', 'POST /pencari/sertifikat', 'INSERT', 'bukti, berkas PDF disimpan ke File Storage'],
      ['Sertifikat', 'Ganti berkas', 'PUT /pencari/sertifikat/{id}', 'UPDATE', 'bukti, kolom diunggah_pada diperbarui sehingga centang birunya tercabut'],
      ['Sertifikat', 'Hapus', 'DELETE /pencari/sertifikat/{id}', 'DELETE', 'bukti, lalu verifikasi dan bukti_keahlian ikut terhapus lewat ON DELETE CASCADE'],
      ['Cari lowongan', 'Kotak pencarian', 'GET /pencari/lowongan?q=', 'SELECT dengan MATCH AGAINST', 'lowongan, perusahaan'],
      ['Cari lowongan', 'Lamar', 'POST /pencari/lowongan/{id}/lamar', 'INSERT', 'lamaran, skor_kecocokan dihitung dari syarat_keahlian dan v_status_keahlian'],
      ['Lamaran', 'Lihat detail', 'GET /pencari/lamaran/{id}', 'SELECT', 'lamaran, lowongan, perusahaan, tahap_seleksi'],
      ['Lamaran', 'Tarik lamaran', 'PATCH /pencari/lamaran/{id}/tarik', 'UPDATE', 'lamaran, statusnya diubah jadi ditarik dan barisnya tidak dihapus'],
    ],
  },
  {
    peran: 'Perusahaan',
    pengantar:
      'Perusahaan mengelola lowongannya sendiri beserta proses seleksinya. Lowongan cuma bisa dipublikasikan kalau sudah punya minimal satu syarat keahlian, dan CHECK constraint memastikan kolom tanggal publikasinya ikut terisi saat statusnya berubah.',
    gambar: [
      ['perusahaan-dashboard', 'Ringkasan perusahaan'],
      ['perusahaan-lowongan', 'Daftar lowongan milik perusahaan'],
      ['perusahaan-lowongan-form', 'Formulir lowongan beserta pengaturan syarat keahlian'],
      ['perusahaan-pelamar', 'Daftar pelamar yang diurutkan berdasarkan skor kecocokan'],
      ['perusahaan-pelamar-detail', 'Detail pelamar beserta rincian pemenuhan syarat'],
    ],
    aksi: [
      ['Ringkasan', 'Membuka halaman', 'GET /perusahaan/dashboard', 'SELECT', 'perusahaan, lowongan, lamaran'],
      ['Profil', 'Simpan perubahan', 'PUT /perusahaan/profil', 'UPDATE', 'pengguna, perusahaan'],
      ['Lowongan', 'Buat lowongan', 'POST /perusahaan/lowongan', 'INSERT', 'lowongan'],
      ['Lowongan', 'Simpan dan publikasikan', 'PUT /perusahaan/lowongan/{id}', 'UPDATE', 'lowongan, kolom dipublikasikan_pada diisi'],
      ['Lowongan', 'Hapus', 'DELETE /perusahaan/lowongan/{id}', 'DELETE', 'lowongan, lalu syarat_keahlian, lamaran, dan tahap_seleksi ikut terhapus lewat ON DELETE CASCADE'],
      ['Formulir lowongan', 'Tambah syarat keahlian', 'POST /perusahaan/lowongan/{id}/syarat', 'INSERT', 'syarat_keahlian'],
      ['Formulir lowongan', 'Hapus syarat', 'DELETE /perusahaan/syarat/{id}', 'DELETE', 'syarat_keahlian'],
      ['Daftar pelamar', 'Membuka halaman', 'GET /perusahaan/lowongan/{id}/pelamar', 'SELECT', 'lamaran, pencari_kerja, pengguna, klaim_keahlian, v_status_keahlian'],
      ['Detail pelamar', 'Ubah status lamaran', 'PATCH /perusahaan/pelamar/{id}/status', 'UPDATE', 'lamaran'],
      ['Detail pelamar', 'Tambah tahap seleksi', 'POST /perusahaan/pelamar/{id}/tahap', 'INSERT', 'tahap_seleksi'],
      ['Detail pelamar', 'Ubah tahap seleksi', 'PUT /perusahaan/tahap/{id}', 'UPDATE', 'tahap_seleksi'],
      ['Detail pelamar', 'Hapus tahap seleksi', 'DELETE /perusahaan/tahap/{id}', 'DELETE', 'tahap_seleksi'],
    ],
  },
  {
    peran: 'Verifikator',
    pengantar:
      'Verifikator cuma melihat sertifikat yang pemiliknya mengklaim keahlian di dalam bidang kewenangannya. Menyimpan keputusan tidak pernah menimpa baris lama, tetapi menambah baris baru di tabel verifikasi, jadi riwayat pemeriksaannya selalu bisa ditelusuri.',
    gambar: [
      ['verifikator-dashboard', 'Antrean sertifikat yang menunggu pemeriksaan'],
      ['verifikator-periksa', 'Halaman pemeriksaan sertifikat beserta pratinjau PDF'],
      ['verifikator-riwayat', 'Riwayat keputusan verifikator'],
      ['verifikator-kewenangan', 'Daftar kewenangan keahlian verifikator'],
    ],
    aksi: [
      ['Antrean', 'Membuka halaman', 'GET /verifikator/dashboard', 'SELECT', 'bukti, v_verifikasi_terbaru, kewenangan_verifikator, klaim_keahlian'],
      ['Antrean', 'Periksa', 'GET /verifikator/periksa/{bukti}', 'SELECT', 'bukti, jenis_bukti, klaim_keahlian, v_status_keahlian, verifikasi'],
      ['Pemeriksaan', 'Setujui', 'POST /verifikator/periksa/{bukti}', 'INSERT ke dua tabel dalam satu transaksi', 'verifikasi, lalu bukti_keahlian untuk tiap keahlian yang dicentang'],
      ['Pemeriksaan', 'Tolak', 'POST /verifikator/periksa/{bukti}', 'INSERT', 'verifikasi, kolom catatan wajib diisi'],
      ['Pemeriksaan', 'Tunda', 'POST /verifikator/periksa/{bukti}', 'INSERT', 'verifikasi dengan keputusan menunggu'],
      ['Pemeriksaan', 'Buka PDF', 'GET /berkas/bukti/{bukti}', 'SELECT lalu membaca berkas', 'bukti, berkasnya dibaca dari File Storage setelah hak aksesnya dicek'],
      ['Riwayat', 'Membuka halaman', 'GET /verifikator/riwayat', 'SELECT', 'verifikasi, bukti, pengguna'],
      ['Kewenangan', 'Membuka halaman', 'GET /verifikator/kewenangan', 'SELECT', 'kewenangan_verifikator, keahlian'],
    ],
  },
  {
    peran: 'Administrator',
    pengantar:
      'Administrator merawat data acuan dan status akun. Data acuan yang masih dipakai tidak bisa dihapus karena ditolak ON DELETE RESTRICT, dan aplikasinya menampilkan pesan yang menyarankan menonaktifkan data itu saja.',
    gambar: [
      ['admin-dashboard', 'Ringkasan administrator'],
      ['admin-pengguna', 'Halaman kelola akun pengguna'],
      ['admin-verifikator-form', 'Formulir verifikator beserta pemilihan kewenangan keahlian'],
      ['admin-keahlian', 'Halaman data acuan keahlian'],
    ],
    aksi: [
      ['Ringkasan', 'Membuka halaman', 'GET /admin/dashboard', 'SELECT dengan COUNT', 'semua tabel utama'],
      ['Kelola pengguna', 'Tambah akun', 'POST /admin/pengguna', 'INSERT', 'pengguna, beserta tabel profil sesuai peran yang dipilih'],
      ['Kelola pengguna', 'Ubah akun dan status', 'PUT /admin/pengguna/{id}', 'UPDATE', 'pengguna'],
      ['Kelola pengguna', 'Hapus akun', 'DELETE /admin/pengguna/{id}', 'DELETE', 'pengguna, ditolak kalau masih terhubung profil perusahaan atau riwayat verifikasi'],
      ['Kelola verifikator', 'Simpan kewenangan', 'PUT /admin/verifikator/{id}', 'UPDATE dan sinkronisasi', 'verifikator, kewenangan_verifikator'],
      ['Kelola perusahaan', 'Ubah status', 'PUT /admin/perusahaan/{id}', 'UPDATE', 'perusahaan'],
      ['Data keahlian', 'Tambah, ubah, hapus', 'POST, PUT, DELETE /admin/keahlian', 'INSERT, UPDATE, DELETE', 'keahlian, penghapusan ditolak kalau masih dipakai klaim atau syarat'],
      ['Data jenis bukti', 'Tambah, ubah, hapus', 'POST, PUT, DELETE /admin/jenis-bukti', 'INSERT, UPDATE, DELETE', 'jenis_bukti, penghapusan ditolak kalau masih dipakai sertifikat'],
    ],
  },
];

const PENGUJIAN = {
  kelayakan: {
    pengantar:
      'Kelayakan basis data kami uji pakai skrip database/uji_kelayakan.php yang menjalankan 62 pemeriksaan dalam tujuh kelompok. Semua pemeriksaan yang menulis dibungkus transaksi lalu dibatalkan lagi, jadi isi basis datanya tidak berubah. Hasilnya 62 pemeriksaan lulus dan tidak ada yang gagal.',
    baris: [
      ['1. Struktur skema', '7', 'Kelengkapan 15 tabel dan 2 view, mesin penyimpanan InnoDB, karakter utf8mb4, keberadaan primary key di tiap tabel, dan indeks penopang buat tiap foreign key.'],
      ['2. Integritas referensial', '12', 'Mencari baris yatim di tiap relasi induk dan anak.'],
      ['3. Penegakan constraint', '17', 'Mencoba menulis data yang seharusnya ditolak: pelanggaran UNIQUE, foreign key, ON DELETE RESTRICT, delapan CHECK constraint, dan keempat trigger.'],
      ['4. Konsistensi data', '7', 'Mengecek data yang sudah tersimpan tidak melanggar aturan antarkolom maupun aturan kewenangan verifikator.'],
      ['5. Perilaku view centang biru', '7', 'Penggantian berkas mencabut centang biru, keahlian nonaktif tidak terhitung, verifikasi kedaluwarsa tidak memberi centang, dan klaim nonaktif tidak muncul.'],
      ['6. Pemakaian indeks', '7', 'Mengecek rencana eksekusi buat memastikan tiap query halaman utama benar-benar memakai indeks yang dirancang untuknya.'],
      ['7. Bentuk normal', '5', 'Mengecek tidak ada kolom bernilai jamak, ketergantungan parsial di tabel berkunci gabungan, kolom status turunan, dan kolom agregat.'],
    ],
  },
  fungsional: {
    pengantar:
      'Pengujian fungsional kami jalankan pakai php artisan test dengan basis data terpisah bernama sibuker_pt_test. Basis data itu dibangun ulang dari migration tiap kali pengujian jalan, jadi view, CHECK constraint, dan trigger ikut teruji. Semua 15 pengujian dengan 107 asersi lulus.',
    baris: [
      ['Halaman publik terbuka', 'Semua halaman publik bisa diakses, dan lowongan berstatus draft mengembalikan 404.'],
      ['Halaman panel per peran', 'Tiap peran bisa membuka semua halaman miliknya.'],
      ['Hak akses dijaga', 'Peran yang tidak berhak ditolak dengan kode 403.'],
      ['Status centang biru dibaca dari view', 'Nilai centang biru yang ditampilkan memang berasal dari v_status_keahlian.'],
      ['Masuk dan pilih peran', 'Akun yang punya lebih dari satu peran diarahkan ke halaman pemilihan peran.'],
      ['Pendaftaran perusahaan', 'Pendaftaran membuat satu baris pengguna dan satu baris perusahaan.'],
      ['Melamar menyimpan skor dan menolak duplikat', 'Skor kecocokan tersimpan, dan lamaran kedua di lowongan yang sama ditolak UNIQUE constraint.'],
      ['Verifikasi disetujui memberi centang biru', 'Persetujuan verifikator langsung mengubah status di view.'],
      ['Publikasi butuh syarat', 'Lowongan tanpa syarat keahlian tidak bisa dipublikasikan.'],
      ['Hapus data acuan yang dipakai ditolak', 'ON DELETE RESTRICT bekerja dan aplikasinya menampilkan pesan yang sesuai.'],
      ['Mengganti berkas mencabut centang biru', 'Sertifikat yang berkasnya diganti kembali masuk antrean dan kehilangan centang birunya.'],
      ['Penautan lintas pemilik ditolak', 'Trigger menolak sertifikat satu pengguna membuktikan klaim pengguna lain.'],
      ['Verifikator tidak memeriksa miliknya sendiri', 'Ditolak controller sekaligus ditolak trigger di tingkat basis data.'],
      ['Keahlian nonaktif tidak terverifikasi', 'Klaim pada keahlian yang dinonaktifkan tidak lagi bercentang biru.'],
      ['CHECK constraint menjaga konsistensi', 'Kombinasi kolom yang tidak konsisten ditolak basis data.'],
    ],
  },
};

const PUSTAKA = [
  'Alfina, O., & Syafrinal, I. (2022). Perancangan Sistem Verifikasi Ijazah Digital Berbasis Web. Jurnal Sistem Informasi dan Teknologi Informasi.',
  'Codd, E. F. (1970). A Relational Model of Data for Large Shared Data Banks. Communications of the ACM, 13(6), 377-387.',
  'Connolly, T., & Begg, C. (2015). Database Systems: A Practical Approach to Design, Implementation, and Management (6th ed.). Pearson Education.',
  'Elmasri, R., & Navathe, S. B. (2016). Fundamentals of Database Systems (7th ed.). Pearson Education.',
  'Oracle Corporation. (2024). MySQL 8.4 Reference Manual. Oracle Corporation.',
  'Rayhan, M., & Utami, E. (2024). Sistem Informasi Bursa Kerja Khusus Berbasis Web. Jurnal Teknologi Informasi dan Ilmu Komputer.',
  'Restaldo, A., & Beeh, Y. R. (2022). Perancangan Sistem Informasi Berbasis Web Menggunakan Framework Laravel. Jurnal Informatika.',
  'United Nations. (2015). Transforming Our World: The 2030 Agenda for Sustainable Development. United Nations General Assembly.',
];

module.exports = {
  IDENTITAS, BAB1, BAB2, PERAN, KOMPONEN, RELASI_KONSEPTUAL, MODUL_ERD,
  NORMALISASI, DENORMALISASI, VIEW_PENJELASAN, TRIGGER_PENJELASAN, BENCHMARK,
  MATRIKS_AKSES, ALUR_PERAN, PENGUJIAN, PUSTAKA,
};
