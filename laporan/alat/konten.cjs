/**
 * Isi naratif laporan SIBUKER-PT.
 *
 * Berkas ini hanya berisi teks dan data pemetaan. Seluruh angka yang berasal dari
 * basis data (kamus data, foreign key, indeks, constraint) TIDAK ditulis di sini,
 * melainkan dibaca buat_laporan.cjs dari laporan/skema.json agar tidak pernah basi.
 */

const IDENTITAS = {
  judul: 'SISTEM INFORMASI BURSA KERJA BERBASIS PORTOFOLIO DAN KEAHLIAN TERVERIFIKASI',
  akronim: 'SIBUKER-PT',
  matkul: 'Laporan Tugas Ujian Tengah Semester',
  matkulNama: 'Mata Kuliah Basis Data',
  dosen: ['Dr. Aziz Fajar, S.Kom., M.Sc.'],
  kelompok: 'Kelompok F — Kelas SD-A2',
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
    'Perkembangan dunia kerja saat ini membuat kebutuhan perusahaan terhadap tenaga kerja semakin berorientasi pada keterampilan. Namun demikian, informasi keterampilan yang dicantumkan oleh pencari kerja pada profil atau curriculum vitae umumnya masih bersifat klaim pribadi tanpa bukti yang memadai. Dalam kondisi tersebut perusahaan mengalami kesulitan membedakan keterampilan yang telah memiliki bukti pendukung dari keterampilan yang belum terbukti.',
    'Sertifikat dapat digunakan sebagai salah satu bukti keterampilan, tetapi tidak semua pencari kerja memiliki sertifikat untuk setiap kemampuan yang dikuasainya. Keterampilan juga dapat diperoleh melalui pengalaman kerja, proyek pribadi, pelatihan, maupun pembelajaran mandiri. Oleh karena itu diperlukan sistem yang tetap memungkinkan pengguna mencantumkan keterampilan tanpa sertifikat, sekaligus memberikan penanda khusus bagi keterampilan yang didukung oleh sertifikat valid.',
    'Permasalahan tersebut berkaitan dengan Sustainable Development Goal (SDG) 8, khususnya pada aspek perluasan kesempatan memperoleh pekerjaan yang layak dan inklusif bagi individu dengan beragam latar belakang pendidikan dan pengalaman. Sistem rekrutmen yang transparan dan berbasis bukti keterampilan tidak hanya membantu perusahaan menyeleksi tenaga kerja secara lebih objektif, tetapi juga membuka peluang yang lebih adil bagi pencari kerja untuk menunjukkan kemampuannya, terlepas dari ada atau tidaknya sertifikat formal yang dimilikinya.',
    'Berdasarkan permasalahan tersebut dirancang SIBUKER-PT, yaitu Sistem Informasi Bursa Kerja Berbasis Portofolio Terverifikasi, sebagai solusi untuk menjembatani kebutuhan validasi keterampilan dalam proses pencarian kerja. Melalui sistem ini pengguna dapat mencantumkan berbagai keterampilan dan mengunggah sertifikat secara opsional. Sertifikat diperiksa oleh verifikator, sehingga keterampilan yang berhasil dibuktikan memperoleh tanda centang biru. Perusahaan dapat menentukan kebutuhan verifikasi untuk setiap keterampilan pada lowongan yang dibuka, sedangkan pencari kerja dapat menyaring lowongan berdasarkan persyaratan verifikasi tersebut. Dengan mengintegrasikan data keterampilan, sertifikat, verifikasi, lowongan, dan lamaran, SIBUKER-PT diharapkan mendukung proses rekrutmen yang lebih transparan, fleksibel, dan berbasis kemampuan nyata pencari kerja.',
  ],
  mission:
    'Mewujudkan sistem bursa kerja berbasis keterampilan yang fleksibel, inklusif, dan terpercaya melalui pengelolaan profil keahlian, verifikasi sertifikat, pencocokan persyaratan lowongan, serta penyediaan informasi rekrutmen yang terintegrasi bagi pencari kerja dan perusahaan.',
  objectives: [
    'Menyediakan pengelolaan profil pencari kerja secara terintegrasi, meliputi informasi pribadi, keterampilan, portofolio, dan sertifikat pendukung.',
    'Menyediakan mekanisme verifikasi sertifikat untuk memberikan centang biru pada keterampilan yang didukung bukti valid, tanpa mewajibkan sertifikat untuk seluruh keterampilan.',
    'Memfasilitasi perusahaan dalam mengelola lowongan pekerjaan serta menentukan keterampilan wajib atau opsional beserta kebutuhan verifikasinya.',
    'Mencocokkan keterampilan pencari kerja dengan persyaratan lowongan serta menyediakan filter berdasarkan keterampilan dan status verifikasinya.',
    'Mengintegrasikan proses lamaran dan seleksi agar perkembangan setiap lamaran dapat disimpan dan dipantau secara terstruktur.',
    'Mendukung proses rekrutmen yang lebih transparan dan inklusif dengan menempatkan keterampilan beserta bukti pendukungnya sebagai bagian utama penilaian calon pekerja.',
  ],
};

// ---------------------------------------------------------------------------
// BAB II
// ---------------------------------------------------------------------------

const BAB2 = {
  kajian: [
    'Pengembangan sistem informasi bursa kerja telah dilakukan dalam beberapa penelitian sebelumnya. Rayhan dan Utami (2024) mengembangkan sistem informasi Bursa Kerja Khusus berbasis web untuk membantu pengelolaan lowongan dan data pencari kerja. Penelitian tersebut menunjukkan bahwa sistem terintegrasi dapat mempermudah penyampaian informasi lowongan serta pengelolaan proses rekrutmen.',
    'Dalam aspek verifikasi dokumen, Alfina dan Syafrinal (2022) membahas sistem verifikasi ijazah digital untuk memastikan keaslian dokumen. Sementara itu Restaldo dan Beeh (2022) menunjukkan bahwa Laravel dapat digunakan untuk membangun sistem informasi berbasis web dengan struktur aplikasi yang terorganisasi. Kedua penelitian tersebut menjadi acuan dalam penerapan pemeriksaan sertifikat dan pengembangan aplikasi SIBUKER-PT.',
    'SIBUKER-PT mengembangkan konsep tersebut melalui pencocokan lowongan berbasis keahlian. Pencari kerja dapat mencantumkan keahlian tanpa sertifikat, tetapi dapat mengunggah sertifikat dalam bentuk PDF sebagai bukti pendukung. Verifikator kemudian memeriksa keaslian sertifikat dan menentukan keahlian yang dibuktikannya. Perusahaan dapat menetapkan apakah setiap keahlian dalam lowongan harus terverifikasi atau tidak. Dengan demikian SIBUKER-PT menggabungkan pengelolaan lowongan, verifikasi sertifikat, dan pencocokan berbasis keahlian dalam satu sistem.',
  ],
  teori: [
    {
      judul: 'Basis Data dan Sistem Manajemen Basis Data',
      isi: [
        'Basis data merupakan kumpulan data yang saling berhubungan dan disusun secara terstruktur agar dapat digunakan oleh pengguna maupun aplikasi. Pengelolaannya dilakukan melalui Database Management System (DBMS), yaitu perangkat lunak yang menyediakan fasilitas untuk mendefinisikan, menyimpan, mengubah, mengambil, dan mengendalikan akses terhadap data (Connolly dan Begg, 2015).',
        'Penggunaan DBMS membantu mengurangi duplikasi, menjaga konsistensi, dan mengatur hubungan antardata. Dalam SIBUKER-PT basis data digunakan untuk mengintegrasikan data pengguna, keahlian, sertifikat, verifikasi, perusahaan, lowongan, lamaran, dan tahapan seleksi. MySQL dipilih sebagai DBMS karena mendukung model relasional, menyediakan penegakan integritas berupa foreign key, CHECK constraint, view, dan trigger, serta sesuai dengan framework Laravel yang digunakan.',
      ],
    },
    {
      judul: 'Model Data Relasional',
      isi: [
        'Model data relasional merepresentasikan data dalam bentuk tabel yang terdiri atas baris dan kolom. Setiap tabel menggambarkan suatu entitas, sedangkan setiap baris merepresentasikan satu data dari entitas tersebut. Hubungan antartabel dibentuk menggunakan primary key dan foreign key (Codd, 1970).',
        'Dalam SIBUKER-PT model relasional digunakan karena data yang dikelola memiliki hubungan yang jelas. Sebagai contoh, pencari kerja terhubung dengan klaim keahlian, perusahaan terhubung dengan lowongan, dan lowongan terhubung dengan persyaratan keahlian. Model ini juga memungkinkan penerapan aturan integritas untuk mencegah data yang tidak konsisten atau tidak memiliki hubungan yang sah.',
      ],
    },
    {
      judul: 'Entity Relationship Diagram',
      isi: [
        'Entity Relationship Diagram (ERD) merupakan notasi pemodelan data yang menggambarkan entitas, atribut, dan relasi dalam suatu sistem basis data (Elmasri dan Navathe, 2016). ERD digunakan sebagai dasar untuk memahami struktur data sebelum basis data diimplementasikan secara fisik.',
        'Perancangan SIBUKER-PT memakai dua tingkat ERD. ERD konseptual menggambarkan entitas dan relasi tanpa memperlihatkan kolom, tipe data, maupun kunci, sehingga dapat dibaca oleh pihak yang tidak berlatar belakang teknis. ERD fisik menggambarkan struktur tabel yang benar-benar diterapkan pada MySQL, lengkap dengan tipe data, primary key, foreign key, dan unique constraint.',
      ],
      butir: [
        'Entitas, yaitu objek yang datanya disimpan dalam sistem, seperti pengguna, keahlian, bukti, lowongan, dan lamaran.',
        'Atribut, yaitu informasi yang menjelaskan suatu entitas, seperti nama dan email pada pengguna atau posisi dan lokasi pada lowongan.',
        'Primary key, yaitu atribut yang membedakan setiap baris dalam satu tabel.',
        'Foreign key, yaitu atribut yang menghubungkan suatu tabel dengan tabel lain.',
        'Kardinalitas, yaitu jumlah keterhubungan antarentitas, misalnya satu perusahaan dapat membuka banyak lowongan.',
        'Entitas asosiatif, yaitu entitas yang digunakan untuk menyelesaikan hubungan banyak ke banyak. Pada SIBUKER-PT peran tersebut dijalankan oleh bukti_keahlian, kewenangan_verifikator, syarat_keahlian, dan lamaran.',
      ],
    },
    {
      judul: 'Normalisasi',
      isi: [
        'Normalisasi adalah proses penyusunan struktur relasi untuk mengurangi redundansi data serta menghilangkan anomali penyisipan, pembaruan, dan penghapusan (Codd, 1970; Connolly dan Begg, 2015). Bentuk normal pertama (1NF) mensyaratkan setiap atribut bernilai atomik dan tidak mengandung kelompok berulang. Bentuk normal kedua (2NF) mensyaratkan terpenuhinya 1NF serta tidak adanya ketergantungan parsial atribut bukan kunci terhadap sebagian primary key majemuk. Bentuk normal ketiga (3NF) mensyaratkan terpenuhinya 2NF serta tidak adanya ketergantungan transitif antaratribut bukan kunci.',
        'Perancangan basis data SIBUKER-PT memenuhi 3NF. Bukti pemenuhan setiap bentuk normal beserta contoh pelanggaran yang dihindari diuraikan pada subbab 3.3.4.',
      ],
    },
    {
      judul: 'Keahlian dan Verifikasi Sertifikat',
      isi: [
        'Keahlian merupakan kemampuan yang dimiliki seseorang dalam melaksanakan pekerjaan atau aktivitas tertentu. Keahlian dapat diperoleh melalui pendidikan formal, pelatihan, pengalaman kerja, proyek pribadi, maupun pembelajaran mandiri. Oleh karena itu tidak seluruh keahlian harus disertai sertifikat agar dapat dicantumkan pada profil.',
        'Dalam SIBUKER-PT pencari kerja mencantumkan keahlian melalui tabel klaim_keahlian. Sertifikat dapat diunggah secara opsional dalam bentuk PDF melalui tabel bukti. Verifikator kemudian memeriksa keaslian sertifikat dan menentukan keahlian yang benar-benar dibuktikan oleh dokumen tersebut. Satu sertifikat dapat membuktikan beberapa keahlian sekaligus melalui tabel penghubung bukti_keahlian.',
        'Status terverifikasi atau centang biru tidak diisi langsung oleh pencari kerja. Status tersebut dihitung dari hasil verifikasi terbaru yang disetujui dan masih berlaku. Jika bukti tidak disetujui, masa verifikasinya berakhir, atau berkas PDF-nya diganti setelah pemeriksaan, keahlian tetap dapat ditampilkan pada profil tetapi tidak memperoleh centang biru. Dengan demikian sistem tetap memberi ruang bagi keahlian yang belum memiliki sertifikat tanpa menyamakannya dengan keahlian yang telah dibuktikan.',
        'Portofolio dalam SIBUKER-PT dipahami sebagai kumpulan informasi profil, keahlian, dan sertifikat milik pencari kerja. Portofolio tidak disimpan sebagai satu entitas tersendiri, melainkan terbentuk dari data yang saling terhubung di dalam basis data.',
      ],
    },
    {
      judul: 'Pencocokan Berbasis Keahlian',
      isi: [
        'Pencocokan berbasis keahlian merupakan proses membandingkan keahlian yang dimiliki pencari kerja dengan persyaratan suatu lowongan. Pendekatan ini memungkinkan sistem menilai kesesuaian kandidat berdasarkan kemampuan yang relevan, bukan hanya berdasarkan kata kunci pada judul pekerjaan atau latar belakang pendidikan.',
        'Setiap lowongan dalam SIBUKER-PT dapat memiliki beberapa persyaratan melalui tabel syarat_keahlian. Perusahaan menentukan keahlian yang dibutuhkan, level minimum, sifat wajib atau opsional, bobot, dan kebutuhan verifikasinya. Keahlian yang diwajibkan terverifikasi hanya dianggap memenuhi persyaratan apabila memiliki centang biru yang masih berlaku, sedangkan keahlian yang tidak mewajibkan verifikasi cukup dipenuhi oleh klaim keahlian pengguna.',
        'Hasil perbandingan tersebut dipakai untuk menghitung skor kecocokan dengan rumus: skor sama dengan jumlah bobot syarat yang terpenuhi dibagi total bobot seluruh syarat, dikalikan seratus. Nilai skor disimpan pada kolom lamaran.skor_kecocokan pada saat lamaran dikirim.',
      ],
    },
    {
      judul: 'Arsitektur Model-View-Controller dan Framework Laravel',
      isi: [
        'Model-View-Controller (MVC) adalah pola arsitektur perangkat lunak yang memisahkan aplikasi menjadi tiga lapisan, yaitu model yang menangani data dan aturan bisnis, view yang menangani penyajian antarmuka, serta controller yang menjembatani masukan pengguna dengan pemrosesan pada model. Pemisahan ini meningkatkan keterbacaan kode dan mempermudah pemeliharaan sistem yang memiliki banyak relasi data.',
        'Laravel merupakan framework PHP yang menerapkan pola MVC dan menyediakan fitur pendukung seperti Eloquent ORM untuk pemetaan objek relasional, migration untuk pengelolaan versi skema basis data, seeder untuk pengisian data contoh, serta middleware untuk pembatasan hak akses. Restaldo dan Beeh (2022) menyimpulkan bahwa fitur-fitur tersebut mempercepat pembangunan sistem informasi berbasis basis data relasional sekaligus menjaga konsistensi struktur data selama pengembangan. Dalam penelitian ini migration digunakan untuk mewujudkan rancangan fisik basis data, sedangkan middleware digunakan untuk menegakkan pembatasan kewenangan antarperan pengguna.',
      ],
    },
    {
      judul: 'Keterkaitan dengan Sustainable Development Goals',
      isi: [
        'Sustainable Development Goals merupakan agenda pembangunan global yang ditetapkan oleh Perserikatan Bangsa-Bangsa. Tujuan kedelapan atau SDG 8 berfokus pada pertumbuhan ekonomi yang inklusif, kesempatan kerja produktif, dan pekerjaan yang layak bagi seluruh masyarakat. Target 8.5 secara khusus menekankan tercapainya pekerjaan penuh dan produktif serta pekerjaan layak bagi semua orang.',
        'SIBUKER-PT mendukung tujuan tersebut dengan membantu pencari kerja menunjukkan keahlian yang diperoleh melalui berbagai jalur, termasuk pelatihan, pengalaman, dan pembelajaran mandiri. Mekanisme verifikasi sertifikat memberikan informasi tambahan mengenai keahlian yang telah dibuktikan, sedangkan fleksibilitas persyaratan lowongan memungkinkan perusahaan tetap menerima kandidat dengan keahlian yang belum terverifikasi. Dengan demikian sistem diharapkan memperluas kesempatan kerja serta mendukung proses rekrutmen yang lebih transparan dan inklusif.',
      ],
    },
  ],
};

// ---------------------------------------------------------------------------
// BAB III
// ---------------------------------------------------------------------------

const PERAN = [
  ['Tamu', 'Tanpa login', 'Melihat beranda, mencari dan membuka detail lowongan yang dipublikasikan, mendaftar, dan masuk.'],
  ['Pencari kerja', 'Mendaftar sendiri melalui halaman Daftar', 'Mengelola profil, mencantumkan keahlian, mengunggah sertifikat PDF, melihat status centang biru, mencari lowongan beserta skor kecocokan, melamar, memantau dan menarik lamaran.'],
  ['Perusahaan', 'Mendaftar sendiri; status aktif dikendalikan administrator', 'Mengelola profil perusahaan, membuat lowongan dan syarat keahlian, mempublikasikan atau menutup lowongan, meninjau pelamar, mengubah status lamaran, dan mengatur tahap seleksi.'],
  ['Verifikator', 'Dibuat administrator beserta kewenangannya', 'Melihat antrean sertifikat sesuai kewenangan, memeriksa berkas, memutuskan disetujui atau ditolak, memilih keahlian yang terbukti, dan menentukan masa berlaku.'],
  ['Administrator', 'Ditandai kolom pengguna.is_admin', 'Mengelola akun pengguna, verifikator beserta kewenangannya, status perusahaan, serta data acuan keahlian dan jenis bukti.'],
];

const KOMPONEN = [
  ['Antarmuka', 'Blade, HTML, CSS, JavaScript', 'Menyusun halaman yang dilihat pengguna. Tidak mengakses basis data secara langsung.'],
  ['Routing', 'routes/web.php', 'Memetakan URL ke controller. Dibagi lima kelompok: publik, pencari kerja, perusahaan, verifikator, administrator.'],
  ['Kontrol akses', 'Middleware auth dan peran', 'Memeriksa apakah akun yang login memiliki profil peran yang dibutuhkan. Permintaan yang tidak berhak dihentikan dengan kode 403.'],
  ['Logika aplikasi', 'Controller dan kelas Kecocokan', 'Memvalidasi masukan, menjalankan aturan bisnis, dan menghitung skor kecocokan.'],
  ['Akses data', 'Model Eloquent', 'Menerjemahkan operasi objek menjadi perintah SQL. Satu model mewakili satu tabel.'],
  ['Penyimpanan data', 'MySQL 8.4', 'Menyimpan seluruh data sistem serta menegakkan integritas melalui foreign key, CHECK constraint, view, dan trigger.'],
  ['Penyimpanan berkas', 'Laravel File Storage', 'Menyimpan berkas PDF sertifikat. Basis data hanya menyimpan lokasi berkas pada kolom bukti.url_berkas.'],
];

const RELASI_KONSEPTUAL = [
  ['Pengguna — Pencari Kerja', 'Memiliki profil', '1 : 1', 'Satu akun dapat memiliki paling banyak satu profil pencari kerja.'],
  ['Pengguna — Perusahaan', 'Memiliki profil', '1 : 1', 'Satu akun dapat memiliki paling banyak satu profil perusahaan.'],
  ['Pengguna — Verifikator', 'Memiliki profil', '1 : 1', 'Satu akun dapat memiliki paling banyak satu profil verifikator.'],
  ['Pencari Kerja — Klaim Keahlian', 'Mencantumkan', '1 : N', 'Satu pencari kerja dapat mencantumkan banyak keahlian.'],
  ['Keahlian — Klaim Keahlian', 'Diklaim pada', '1 : N', 'Satu keahlian dapat diklaim oleh banyak pencari kerja.'],
  ['Pengguna — Bukti', 'Mengunggah', '1 : N', 'Satu akun dapat mengunggah banyak sertifikat.'],
  ['Jenis Bukti — Bukti', 'Mengelompokkan', '1 : N', 'Satu jenis bukti dapat dipakai oleh banyak sertifikat.'],
  ['Bukti — Verifikasi', 'Diperiksa lewat', '1 : N', 'Satu sertifikat dapat memiliki beberapa riwayat pemeriksaan.'],
  ['Verifikator — Verifikasi', 'Memutuskan', '1 : N', 'Satu verifikator dapat melakukan banyak pemeriksaan.'],
  ['Verifikator — Keahlian', 'Berwenang atas', 'M : N', 'Diwujudkan tabel kewenangan_verifikator.'],
  ['Verifikasi — Klaim Keahlian', 'Membuktikan', 'M : N', 'Diwujudkan tabel bukti_keahlian. Satu sertifikat dapat membuktikan beberapa keahlian.'],
  ['Perusahaan — Lowongan', 'Membuka', '1 : N', 'Satu perusahaan dapat membuka banyak lowongan.'],
  ['Lowongan — Syarat Keahlian', 'Menetapkan', '1 : N', 'Satu lowongan dapat menetapkan banyak persyaratan keahlian.'],
  ['Keahlian — Syarat Keahlian', 'Dipakai pada', '1 : N', 'Satu keahlian dapat dipakai pada banyak lowongan.'],
  ['Lowongan — Lamaran', 'Menerima', '1 : N', 'Satu lowongan dapat menerima banyak lamaran.'],
  ['Pencari Kerja — Lamaran', 'Mengirim', '1 : N', 'Satu pencari kerja dapat mengirim banyak lamaran.'],
  ['Lamaran — Tahap Seleksi', 'Memiliki', '1 : N', 'Satu lamaran dapat memiliki beberapa tahap seleksi berurutan.'],
];

const MODUL_ERD = [
  ['fisik-pengguna', 'Modul pengguna dan profil', 'Tabel pengguna menjadi pusat akun. Tiga tabel spesialisasi menyimpan atribut yang hanya relevan bagi peran masing-masing. Karena kolom pengguna_id pada ketiganya berstatus UNIQUE, satu akun hanya dapat memiliki paling banyak satu profil untuk tiap peran.'],
  ['fisik-keahlian', 'Modul keahlian dan verifikasi', 'Klaim keahlian menghubungkan pencari kerja dengan data acuan keahlian. Sertifikat disimpan pada tabel bukti, hasil pemeriksaannya pada tabel verifikasi, dan keahlian yang benar-benar terbukti dihubungkan melalui tabel penghubung bukti_keahlian.'],
  ['fisik-lowongan', 'Modul lowongan dan persyaratan', 'Perusahaan membuka lowongan, dan setiap lowongan menetapkan persyaratan keahlian beserta level minimum, sifat, bobot, dan kebutuhan verifikasinya.'],
  ['fisik-lamaran', 'Modul lamaran dan seleksi', 'Lamaran menghubungkan lowongan dengan pencari kerja dan menyimpan potret skor kecocokan. Perkembangan seleksi dicatat pada tahap_seleksi yang terurut melalui kolom urutan.'],
];

const NORMALISASI = [
  {
    bentuk: 'Bentuk Normal Pertama (1NF)',
    syarat: 'Setiap atribut bernilai atomik dan tidak mengandung kelompok berulang.',
    pelanggaran: 'Menyimpan keahlian sebagai satu kolom teks pada profil pencari kerja, misalnya kolom keahlian berisi "SQL, Python, Visualisasi Data".',
    penerapan: 'Keahlian dipisahkan ke tabel acuan keahlian, dan kepemilikannya dicatat sebagai satu baris per keahlian pada tabel klaim_keahlian. Tidak ada kolom pada seluruh 15 tabel yang menyimpan daftar bernilai jamak.',
    akibat: 'Tanpa pemisahan ini, mencari seluruh pelamar yang menguasai SQL harus dilakukan dengan pencocokan teks, dan memperbaiki penulisan satu nama keahlian harus mengubah setiap baris profil yang memuatnya.',
  },
  {
    bentuk: 'Bentuk Normal Kedua (2NF)',
    syarat: 'Memenuhi 1NF serta tidak ada atribut bukan kunci yang bergantung hanya pada sebagian primary key majemuk.',
    pelanggaran: 'Menambahkan kolom nama_keahlian pada tabel kewenangan_verifikator yang berkunci gabungan (verifikator_id, keahlian_id). Kolom tersebut hanya bergantung pada keahlian_id, yaitu sebagian dari kunci.',
    penerapan: 'Dua tabel berkunci gabungan pada SIBUKER-PT adalah kewenangan_verifikator dan bukti_keahlian. Tabel bukti_keahlian sama sekali tidak memiliki atribut bukan kunci, sedangkan kewenangan_verifikator hanya memiliki diberikan_pada yang bergantung pada seluruh kunci, karena menyatakan kapan pasangan verifikator dan keahlian itu ditetapkan.',
    akibat: 'Pemisahan ini mencegah satu nama keahlian tersimpan berulang di banyak baris kewenangan, yang akan menimbulkan anomali pembaruan ketika namanya diperbaiki.',
  },
  {
    bentuk: 'Bentuk Normal Ketiga (3NF)',
    syarat: 'Memenuhi 2NF serta tidak ada atribut bukan kunci yang bergantung pada atribut bukan kunci lainnya.',
    pelanggaran: 'Menambahkan kolom terverifikasi pada tabel klaim_keahlian. Nilainya bukan fakta mandiri, melainkan turunan dari keputusan verifikasi terbaru beserta masa berlakunya.',
    penerapan: 'Status centang biru tidak disimpan pada tabel mana pun. Nilainya dihitung saat dibaca melalui view v_status_keahlian. Tidak ada pula kolom agregat seperti jumlah pelamar atau total lowongan yang disimpan permanen.',
    akibat: 'Kolom turunan semacam itu akan menjadi salah dengan sendirinya. Ketika tanggal pada kolom verifikasi.berlaku_sampai terlampaui, centang biru seharusnya padam, padahal tidak ada satu pun operasi tulis yang terjadi ke basis data pada saat itu. Dengan view, status selalu dihitung dari keadaan terbaru.',
  },
];

const DENORMALISASI =
  'Satu denormalisasi diterapkan secara sadar, yaitu kolom lamaran.skor_kecocokan. Secara teori nilai tersebut dapat dihitung ulang setiap kali dibutuhkan dari tabel syarat_keahlian dan klaim_keahlian. Namun skor kecocokan adalah potret keadaan pada saat lamaran dikirim. Bila dihitung ulang setiap kali dibaca, nilainya akan berubah ketika pencari kerja menambah keahlian atau memperoleh centang biru baru setelah melamar, sehingga riwayat seleksi tidak lagi dapat dipertanggungjawabkan. Kolom ini karena itu menyimpan fakta historis, bukan menduplikasi fakta yang masih berlaku.';

const VIEW_PENJELASAN = [
  {
    nama: 'v_verifikasi_terbaru',
    tujuan: 'Menghasilkan paling banyak satu baris per sertifikat, yaitu hasil pemeriksaan yang masih berlaku.',
    aturan: [
      'Verifikasi yang dibuat sebelum berkas PDF terakhir diunggah dianggap gugur. Aturan ini membuat penggantian berkas otomatis mencabut hasil pemeriksaan lama.',
      'Dari verifikasi yang tersisa, hanya yang terbaru, yaitu yang memiliki id terbesar, yang dinyatakan berlaku.',
    ],
  },
  {
    nama: 'v_status_keahlian',
    tujuan: 'Menghasilkan status centang biru untuk setiap klaim keahlian yang aktif.',
    aturan: [
      'Klaim dianggap terverifikasi apabila terhubung ke verifikasi terbaru yang berkeputusan disetujui melalui tabel bukti_keahlian.',
      'Verifikasi yang masa berlakunya telah lewat tidak lagi memberi centang biru.',
      'Klaim pada keahlian yang dinonaktifkan administrator tidak ikut ditampilkan.',
    ],
  },
];

const TRIGGER_PENJELASAN = [
  ['trg_bukti_keahlian_insert dan trg_bukti_keahlian_update', 'bukti_keahlian', 'Menolak penautan sertifikat milik satu pengguna ke klaim keahlian milik pengguna lain, serta menolak penautan dari verifikasi yang belum berkeputusan disetujui. Foreign key hanya dapat memastikan kedua baris yang dirujuk ada, tetapi tidak dapat memastikan keduanya bermuara pada pengguna yang sama.'],
  ['trg_verifikasi_insert dan trg_verifikasi_update', 'verifikasi', 'Menolak verifikator yang memeriksa sertifikat miliknya sendiri. Aturan ini diperlukan karena satu akun boleh merangkap sebagai verifikator sekaligus pencari kerja.'],
];

const BENCHMARK = {
  keterangan:
    'Pengujian kinerja dilakukan pada basis data uji berisi 25.000 lowongan, 75.000 lamaran, 45.000 verifikasi, dan 30.000 sertifikat. Setiap query dijalankan lima kali setelah pemanasan, lalu diambil rata-ratanya. Kolom sebelum menunjukkan waktu pada rancangan awal tanpa indeks komposit, dan kolom sesudah menunjukkan waktu pada rancangan akhir.',
  baris: [
    ['Menampilkan daftar lowongan publik', '61,92 ms', '0,34 ms', 'idx_lowongan_publik'],
    ['Mencari lowongan berdasarkan kata kunci', '84,07 ms', '0,51 ms', 'ft_lowongan_pencarian'],
    ['Menghitung keahlian bercentang biru', '1.265,07 ms', '300,53 ms', 'idx_verifikasi_bukti_terbaru'],
    ['Mengurutkan pelamar berdasarkan skor', '0,64 ms', '0,23 ms', 'idx_lamaran_peringkat'],
    ['Menampilkan lamaran milik pencari kerja', '0,88 ms', '0,10 ms', 'idx_lamaran_pencari_waktu'],
    ['Menampilkan sertifikat milik pengguna', '0,82 ms', '0,09 ms', 'idx_bukti_pemilik'],
  ],
  catatanPencarian:
    'Angka pencarian kata kunci diambil pada kasus terburuk, yaitu kata yang tidak ditemukan sama sekali sehingga seluruh tabel harus dipindai bila memakai LIKE. Untuk kata yang sangat umum, LIKE justru lebih cepat karena berhenti setelah menemukan cukup baris. Indeks FULLTEXT dipilih karena membatasi waktu kasus terburuk, bukan karena selalu lebih cepat pada semua kasus.',
  catatanView:
    'Bentuk penulisan view juga berpengaruh besar. Versi v_verifikasi_terbaru yang memakai window function ROW_NUMBER memaksa MySQL memmaterialisasi seluruh tabel verifikasi walaupun query pemanggil hanya membutuhkan satu sertifikat. Membaca status satu klaim memakan 541 ms dengan bentuk tersebut, dibandingkan 0,43 ms dengan bentuk subquery berkorelasi yang akhirnya dipakai.',
};

// Matriks hak akses. C = create, R = read, U = update, D = delete.
const MATRIKS_AKSES = [
  ['pengguna', 'C (daftar)', 'R U (akun sendiri)', 'R U (akun sendiri)', 'R U (akun sendiri)', 'C R U D'],
  ['pencari_kerja', 'C (daftar)', 'R U (milik sendiri)', '—', '—', 'R'],
  ['perusahaan', 'C (daftar)', 'R (profil publik)', 'R U (milik sendiri)', '—', 'R U (status)'],
  ['verifikator', '—', '—', '—', 'R (milik sendiri)', 'C R U D'],
  ['keahlian', 'R', 'R', 'R', 'R', 'C R U D'],
  ['jenis_bukti', '—', 'R', '—', 'R', 'C R U D'],
  ['kewenangan_verifikator', '—', '—', '—', 'R (milik sendiri)', 'C R U D'],
  ['klaim_keahlian', '—', 'C R U D (milik sendiri)', 'R (pelamar saja)', 'R (dalam kewenangan)', 'R'],
  ['bukti', '—', 'C R U D (milik sendiri)', '—', 'R (dalam antrean)', 'R'],
  ['verifikasi', '—', 'R (milik sendiri)', '—', 'C R (dalam kewenangan)', 'R'],
  ['bukti_keahlian', '—', 'R (milik sendiri)', '—', 'C (saat menyetujui)', 'R'],
  ['lowongan', 'R (dipublikasikan)', 'R (dipublikasikan)', 'C R U D (milik sendiri)', '—', 'R'],
  ['syarat_keahlian', 'R', 'R', 'C R D (lowongan sendiri)', '—', 'R'],
  ['lamaran', '—', 'C R U (tarik)', 'R U (status)', '—', 'R'],
  ['tahap_seleksi', '—', 'R (lamaran sendiri)', 'C R U D (pelamarnya)', '—', 'R'],
];

/**
 * Pemetaan tombol dan aksi pada antarmuka ke operasi basis data.
 * Setiap bagian menghasilkan satu subbab berisi tangkapan layar dan satu tabel.
 */
const ALUR_PERAN = [
  {
    peran: 'Tamu dan Halaman Publik',
    pengantar:
      'Pengunjung tanpa akun dapat menelusuri lowongan yang telah dipublikasikan. Seluruh aksi pada kelompok ini hanya membaca data, kecuali pendaftaran akun yang menulis ke dua tabel sekaligus.',
    gambar: [
      ['publik-beranda', 'Halaman beranda SIBUKER-PT'],
      ['publik-lowongan', 'Halaman daftar lowongan beserta panel filter'],
      ['publik-lowongan-detail', 'Halaman detail lowongan beserta persyaratan keahlian'],
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
      'Pencari kerja mengelola portofolionya sendiri. Perlu diperhatikan bahwa mengganti berkas PDF sertifikat memperbarui kolom bukti.diunggah_pada, dan pembaruan itu otomatis mencabut centang biru sampai sertifikat diperiksa ulang oleh verifikator.',
    gambar: [
      ['pencari-dashboard', 'Ringkasan pencari kerja'],
      ['pencari-profil', 'Halaman ubah profil pencari kerja'],
      ['pencari-keahlian', 'Daftar keahlian beserta status centang biru'],
      ['pencari-keahlian-form', 'Formulir tambah keahlian'],
      ['pencari-sertifikat', 'Daftar sertifikat beserta status pemeriksaan'],
      ['pencari-sertifikat-form', 'Formulir unggah sertifikat'],
      ['pencari-cari-lowongan', 'Halaman cari lowongan beserta skor kecocokan'],
      ['pencari-lamaran', 'Daftar lamaran yang dikirim'],
      ['pencari-lamaran-detail', 'Detail lamaran beserta tahap seleksi'],
    ],
    aksi: [
      ['Ringkasan', 'Membuka halaman', 'GET /pencari/dashboard', 'SELECT', 'pencari_kerja, klaim_keahlian, v_status_keahlian, bukti, v_verifikasi_terbaru, lamaran'],
      ['Profil', 'Simpan perubahan', 'PUT /pencari/profil', 'UPDATE', 'pengguna, pencari_kerja'],
      ['Keahlian', 'Tambah keahlian', 'POST /pencari/keahlian', 'INSERT', 'klaim_keahlian'],
      ['Keahlian', 'Ubah level', 'PUT /pencari/keahlian/{id}', 'UPDATE', 'klaim_keahlian'],
      ['Keahlian', 'Hapus', 'DELETE /pencari/keahlian/{id}', 'DELETE', 'klaim_keahlian, dan bukti_keahlian terhapus mengikuti ON DELETE CASCADE'],
      ['Sertifikat', 'Unggah sertifikat', 'POST /pencari/sertifikat', 'INSERT', 'bukti, berkas PDF disimpan ke File Storage'],
      ['Sertifikat', 'Ganti berkas', 'PUT /pencari/sertifikat/{id}', 'UPDATE', 'bukti, kolom diunggah_pada diperbarui sehingga centang biru tercabut'],
      ['Sertifikat', 'Hapus', 'DELETE /pencari/sertifikat/{id}', 'DELETE', 'bukti, diikuti verifikasi dan bukti_keahlian melalui ON DELETE CASCADE'],
      ['Cari lowongan', 'Kotak pencarian', 'GET /pencari/lowongan?q=', 'SELECT dengan MATCH AGAINST', 'lowongan, perusahaan'],
      ['Cari lowongan', 'Lamar', 'POST /pencari/lowongan/{id}/lamar', 'INSERT', 'lamaran, dengan skor_kecocokan dihitung dari syarat_keahlian dan v_status_keahlian'],
      ['Lamaran', 'Lihat detail', 'GET /pencari/lamaran/{id}', 'SELECT', 'lamaran, lowongan, perusahaan, tahap_seleksi'],
      ['Lamaran', 'Tarik lamaran', 'PATCH /pencari/lamaran/{id}/tarik', 'UPDATE', 'lamaran, status diubah menjadi ditarik dan barisnya tidak dihapus'],
    ],
  },
  {
    peran: 'Perusahaan',
    pengantar:
      'Perusahaan mengelola lowongan miliknya sendiri beserta proses seleksinya. Lowongan hanya dapat dipublikasikan apabila sudah memiliki minimal satu syarat keahlian, dan CHECK constraint memastikan kolom tanggal publikasi ikut terisi saat statusnya berubah.',
    gambar: [
      ['perusahaan-dashboard', 'Ringkasan perusahaan'],
      ['perusahaan-lowongan', 'Daftar lowongan milik perusahaan'],
      ['perusahaan-lowongan-form', 'Formulir lowongan beserta pengaturan syarat keahlian'],
      ['perusahaan-pelamar', 'Daftar pelamar terurut berdasarkan skor kecocokan'],
      ['perusahaan-pelamar-detail', 'Detail pelamar beserta rincian pemenuhan syarat'],
    ],
    aksi: [
      ['Ringkasan', 'Membuka halaman', 'GET /perusahaan/dashboard', 'SELECT', 'perusahaan, lowongan, lamaran'],
      ['Profil', 'Simpan perubahan', 'PUT /perusahaan/profil', 'UPDATE', 'pengguna, perusahaan'],
      ['Lowongan', 'Buat lowongan', 'POST /perusahaan/lowongan', 'INSERT', 'lowongan'],
      ['Lowongan', 'Simpan dan publikasikan', 'PUT /perusahaan/lowongan/{id}', 'UPDATE', 'lowongan, kolom dipublikasikan_pada diisi'],
      ['Lowongan', 'Hapus', 'DELETE /perusahaan/lowongan/{id}', 'DELETE', 'lowongan, diikuti syarat_keahlian, lamaran, dan tahap_seleksi melalui ON DELETE CASCADE'],
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
      'Verifikator hanya melihat sertifikat yang pemiliknya mengklaim keahlian di dalam bidang kewenangannya. Menyimpan keputusan tidak pernah menimpa baris lama, melainkan menambah baris baru pada tabel verifikasi, sehingga riwayat pemeriksaan selalu dapat ditelusuri.',
    gambar: [
      ['verifikator-dashboard', 'Antrean sertifikat yang menunggu pemeriksaan'],
      ['verifikator-periksa', 'Halaman pemeriksaan sertifikat beserta pratinjau PDF'],
      ['verifikator-riwayat', 'Riwayat keputusan verifikator'],
      ['verifikator-kewenangan', 'Daftar kewenangan keahlian verifikator'],
    ],
    aksi: [
      ['Antrean', 'Membuka halaman', 'GET /verifikator/dashboard', 'SELECT', 'bukti, v_verifikasi_terbaru, kewenangan_verifikator, klaim_keahlian'],
      ['Antrean', 'Periksa', 'GET /verifikator/periksa/{bukti}', 'SELECT', 'bukti, jenis_bukti, klaim_keahlian, v_status_keahlian, verifikasi'],
      ['Pemeriksaan', 'Setujui', 'POST /verifikator/periksa/{bukti}', 'INSERT dua tabel dalam satu transaksi', 'verifikasi, lalu bukti_keahlian untuk tiap keahlian yang dicentang'],
      ['Pemeriksaan', 'Tolak', 'POST /verifikator/periksa/{bukti}', 'INSERT', 'verifikasi, kolom catatan wajib diisi'],
      ['Pemeriksaan', 'Tunda', 'POST /verifikator/periksa/{bukti}', 'INSERT', 'verifikasi dengan keputusan menunggu'],
      ['Pemeriksaan', 'Buka PDF', 'GET /berkas/bukti/{bukti}', 'SELECT lalu membaca berkas', 'bukti, berkas dibaca dari File Storage setelah hak akses diperiksa'],
      ['Riwayat', 'Membuka halaman', 'GET /verifikator/riwayat', 'SELECT', 'verifikasi, bukti, pengguna'],
      ['Kewenangan', 'Membuka halaman', 'GET /verifikator/kewenangan', 'SELECT', 'kewenangan_verifikator, keahlian'],
    ],
  },
  {
    peran: 'Administrator',
    pengantar:
      'Administrator memelihara data acuan dan status akun. Penghapusan data acuan yang masih dirujuk ditolak basis data melalui ON DELETE RESTRICT, dan aplikasi menampilkan pesan yang menyarankan menonaktifkan data tersebut alih-alih menghapusnya.',
    gambar: [
      ['admin-dashboard', 'Ringkasan administrator'],
      ['admin-pengguna', 'Halaman kelola akun pengguna'],
      ['admin-verifikator-form', 'Formulir verifikator beserta pemilihan kewenangan keahlian'],
      ['admin-keahlian', 'Halaman data acuan keahlian'],
    ],
    aksi: [
      ['Ringkasan', 'Membuka halaman', 'GET /admin/dashboard', 'SELECT dengan COUNT', 'seluruh tabel utama'],
      ['Kelola pengguna', 'Tambah akun', 'POST /admin/pengguna', 'INSERT', 'pengguna, beserta tabel profil sesuai peran yang dipilih'],
      ['Kelola pengguna', 'Ubah akun dan status', 'PUT /admin/pengguna/{id}', 'UPDATE', 'pengguna'],
      ['Kelola pengguna', 'Hapus akun', 'DELETE /admin/pengguna/{id}', 'DELETE', 'pengguna, ditolak bila masih terhubung profil perusahaan atau riwayat verifikasi'],
      ['Kelola verifikator', 'Simpan kewenangan', 'PUT /admin/verifikator/{id}', 'UPDATE dan sinkronisasi', 'verifikator, kewenangan_verifikator'],
      ['Kelola perusahaan', 'Ubah status', 'PUT /admin/perusahaan/{id}', 'UPDATE', 'perusahaan'],
      ['Data keahlian', 'Tambah, ubah, hapus', 'POST, PUT, DELETE /admin/keahlian', 'INSERT, UPDATE, DELETE', 'keahlian, penghapusan ditolak bila masih dipakai klaim atau syarat'],
      ['Data jenis bukti', 'Tambah, ubah, hapus', 'POST, PUT, DELETE /admin/jenis-bukti', 'INSERT, UPDATE, DELETE', 'jenis_bukti, penghapusan ditolak bila masih dipakai sertifikat'],
    ],
  },
];

const PENGUJIAN = {
  kelayakan: {
    pengantar:
      'Kelayakan basis data diuji dengan skrip laporan/../database/uji_kelayakan.php yang menjalankan 62 pemeriksaan pada tujuh kelompok. Seluruh pemeriksaan yang menulis dibungkus transaksi dan dibatalkan kembali, sehingga isi basis data tidak berubah. Hasil akhir adalah 62 pemeriksaan lulus dan tidak ada yang gagal.',
    baris: [
      ['1. Struktur skema', '7', 'Kelengkapan 15 tabel dan 2 view, mesin penyimpanan InnoDB, karakter utf8mb4, keberadaan primary key pada setiap tabel, serta indeks penopang bagi setiap foreign key.'],
      ['2. Integritas referensial', '12', 'Pencarian baris yatim pada setiap relasi induk dan anak.'],
      ['3. Penegakan constraint', '17', 'Percobaan penulisan data yang seharusnya ditolak: pelanggaran UNIQUE, foreign key, ON DELETE RESTRICT, delapan CHECK constraint, serta keempat trigger.'],
      ['4. Konsistensi data', '7', 'Pemeriksaan bahwa data yang sudah tersimpan tidak melanggar aturan antarkolom maupun aturan kewenangan verifikator.'],
      ['5. Perilaku view centang biru', '7', 'Penggantian berkas mencabut centang biru, keahlian nonaktif tidak terhitung, verifikasi kedaluwarsa tidak memberi centang, dan klaim nonaktif tidak muncul.'],
      ['6. Pemakaian indeks', '7', 'Pemeriksaan rencana eksekusi untuk memastikan setiap query halaman utama benar-benar memakai indeks yang dirancang untuknya.'],
      ['7. Bentuk normal', '5', 'Pemeriksaan tidak adanya kolom bernilai jamak, ketergantungan parsial pada tabel berkunci gabungan, kolom status turunan, dan kolom agregat.'],
    ],
  },
  fungsional: {
    pengantar:
      'Pengujian fungsional dijalankan dengan php artisan test menggunakan basis data terpisah bernama sibuker_pt_test. Basis data tersebut dibangun ulang dari migration pada setiap pengujian, sehingga view, CHECK constraint, dan trigger ikut diuji. Seluruh 15 pengujian dengan 107 asersi dinyatakan lulus.',
    baris: [
      ['Halaman publik terbuka', 'Seluruh halaman publik dapat diakses, sedangkan lowongan berstatus draft mengembalikan 404.'],
      ['Halaman panel per peran', 'Setiap peran dapat membuka seluruh halaman miliknya.'],
      ['Hak akses dijaga', 'Peran yang tidak berhak ditolak dengan kode 403.'],
      ['Status centang biru dibaca dari view', 'Nilai centang biru yang ditampilkan berasal dari v_status_keahlian.'],
      ['Masuk dan pilih peran', 'Akun dengan lebih dari satu peran diarahkan ke halaman pemilihan peran.'],
      ['Pendaftaran perusahaan', 'Pendaftaran membuat satu baris pengguna dan satu baris perusahaan.'],
      ['Melamar menyimpan skor dan menolak duplikat', 'Skor kecocokan tersimpan, dan lamaran kedua pada lowongan yang sama ditolak UNIQUE constraint.'],
      ['Verifikasi disetujui memberi centang biru', 'Persetujuan verifikator langsung mengubah status pada view.'],
      ['Publikasi butuh syarat', 'Lowongan tanpa syarat keahlian tidak dapat dipublikasikan.'],
      ['Hapus data acuan yang dipakai ditolak', 'ON DELETE RESTRICT bekerja dan aplikasi menampilkan pesan yang sesuai.'],
      ['Mengganti berkas mencabut centang biru', 'Sertifikat yang berkasnya diganti kembali masuk antrean dan kehilangan centang biru.'],
      ['Penautan lintas pemilik ditolak', 'Trigger menolak sertifikat satu pengguna membuktikan klaim pengguna lain.'],
      ['Verifikator tidak memeriksa miliknya sendiri', 'Ditolak controller sekaligus ditolak trigger pada tingkat basis data.'],
      ['Keahlian nonaktif tidak terverifikasi', 'Klaim pada keahlian yang dinonaktifkan tidak lagi bercentang biru.'],
      ['CHECK constraint menjaga konsistensi', 'Kombinasi kolom yang tidak konsisten ditolak basis data.'],
    ],
  },
};

const PUSTAKA = [
  'Alfina, O., & Syafrinal, I. (2022). Perancangan Sistem Verifikasi Ijazah Digital Berbasis Web. Jurnal Sistem Informasi dan Teknologi Informasi.',
  'Codd, E. F. (1970). A Relational Model of Data for Large Shared Data Banks. Communications of the ACM, 13(6), 377–387.',
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
