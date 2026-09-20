@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="auth">
    @include('partials.auth-intro', ['illustration' => 'images/login-profile.png'])
    <div class="auth-content stack">
    <div class="panel">
        <div class="panel-body">
            <span class="eyebrow">SELAMAT DATANG KEMBALI</span>
            <h1>Siap melangkah lagi?</h1>
            <p class="muted" style="margin-bottom:24px">Satu akun bisa dipakai untuk lebih dari satu peran.</p>

            <form action="{{ route('masuk.proses') }}" method="POST" id="form-masuk" novalidate>
                @csrf
                <div class="field">
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" @class(['input', 'is-invalid' => $errors->has('email')]) placeholder="nama@email.com" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label class="label" for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" @class(['input', 'is-invalid' => $errors->has('password')]) autocomplete="current-password" required>
                </div>
                <button class="btn btn-primary btn-block" style="margin-top:24px;height:44px">Masuk</button>
            </form>
            <p class="muted" style="margin-top:20px;text-align:center">Belum punya akun? <a href="{{ route('daftar') }}">Daftar</a></p>
        </div>
    </div>

    @if ($akunDemo)
        <div class="panel">
            <div class="panel-head"><h2>Akun demo</h2><span class="pill">sandi: password</span></div>
            <div class="panel-body" style="padding-block:8px">
                <ul class="demo-list">
                    @foreach ($akunDemo as [$peran, $email])
                        <li>
                            <span class="muted">{{ $peran }}</span>
                            <button type="button" data-demo="{{ $email }}">{{ $email }}</button>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="panel-foot small muted">Klik email untuk mengisi form. Daftar ini hanya muncul saat APP_ENV=local.</div>
        </div>
    @endif
    </div>
</div>
@endsection
