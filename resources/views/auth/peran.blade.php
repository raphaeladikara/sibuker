@extends('layouts.app')

@section('title', 'Pilih peran')

@section('content')
@php
    $keterangan = [
        'pencari' => ['bi-person', 'Cari lowongan, kelola keahlian dan sertifikat'],
        'perusahaan' => ['bi-building', 'Kelola lowongan dan pelamar'],
        'verifikator' => ['bi-shield-check', 'Periksa sertifikat di antrean'],
        'admin' => ['bi-gear', 'Kelola akun dan data master'],
    ];
@endphp
<div class="auth">
    @include('partials.auth-intro')
    <div class="panel">
        <div class="panel-body">
            <span class="eyebrow">SATU AKUN, BANYAK PELUANG</span>
            <h1>Masuk sebagai</h1>
            <p class="muted" style="margin-bottom:22px">Akunmu punya {{ count($peran) }} peran. Kamu bisa pindah kapan saja lewat menu di kanan atas.</p>
            <div class="peran-grid">
                @foreach ($peran as $p)
                    <a href="{{ route($p . '.dashboard') }}">
                        <span class="logo rona-{{ $loop->iteration }}"><i class="bi {{ $keterangan[$p][0] }}" aria-hidden="true"></i></span>
                        <span>
                            <span style="display:block;font-weight:600">{{ \App\Models\Pengguna::PERAN[$p] }}</span>
                            <span class="muted small">{{ $keterangan[$p][1] }}</span>
                        </span>
                        <i class="bi bi-arrow-right" style="margin-left:auto" aria-hidden="true"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
