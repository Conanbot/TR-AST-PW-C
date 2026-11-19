@extends(auth()->user()?->role === 'cashier' ? 'layouts.cashier' : 'layouts.admin')

@section('title', 'Pengaturan Pembayaran — Geprek GLO')

@section('content')
@php $isCashier = auth()->user()?->role === 'cashier'; @endphp

<div class="page-heading">
    <div>
        <span class="eyebrow">Pembayaran</span>
        <h1>Pengaturan Metode Pembayaran</h1>
        <p class="text-muted">Aktifkan metode dan konfigurasi QRIS.</p>
    </div>
    <a href="{{ $isCashier ? route('cashier.payments.index') : '#' }}" class="btn btn-secondary">← Kembali ke Pembayaran</a>
</div>

<form method="POST"
      action="{{ $isCashier ? route('cashier.payments.settings.update') : '#' }}"
      enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="card" style="padding:var(--space-lg);margin-bottom:var(--space-lg);">
        <h2 style="margin-bottom:16px;">QRIS</h2>
        <div class="stack-md">
            <label class="form-check" style="font-weight:600;">
                <input type="checkbox" name="qris_enabled" value="1" @checked($settings->qris_enabled)>
                Aktifkan pembayaran QRIS
            </label>
            <div class="form-group">
                <label class="form-label">Nama Merchant QRIS</label>
                <input class="form-input" name="qris_merchant_name"
                       value="{{ old('qris_merchant_name', $settings->qris_merchant_name) }}"
                       placeholder="Nama merchant sesuai QRIS resmi">
            </div>
            <div class="form-group">
                <label class="form-label">Instruksi Pembayaran</label>
                <textarea class="form-input" name="qris_instructions" rows="3"
                          placeholder="Instruksi yang ditampilkan ke pelanggan saat memilih QRIS">{{ old('qris_instructions', $settings->qris_instructions) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Gambar QRIS</label>
                @if($settings->qris_image)
                    <img src="{{ asset('storage/'.$settings->qris_image) }}" alt="QRIS"
                         style="max-width:220px;border-radius:12px;display:block;margin-bottom:12px;">
                    <span class="text-xs text-muted">Upload baru untuk mengganti gambar yang ada.</span>
                @endif
                <input class="form-input" type="file" name="qris_image"
                       accept="image/jpeg,image/png,image/webp" style="margin-top:8px;">
            </div>
        </div>
    </div>

    <div class="card" style="padding:var(--space-lg);margin-bottom:var(--space-lg);">
        <h2 style="margin-bottom:16px;">Metode Pembayaran</h2>
        <div class="stack-sm">
            @foreach($methods as $method)
                <div class="flex justify-between items-center gap-sm"
                     style="padding:12px 0;border-bottom:1px solid var(--border);">
                    <label class="form-check" style="font-weight:600;">
                        <input type="checkbox"
                               name="methods[{{ $method->code }}][is_active]"
                               value="1" @checked($method->is_active)>
                        {{ $method->name }}
                        <span class="text-xs text-muted" style="margin-left:4px;">{{ $method->code }}</span>
                    </label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <label class="form-label text-xs" style="margin:0;">Urutan</label>
                        <input class="form-input" type="number"
                               name="methods[{{ $method->code }}][sort_order]"
                               value="{{ $method->sort_order }}" min="0"
                               style="max-width:80px;min-height:36px;">
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <button class="btn btn-primary" type="submit">Simpan Pengaturan</button>
</form>
@endsection
