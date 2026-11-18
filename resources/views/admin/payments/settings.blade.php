@extends('layouts.admin')

@section('title', 'Pengaturan Pembayaran — Geprek GLO')

@section('content')
<div class="page-heading"><div><span class="eyebrow">Payment settings</span><h1>Metode pembayaran</h1></div></div>

<form method="POST" action="{{ route('admin.payments.settings.update') }}" enctype="multipart/form-data" class="card stack-md" style="padding:var(--space-lg);">
    @csrf @method('PUT')
    <h2>QRIS</h2>
    <label class="form-check"><input type="checkbox" name="qris_enabled" value="1" @checked($settings->qris_enabled)> Aktifkan QRIS</label>
    <input class="form-control" name="qris_merchant_name" value="{{ old('qris_merchant_name',$settings->qris_merchant_name) }}" placeholder="Nama merchant QRIS resmi">
    <textarea class="form-control" name="qris_instructions" placeholder="Instruksi pembayaran">{{ old('qris_instructions',$settings->qris_instructions) }}</textarea>
    @if($settings->qris_image)<img src="{{ asset('storage/'.$settings->qris_image) }}" alt="QRIS" style="max-width:220px;border-radius:12px;">@endif
    <input class="form-control" type="file" name="qris_image" accept="image/jpeg,image/png,image/webp">

    <h2 style="margin-top:12px;">Metode manual</h2>
    @foreach($methods as $method)
        <div class="flex justify-between items-center gap-sm" style="border-top:1px solid var(--border);padding-top:12px;">
            <label class="form-check"><input type="checkbox" name="methods[{{ $method->code }}][is_active]" value="1" @checked($method->is_active)> {{ $method->name }}</label>
            <input class="form-control" type="number" name="methods[{{ $method->code }}][sort_order]" value="{{ $method->sort_order }}" min="0" style="max-width:100px;">
        </div>
    @endforeach
    <button class="btn btn-primary" type="submit">Simpan pengaturan</button>
</form>
@endsection
