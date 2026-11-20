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
            <div class="staff-row" style="align-items:flex-start; gap:0.75rem;">
                <img src="{{ asset('assets/img/avatar-placeholder.svg') }}" alt="" class="staff-avatar" width="50" height="50">
                <div style="flex:1; min-width:0;">
                    <strong>{{ $cashier->name }}</strong>
                    <span>{{ '@' . $cashier->username }}</span>
                </div>
                <span class="role-pill role-pill-cashier">Kasir</span>

                {{-- Reset Password Modal Toggle --}}
                <details style="position:relative;">
                    <summary class="btn btn-sm" style="cursor:pointer; list-style:none; padding:0.25rem 0.6rem; font-size:0.8rem;">Ubah Password</summary>
                    <div style="position:absolute; right:0; top:2rem; z-index:10; background:var(--surface-2,#1e1e1e); border:1px solid var(--border,#333); padding:1rem; border-radius:0.5rem; min-width:260px;">
                        <form action="{{ route('admin.cashiers.password', $cashier) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="form-group" style="margin-bottom:0.5rem;">
                                <label class="form-label" style="font-size:0.8rem;">Password Baru</label>
                                <input class="form-control" type="password" name="password" minlength="8" required autocomplete="new-password">
                            </div>
                            <div class="form-group" style="margin-bottom:0.75rem;">
                                <label class="form-label" style="font-size:0.8rem;">Konfirmasi</label>
                                <input class="form-control" type="password" name="password_confirmation" minlength="8" required autocomplete="new-password">
                            </div>
                            <button class="btn btn-primary btn-sm" type="submit" style="font-size:0.8rem; width:100%;">Simpan Password</button>
                        </form>
                    </div>
                </details>

                {{-- Delete --}}
                <form action="{{ route('admin.cashiers.destroy', $cashier) }}" method="POST" onsubmit="return confirm('Hapus akun kasir {{ addslashes($cashier->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" type="submit" style="font-size:0.8rem;">Hapus</button>
                </form>
            </div>
        @empty
            <div class="empty-state"><span class="empty-state-mark" aria-hidden="true">＋</span><h3>Belum ada akun kasir</h3><p>Buat akun pertama untuk membuka akses kasir.</p></div>
        @endforelse
    </section>
</div>
@endsection
