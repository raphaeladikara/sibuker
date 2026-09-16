@php
    $warna = [
        'aktif' => 't-green', 'nonaktif' => '', 'ditangguhkan' => 't-rose',
        'draft' => '', 'dipublikasikan' => 't-green', 'ditutup' => 't-violet',
        'menunggu' => 't-amber', 'disetujui' => 't-green', 'ditolak' => 't-rose',
        'dikirim' => 't-blue', 'ditinjau' => 't-indigo', 'diproses' => 't-amber', 'diterima' => 't-green', 'ditarik' => '',
        'berlangsung' => 't-indigo', 'lulus' => 't-green', 'tidak_lulus' => 't-rose',
    ][$status ?? ''] ?? 't-amber';
    $teks = $status === null ? ($kosong ?? 'Belum diperiksa') : \App\Support\Format::label($status);
@endphp
<span class="pill {{ $warna }}">{{ $teks }}</span>
