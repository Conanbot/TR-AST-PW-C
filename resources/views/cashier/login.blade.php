@extends('layouts.auth')

@section('title', 'Masuk Kasir — Geprek GLO')

@section('auth-form')
    <div class="auth-heading">
        <span class="role-pill role-pill-cashier">Portal kasir</span>
        <h2>Siap melayani?</h2>
        <p>Masuk untuk memilih meja, mencatat pesanan, dan memperbarui status hidangan.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('cashier.login.submit') }}" method="POST" class="auth-form">
        @csrf
        <div class="form-group">
            <label for="username" class="form-label">Username kasir</label>
            <input type="text" id="username" name="username" class="form-control" value="{{ old('username') }}" autocomplete="username" placeholder="Masukkan username" required autofocus>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Kata sandi</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
        </div>
        <label class="form-check auth-remember"><input type="checkbox" name="remember"><span>Ingat perangkat ini</span></label>
        <button type="submit" class="btn btn-primary auth-submit">Masuk ke kasir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>

    <div class="auth-switch">
        <a href="{{ route('admin.login') }}">Masuk sebagai admin</a>
        <span>·</span>
        <a href="{{ route('account.login') }}">Masuk sebagai pelanggan</a>
    </div>
@endsection
