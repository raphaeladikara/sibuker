@extends('layouts.app')

@section('title', 'Status ' . $item->nama)

@section('content')
<a href="{{ route('admin.perusahaan.index') }}" class="back"><i class="bi bi-arrow-return-left"></i> Perusahaan</a>
<div class="page-head">
    <div class="row" style="gap:16px;align-items:flex-start">
        @include('partials.logo', ['perusahaan' => $item, 'ukuran' => 'lg'])
        <div>
            <h1>{{ $item->nama }}</h1>
            <p class="sub">{{ $item->deskripsi ?: 'Tanpa deskripsi' }}</p>
        </div>
    </div>
</div>

<form action="{{ route('admin.perusahaan.update', $item) }}" method="POST" class="panel" style="max-width:640px">
    @csrf
    @method('PUT')
    <div class="panel-body">
        <dl class="dl">
            <dt>NIB</dt><dd>{{ $item->nib ?: '-' }}</dd>
            <dt>Penanggung jawab</dt><dd>{{ $item->pengguna->nama }} &middot; {{ $item->pengguna->email }}</dd>
            <dt>Alamat</dt><dd>{{ $item->alamat ?: '-' }}</dd>
            <dt>Lowongan</dt><dd>{{ $item->lowongan_count }}</dd>
        </dl>
        <div class="field" style="margin-top:26px">
            <span class="label" id="label-status">Status perusahaan</span>
            <div class="segmented" role="radiogroup" aria-labelledby="label-status">
                @foreach (['aktif', 'nonaktif'] as $s)
                    <input type="radio" name="status_perusahaan" id="sp-{{ $s }}" value="{{ $s }}" @checked(old('status_perusahaan', $item->status_perusahaan) === $s)>
                    <label for="sp-{{ $s }}">{{ ucfirst($s) }}</label>
                @endforeach
            </div>
            <span class="hint">Perusahaan nonaktif tidak bisa mempublikasikan lowongan, dan lowongan yang sudah tayang disembunyikan dari publik.</span>
        </div>
    </div>
    <div class="panel-foot form-actions" style="margin:0">
        <a href="{{ route('admin.perusahaan.index') }}" class="btn btn-ghost">Batal</a>
        <button class="btn btn-primary">Simpan</button>
    </div>
</form>
@endsection
