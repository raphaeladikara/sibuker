@include('errors.layout', [
    'kode' => 403,
    'judul' => 'Tidak punya akses',
    'pesan' => $exception->getMessage() ?: 'Akun ini tidak diizinkan membuka halaman tersebut.',
])
