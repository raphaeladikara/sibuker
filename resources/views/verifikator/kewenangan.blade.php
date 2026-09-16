@extends('layouts.app')

@section('title', 'Kewenangan')

@section('content')
<div class="page-head">
    <div>
        <h1>Kewenangan</h1>
        <p class="sub">Bidang yang boleh kamu verifikasi. Daftar ini diatur administrator. Keahlian di luar daftar tidak bisa kamu centang saat memeriksa.</p>
    </div>
</div>

<div class="panel">
    @if ($verifikator->kewenangan->isEmpty())
        <div class="empty"><i class="bi bi-shield"></i><p>Kamu belum diberi kewenangan apa pun. Hubungi administrator.</p></div>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Keahlian</th><th>Kategori</th><th class="kanan">Pencari yang mencantumkan</th><th>Diberikan</th></tr></thead>
                <tbody>
                @foreach ($verifikator->kewenangan as $k)
                    <tr>
                        <td>
                            <div class="utama">{{ $k->nama }}</div>
                            <div class="sekunder">{{ $k->deskripsi }}</div>
                        </td>
                        <td>{{ $k->kategori ?: '-' }}</td>
                        <td class="kanan">{{ $k->klaim_count }}</td>
                        <td class="muted">{{ \App\Support\Format::tanggal($k->pivot->diberikan_pada) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
