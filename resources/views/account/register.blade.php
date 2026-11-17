@extends('layouts.auth')

@section('title', 'Daftar Pelanggan — Geprek GLO')

@section('auth-form')
    <div class="auth-heading">
        <span class="role-pill role-pill-customer">Akun pelanggan</span>
        <h2>Buat akun pelanggan</h2>
        <p>Gunakan email aktif agar riwayat pesanan bisa tersimpan di akun Anda.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('account.register.submit') }}" method="POST" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="name" class="form-label">Nama lengkap</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" autocomplete="name" required autofocus>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata sandi</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" minlength="8" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Ulangi kata sandi</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary auth-submit">Buat akun <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-signup">Sudah punya akun? <a href="{{ route('account.login') }}">Masuk ke akun</a></p>
@endsection
