@extends('layouts.admin')

@section('title', 'Ubah Password Admin — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <h2 style="font-size: 1.4rem; margin-bottom: 4px;"><i class="fa-solid fa-lock-keyhole" aria-hidden="true"></i> Ubah Password Admin</h2>
    <p class="text-sm text-muted">
        Perbarui kata sandi akun administrator untuk menjaga keamanan akses ke panel pengelolaan Geprek GLO.
    </p>
</div>

<div class="card" style="padding: var(--space-xl); max-width: 600px;">
    <form action="{{ route('admin.password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="current_password" class="form-label">Password Saat Ini <span class="text-primary">*</span></label>
            <div style="position: relative;">
                <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" placeholder="Masukkan password saat ini" required style="padding-right: 44px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="togglePasswordVisibility('current_password', this)" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); padding: 5px 8px; font-size: 0.75rem;" title="Tampilkan/Sembunyikan password">
                    Lihat
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password Baru <span class="text-primary">*</span></label>
            <div style="position: relative;">
                <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" placeholder="Minimal 8 karakter" required style="padding-right: 44px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="togglePasswordVisibility('password', this)" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); padding: 5px 8px; font-size: 0.75rem;" title="Tampilkan/Sembunyikan password">
                    Lihat
                </button>
            </div>
            <div style="margin-top: 8px; padding: 10px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.8rem; color: var(--text-muted);">
                <div style="font-weight: 700; margin-bottom: 4px; color: var(--text);">Persyaratan Keamanan Password:</div>
                <ul style="margin-left: 18px; margin-bottom: 0;">
                    <li>Panjang minimal 8 karakter.</li>
                    <li>Wajib mengulangi password baru di kolom konfirmasi dengan persis sama.</li>
                    <li>Gunakan kombinasi huruf dan angka agar akun tidak mudah ditebak.</li>
                </ul>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru <span class="text-primary">*</span></label>
            <div style="position: relative;">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Ulangi password baru" required style="padding-right: 44px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="togglePasswordVisibility('password_confirmation', this)" style="position: absolute; right: 6px; top: 50%; transform: translateY(-50%); padding: 5px 8px; font-size: 0.75rem;" title="Tampilkan/Sembunyikan password">
                    Lihat
                </button>
            </div>
        </div>

        <div class="flex gap-md justify-end" style="margin-top: var(--space-xl); border-top: 1px solid var(--border); padding-top: var(--space-md);">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Tutup';
    } else {
        input.type = 'password';
        btn.textContent = 'Lihat';
    }
}
</script>
@endpush
