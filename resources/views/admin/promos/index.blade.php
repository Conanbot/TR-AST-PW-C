@extends('layouts.admin')

@section('title', 'Manajemen Promo — Panel Admin Geprek GLO')

@section('content')
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: var(--space-md);">
    <div>
        <h2 style="font-size: 1.4rem; margin-bottom: 4px;">Manajemen Banner Promo</h2>
        <p class="text-sm text-muted">Atur banner promosi dan penawaran spesial yang tampil di halaman beranda.</p>
    </div>
    <a href="{{ route('admin.promos.create') }}" class="btn btn-primary">+ Tambah Promo</a>
</div>

<x-filter-panel :action="route('admin.promos.index')">
    <div class="fp-field fp-field-lg">
        <label class="fp-label">Cari nama / kode promo</label>
        <input class="fp-input" name="search" value="{{ request('search') }}" placeholder="Nama promo atau kode...">
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
        <label class="fp-label">Berlaku dari</label>
        <input class="fp-input" type="date" name="from" value="{{ request('from') }}">
    </div>
    <div class="fp-field fp-field-sm">
        <label class="fp-label">Berlaku sampai</label>
        <input class="fp-input" type="date" name="to" value="{{ request('to') }}">
    </div>
    <x-slot name="buttons">
        <button class="fp-btn-primary" type="submit">Filter</button>
        @if(request()->anyFilled(['search','status','from','to']))
            <a href="{{ route('admin.promos.index') }}" class="fp-btn-reset">Reset</a>
        @endif
    </x-slot>
</x-filter-panel>

<div class="card" style="padding: var(--space-md); overflow-x: auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 70px;">Banner</th>
                <th>Judul Promo</th>
                <th>Kode / Tipe</th>
                <th>Keterangan / Subtitle</th>
                <th>Status</th>
                <th>Kuota</th>
                <th>Periode Tayang</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($promos as $promo)
            <tr>
                <td>
                    <div style="width: 60px; height: 38px; border-radius: var(--radius-sm); overflow: hidden; background: #171719; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: var(--text-dim); text-align: center;">
                        @if($promo->image_url)
                            <img src="{{ $promo->image_url }}" alt="{{ $promo->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <span>No Banner</span>
                        @endif
                    </div>
                </td>
                <td>
                    <strong style="color: var(--text);">{{ $promo->title }}</strong>
                </td>
                <td><strong>{{ $promo->code ?: '-' }}</strong><div class="text-xs text-muted">{{ $promo->discount_type }}</div></td>
                <td style="max-width: 320px; font-size: 0.85rem; color: var(--text-muted);">
                    {{ $promo->subtitle ?? '-' }}
                </td>
                <td>
                    <span class="badge {{ $promo->is_active ? 'badge-open' : 'badge-closed' }}">
                        {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="text-xs text-muted">{{ $promo->usage_limit ? $promo->usages_count.' / '.$promo->usage_limit.' terpakai' : 'Tanpa batas' }}</td>
                <td class="text-xs text-muted">
                    @if($promo->start_date || $promo->end_date)
                        {{ $promo->start_date ? $promo->start_date->format('d M Y') : 'Sekarang' }}
                        s.d.
                        {{ $promo->end_date ? $promo->end_date->format('d M Y') : 'Seterusnya' }}
                    @else
                        Selalu Tayang
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="flex gap-xs justify-end" style="justify-content: flex-end;">
                        <a href="{{ route('admin.promos.show', $promo->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px;">Detail</a>
                        <form action="{{ route('admin.promos.toggle', $promo->id) }}" method="POST" style="margin:0;">@csrf @method('PATCH')<button type="submit" class="btn btn-secondary btn-sm" style="padding:4px 10px;">{{ $promo->is_active?'Nonaktifkan':'Aktifkan' }}</button></form>
                        <a href="{{ route('admin.promos.edit', $promo->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px;">
                            Ubah
                        </a>
                        <form action="{{ route('admin.promos.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Hapus promo ini?');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-secondary btn-sm" style="color: #FF5A5F; padding: 4px 10px;">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Belum ada banner promo yang dibuat.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        {{ $promos->links() }}
    </div>
</div>
@endsection
