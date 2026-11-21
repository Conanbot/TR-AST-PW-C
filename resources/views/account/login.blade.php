@extends('layouts.auth')

@section('title', 'Masuk — Geprek GLO')

@section('auth-form')
    <div class="auth-heading">
        <h2>Masuk ke Akun Anda</h2>
        <p>Silakan masuk dengan email atau nomor WhatsApp Anda untuk melanjutkan.</p>
    </div>

    @if(session('info'))
        <div class="alert alert-info" role="status">{{ session('info') }}</div>
    @elseif(session()->has('url.intended') && str_contains(session('url.intended'), '/menu'))
        <div class="alert alert-info" role="status">Silakan masuk ke akun Anda terlebih dahulu untuk mengakses Katalog Menu dan memesan.</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success" role="alert" style="background: rgba(39, 174, 96, 0.15); border: 1px solid var(--ok); color: #2ecc71; padding: 12px 14px; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('account.login.submit') }}" method="POST" class="auth-form">
        @csrf
        @if(request('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
        @endif
        <div class="form-group">
            <label for="email" class="form-label">Email atau No. WhatsApp</label>
            <input type="text" id="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="username" placeholder="nama@email.com atau 0812xxx" required autofocus>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata sandi</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
        </div>
        <label class="form-check auth-remember"><input type="checkbox" name="remember"><span>Ingat saya di perangkat ini</span></label>
        <button type="submit" class="btn btn-primary auth-submit">Masuk <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <p class="auth-signup">Belum punya akun? <a href="{{ route('account.register') }}">Daftar akun baru di sini</a></p>
@endsection
