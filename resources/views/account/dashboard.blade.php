@extends('layouts.account')

@section('title', 'Akun Saya — Geprek GLO')

@section('content')
<section class="account-welcome">
    <div>
        <span class="eyebrow">Pengaturan akun</span>
        <h1>Halo, {{ $user->name }}!</h1>
        <p>Perbarui nama, email, nomor telepon, atau kata sandi akun Anda di sini.</p>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('account.orders') }}" class="btn btn-secondary">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
            Riwayat Pesanan ({{ $totalOrders }})
        </a>
        <a href="{{ route('menu') }}" class="btn btn-primary">
            Pesan menu <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</section>

<section class="surface-panel" style="max-width: 680px; padding: 28px;">
    <div class="panel-heading" style="margin-bottom: 20px;">
        <div>
            <span class="eyebrow">Informasi pribadi</span>
            <h2>Profil &amp; Keamanan Akun</h2>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error" role="alert">
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('account.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="profile-name" class="form-label">Nama Lengkap <span class="text-primary">*</span></label>
            <input type="text" id="profile-name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
            <label for="profile-email" class="form-label">Email <span class="text-primary">*</span></label>
            <input type="email" id="profile-email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="profile-phone" class="form-label">Nomor WhatsApp / HP</label>
            <input type="text" id="profile-phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789">
        </div>

        <div style="margin: 24px 0 18px; padding-top: 20px; border-top: 1px solid var(--border);">
            <span class="eyebrow" style="margin-bottom: 4px;">Ganti kata sandi (opsional)</span>
            <p class="text-xs text-muted" style="margin-bottom: 14px;">Kosongkan jika tidak ingin mengubah kata sandi Anda.</p>

            <div class="form-group">
                <label for="profile-password" class="form-label">Kata Sandi Baru</label>
                <input type="password" id="profile-password" name="password" class="form-control" minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter">
            </div>

            <div class="form-group">
                <label for="profile-password-confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                <input type="password" id="profile-password-confirmation" name="password_confirmation" class="form-control" minlength="6" autocomplete="new-password" placeholder="Ulangi kata sandi baru">
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                Simpan Perubahan
            </button>
        </div>
    </form>
</section>
@endsection
