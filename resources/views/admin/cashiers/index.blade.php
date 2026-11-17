@extends('layouts.admin')

@section('title', 'Akun Kasir — Geprek GLO')

@section('content')
<div class="page-heading admin-page-heading">
    <div><span class="eyebrow">Akses staf</span><h1>Akun kasir</h1><p>Buat akun staf yang dapat menerima pesanan tanpa akses pengaturan admin.</p></div>
</div>

<div class="staff-grid">
    <section class="surface-panel staff-form-panel">
        <div class="panel-heading"><div><span class="eyebrow">Akun baru</span><h2>Tambah kasir</h2></div></div>
        @if($errors->any())<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
        <form action="{{ route('admin.cashiers.store') }}" method="POST" class="staff-form">
            @csrf
            <div class="form-group"><label class="form-label" for="cashier-name">Nama kasir</label><input class="form-control" id="cashier-name" name="name" value="{{ old('name') }}" required></div>
            <div class="form-group"><label class="form-label" for="cashier-username">Username</label><input class="form-control" id="cashier-username" name="username" value="{{ old('username') }}" autocomplete="off" required></div>
            <div class="form-group"><label class="form-label" for="cashier-password">Kata sandi sementara</label><input class="form-control" id="cashier-password" type="password" name="password" minlength="8" autocomplete="new-password" required></div>
            <div class="form-group"><label class="form-label" for="cashier-password-confirmation">Ulangi kata sandi</label><input class="form-control" id="cashier-password-confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></div>
            <button class="btn btn-primary" type="submit">Buat akun kasir</button>
        </form>
    </section>

    <section class="surface-panel staff-list-panel">
        <div class="panel-heading"><div><span class="eyebrow">Tim aktif</span><h2>Daftar kasir</h2></div><span class="count-badge">{{ $cashiers->count() }}</span></div>
        @forelse($cashiers as $cashier)
            <div class="staff-row">
                <span class="avatar-dot">{{ mb_substr($cashier->name, 0, 1) }}</span>
                <div><strong>{{ $cashier->name }}</strong><span>{{ '@' . $cashier->username }}</span></div>
                <span class="role-pill role-pill-cashier">Kasir</span>
            </div>
        @empty
            <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">＋</span><h3>Belum ada akun kasir</h3><p>Buat akun pertama untuk membuka akses kasir.</p></div>
        @endforelse
    </section>
</div>
@endsection
