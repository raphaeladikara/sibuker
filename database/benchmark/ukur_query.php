<?php

/**
 * Mengukur waktu eksekusi query halaman utama pada database benchmark,
 * lalu mencatat indeks yang dipilih optimizer. Dijalankan dua kali:
 * sekali pada skema baru, sekali pada skema lama (setelah rollback).
 *
 *   php bench_ukur.php baru
 *   php bench_ukur.php lama
 */
$label = $argv[1] ?? 'baru';

$p = new PDO('mysql:host=127.0.0.1;dbname=sibuker_pt_bench', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$adaFullText = (bool) $p->query(
    "SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA='sibuker_pt_bench' AND INDEX_NAME='ft_lowongan_pencarian'"
)->fetchColumn();

$query = [
    'Q1 daftar lowongan publik' => "
        SELECT id, posisi, dipublikasikan_pada FROM lowongan
        WHERE status = 'dipublikasikan'
        ORDER BY dipublikasikan_pada DESC
        LIMIT 9",

    'Q2 cari kata kunci' => $adaFullText
        ? "SELECT id, posisi FROM lowongan
           WHERE status = 'dipublikasikan'
             AND MATCH (posisi, deskripsi) AGAINST ('+data* +analyst*' IN BOOLEAN MODE)
           LIMIT 20"
        : "SELECT id, posisi FROM lowongan
           WHERE status = 'dipublikasikan'
             AND (posisi LIKE '%data analyst%' OR deskripsi LIKE '%data analyst%')
           LIMIT 20",

    'Q3 centang biru (view)' => "
        SELECT COUNT(*) FROM v_status_keahlian WHERE terverifikasi = 1",

    'Q4 peringkat pelamar' => "
        SELECT id, skor_kecocokan FROM lamaran
        WHERE lowongan_id = 1234
        ORDER BY skor_kecocokan DESC, dilamar_pada
        LIMIT 50",

    'Q5 lamaran saya' => "
        SELECT id, status FROM lamaran
        WHERE pencari_kerja_id = 4321
        ORDER BY dilamar_pada DESC
        LIMIT 20",

    'Q6 sertifikat saya' => "
        SELECT id, judul FROM bukti
        WHERE pengguna_id = 4321
        ORDER BY diunggah_pada DESC
        LIMIT 20",
];

$hasil = [];

foreach ($query as $nama => $sql) {
    // Pemanasan, supaya buffer pool terisi dan pengukuran tidak mengukur I/O dingin.
    $p->query($sql)->fetchAll();

    $ulang = 5;
    $mulai = microtime(true);
    for ($i = 0; $i < $ulang; $i++) {
        $p->query($sql)->fetchAll();
    }
    $ms = (microtime(true) - $mulai) / $ulang * 1000;

    $rencana = $p->query('EXPLAIN ' . $sql)->fetchAll(PDO::FETCH_ASSOC);
    $baris = array_sum(array_column($rencana, 'rows'));
    $indeks = array_values(array_filter(array_column($rencana, 'key')));
    $tipe = array_column($rencana, 'type');

    $hasil[$nama] = [
        'ms' => $ms,
        'baris' => $baris,
        'indeks' => $indeks ? implode(', ', array_unique($indeks)) : '(tidak ada)',
        'tipe' => implode(', ', array_unique($tipe)),
    ];
}

file_put_contents(__DIR__ . "/bench_$label.json", json_encode($hasil, JSON_PRETTY_PRINT));

printf("%-28s %10s %12s %-38s %s\n", "QUERY [$label]", 'waktu', 'baris dibaca', 'indeks dipakai', 'tipe akses');
printf("%s\n", str_repeat('-', 110));
foreach ($hasil as $nama => $h) {
    printf("%-28s %8.2f ms %12s %-38s %s\n", $nama, $h['ms'], number_format($h['baris']), $h['indeks'], $h['tipe']);
}
