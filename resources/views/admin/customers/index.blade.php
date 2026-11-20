@extends('layouts.admin')

@section('title', 'Pelanggan — Panel Admin Geprek GLO')

@section('content')
<div class="page-heading" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:var(--space-lg);">
    <div>
        <span class="eyebrow">Customer Management</span>
        <h1>Direktori Pelanggan</h1>
        <p class="text-muted">Kelola data pelanggan, pantau riwayat belanja, dan perbarui catatan internal.</p>
    </div>
</div>

<x-filter-panel :action="route('admin.customers.index')">
    <div class="fp-field fp-field-lg">
        <label class="fp-label">Cari pelanggan</label>
        <input class="fp-input" name="search" value="{{ request('search') }}" placeholder="Nama, telepon, atau email...">
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Status</label>
        <select class="fp-select" name="status">
            <option value="">Semua Status</option>
            <option value="active"   @selected(request('status') === 'active')>Aktif</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
        </select>
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Daftar dari</label>
        <input class="fp-input" type="date" name="date_from" value="{{ request('date_from') }}">
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Daftar sampai</label>
        <input class="fp-input" type="date" name="date_to" value="{{ request('date_to') }}">
    </div>
    <x-slot name="buttons">
        <button class="fp-btn-primary" type="submit">Filter</button>
        @if(request()->anyFilled(['search','status','date_from','date_to']))
            <a href="{{ route('admin.customers.index') }}" class="fp-btn-reset">Reset</a>
        @endif
    </x-slot>
</x-filter-panel>

<div class="card" style="padding:var(--space-md);margin-bottom:var(--space-lg);">
    <h3 style="margin-bottom:14px;">Tambah Pelanggan Baru</h3>
    <form method="POST" action="{{ route('admin.customers.store') }}" style="display:grid;gap:10px;">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;flex-wrap:wrap;">
            <div>
                <label class="fp-label">Nama lengkap *</label>
                <input class="fp-input" name="name" placeholder="Nama pelanggan" required value="{{ old('name') }}">
            </div>
            <div>
                <label class="fp-label">Nomor telepon *</label>
                <input class="fp-input" name="phone" placeholder="081234567890" required value="{{ old('phone') }}">
            </div>
            <div>
                <label class="fp-label">Email</label>
                <input class="fp-input" type="email" name="email" placeholder="Email (opsional)" value="{{ old('email') }}">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label class="fp-label">Status</label>
                <select class="fp-select" name="status" required>
                    <option value="active"   @selected(old('status','active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="fp-label">Catatan internal</label>
                <input class="fp-input" name="notes" placeholder="Catatan (opsional)" value="{{ old('notes') }}">
            </div>
        </div>
        <div><button type="submit" class="btn btn-primary">Simpan Data Pelanggan</button></div>
    </form>
</div>

<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <table class="table" style="width:100%;">
        <thead>
            <tr>
                <th>Pelanggan</th>
                <th>Kontak & Telepon</th>
                <th>Total Pesanan</th>
                <th>Total Belanja</th>
                <th>Pesanan Terakhir</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" style="text-decoration:none;font-weight:600;color:var(--text);">
                            {{ $customer->name }}
                        </a>
                        @if($customer->email)
                            <div class="text-xs text-muted">{{ $customer->email }}</div>
                        @endif
                        @if($customer->notes)
                            <div class="text-xs text-muted" style="font-style:italic;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $customer->notes }}">
                                <i class="fa-solid fa-note-sticky" aria-hidden="true"></i> {{ $customer->notes }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div><strong>{{ $customer->phone }}</strong></div>
                        <div class="text-xs text-muted">Norm: {{ $customer->normalized_phone }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));font-weight:600;">
                            {{ $customer->orders_count }} pesanan
                        </span>
                    </td>
                    <td>
                        <strong class="text-primary">{{ $customer->formatted_total_spending }}</strong>
                    </td>
                    <td>
                        @if($customer->last_order_at)
                            <div>{{ \Carbon\Carbon::parse($customer->last_order_at)->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($customer->last_order_at)->format('H:i') }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $customer->status === 'active' ? 'badge-open' : 'badge-closed' }}">
                            {{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:6px;">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-secondary btn-sm">
                                Detail & Transaksi
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Belum ada pelanggan yang sesuai dengan filter pencarian.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $customers->links() }}
    </div>
</section>
@endsection
