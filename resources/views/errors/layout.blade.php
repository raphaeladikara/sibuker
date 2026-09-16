@extends('layouts.app')

@section('title', $judul)

@section('content')
<div class="panel empty" style="max-width:520px;margin:48px auto;padding:48px 32px">
    <div style="font-size:48px;font-weight:500;color:var(--indigo);line-height:1">{{ $kode }}</div>
    <h1 style="font-size:20px;margin:14px 0 8px;color:var(--ink)">{{ $judul }}</h1>
    <p>{{ $pesan }}</p>
    <a href="{{ auth()->check() ? route(auth()->user()->ruteDashboard()) : route('beranda') }}" class="btn btn-primary btn-sm">Kembali ke halaman utama</a>
</div>
@endsection
