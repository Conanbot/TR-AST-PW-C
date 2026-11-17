@extends('layouts.auth')

@section('title', 'Masuk Pelanggan — Geprek GLO')

@section('auth-form')
    <div class="auth-heading">
        <span class="role-pill role-pill-customer">Akun pelanggan</span>
        <h2>Pesan lagi, lebih mudah</h2>
        <p>Masuk untuk melihat pesanan yang terhubung dengan akun Anda.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('account.login.submit') }}" method="POST" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" placeholder="nama@email.com" required autofocus>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata sandi</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
        </div>
        <label class="form-check auth-remember"><input type="checkbox" name="remember"><span>Ingat perangkat ini</span></label>
        <button type="submit" class="btn btn-primary auth-submit">Masuk ke akun <span aria-hidden="true">→</span></button>
    </form>

    <p class="auth-signup">Belum punya akun? <a href="{{ route('account.register') }}">Daftar pelanggan</a></p>
    <div class="auth-switch">
        <a href="{{ route('admin.login') }}">Masuk sebagai admin</a>
        <span>·</span>
        <a href="{{ route('cashier.login') }}">Masuk sebagai kasir</a>
    </div>
@endsection
