@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="auth">
    @include('partials.auth-intro', ['illustration' => 'images/login-profile.png'])
    <div class="panel">
        <div class="panel-body">
            <span class="eyebrow">MULAI DARI SINI</span>
            <h1>Buat akun</h1>
            <p class="muted" style="margin-bottom:24px">Akun verifikator dibuat oleh administrator, tidak lewat halaman ini.</p>

            <form action="{{ route('daftar.proses') }}" method="POST" id="form-daftar" novalidate>
                @csrf
                <div class="field">
                    <span class="label" id="label-peran">Daftar sebagai</span>
                    <div class="segmented" role="radiogroup" aria-labelledby="label-peran">
                        <input type="radio" name="peran" id="peran-pencari" value="pencari" @checked(old('peran', 'pencari') === 'pencari')>
                        <label for="peran-pencari"><i class="bi bi-person" aria-hidden="true"></i>Pencari kerja</label>
                        <input type="radio" name="peran" id="peran-perusahaan" value="perusahaan" @checked(old('peran') === 'perusahaan')>
                        <label for="peran-perusahaan"><i class="bi bi-building" aria-hidden="true"></i>Perusahaan</label>
                    </div>
                </div>

                <div class="fields" data-perusahaan style="margin-top:18px">
                    <div class="field">
                        <label class="label" for="nama_perusahaan">Nama perusahaan</label>
                        <input id="nama_perusahaan" name="nama_perusahaan" value="{{ old('nama_perusahaan') }}" @class(['input', 'is-invalid' => $errors->has('nama_perusahaan')]) placeholder="PT Contoh Sejahtera">
                    </div>
                    <div class="field">
                        <label class="label" for="nib">NIB <span class="opsi">(opsional)</span></label>
                        <input id="nib" name="nib" value="{{ old('nib') }}" @class(['input', 'is-invalid' => $errors->has('nib')]) inputmode="numeric">
                    </div>
                </div>

                <div class="fields" style="margin-top:18px">
                    <div class="field">
                        <label class="label" for="nama">Nama lengkap</label>
                        <input id="nama" name="nama" value="{{ old('nama') }}" @class(['input', 'is-invalid' => $errors->has('nama')]) autocomplete="name" required>
                    </div>
                    <div class="field">
                        <label class="label" for="nomor_telepon">Nomor telepon <span class="opsi">(opsional)</span></label>
                        <input id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}" class="input" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx">
                    </div>
                </div>
                <div class="field">
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" @class(['input', 'is-invalid' => $errors->has('email')]) autocomplete="email" required>
                </div>
                <div class="fields" style="margin-top:18px">
                    <div class="field">
                        <label class="label" for="password">Kata sandi</label>
                        <input id="password" type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) autocomplete="new-password" required>
                        <span class="hint">Minimal 8 karakter.</span>
                    </div>
                    <div class="field">
                        <label class="label" for="password_confirmation">Ulangi kata sandi</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password" required>
                    </div>
                </div>
                <button class="btn btn-primary btn-block" style="margin-top:26px;height:44px">Buat akun</button>
            </form>
            <p class="muted" style="margin-top:20px;text-align:center">Sudah punya akun? <a href="{{ route('masuk') }}">Masuk</a></p>
        </div>
    </div>
</div>
@endsection
