@extends('layouts.auth')

@section('title', 'Masuk Admin — Geprek GLO')

@section('auth-form')
    <div class="auth-heading">
        <span class="role-pill role-pill-admin">Portal admin</span>
        <h2>Selamat datang kembali</h2>
        <p>Masuk untuk mengatur menu, stok, promo, dan akun kasir.</p>
    </div>

    @if(session('info'))
        <div class="alert alert-info" role="status">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="username" class="form-label">Username admin</label>
            <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" autocomplete="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="form-group">
            <div class="form-label-row"><label for="password" class="form-label">Kata sandi</label></div>
            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
        </div>
        <label class="form-check auth-remember"><input type="checkbox" name="remember"><span>Ingat perangkat ini</span></label>
        <button type="submit" class="btn btn-primary auth-submit">Masuk ke panel admin <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="auth-switch">
        <a href="{{ route('cashier.login') }}">Masuk sebagai kasir</a>
        <span>·</span>
        <a href="{{ route('account.login') }}">Masuk sebagai pelanggan</a>
    </div>
@endsection
