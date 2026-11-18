@extends('layouts.auth')

@section('title', 'Daftar Pelanggan — Geprek GLO')

@section('auth-form')
    <div class="auth-heading">
        <span class="role-pill role-pill-customer">Akun pelanggan</span>
        <h2>Buat akun pelanggan</h2>
        <p>Daftarkan akun Anda terlebih dahulu. Setelah berhasil mendaftar, silakan masuk untuk mulai memilih menu dan memesan.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('account.register.submit') }}" method="POST" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="name" class="form-label">Nama Lengkap <span class="text-primary">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" autocomplete="name" placeholder="Contoh: Budi Santoso" required autofocus>
        </div>
        <div class="form-group">
            <label for="phone" class="form-label">Nomor WhatsApp / HP</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Contoh: 08123456789">
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email <span class="text-primary">*</span></label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi <span class="text-primary">*</span></label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" placeholder="Minimal 8 karakter" minlength="8" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Ulangi Kata Sandi <span class="text-primary">*</span></label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Ulangi kata sandi Anda" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary auth-submit">Daftar Akun Sekarang <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-signup">Sudah pernah mendaftar? <a href="{{ route('account.login') }}">Masuk ke akun di sini</a></p>
@endsection
