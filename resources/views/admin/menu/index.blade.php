@extends('layouts.admin')

@section('title', 'Manajemen Menu — Panel Admin Geprek GLO')

@section('content')
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: var(--space-lg);">
    <div>
        <h2 style="font-size: 1.4rem; margin-bottom: 4px;">Manajemen Menu</h2>
        <p class="text-sm text-muted">Tambah, ubah, perbarui harga, ganti foto, dan atur ketersediaan menu.</p>
    </div>
    <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        Tambah Menu Baru
    </a>
</div>

<!-- Filter Bar -->
<x-filter-panel :action="route('admin.items.index')">
    <div class="fp-field fp-field-lg">
        <label class="fp-label">Cari nama menu</label>
        <input class="fp-input" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama menu...">
    </div>
    <div class="fp-field fp-field-md">
        <label class="fp-label">Kategori</label>
        <select class="fp-select" name="category_id">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <x-slot name="buttons">
        <button class="fp-btn-primary" type="submit">Filter</button>
        @if(request()->anyFilled(['search','category_id']))
            <a href="{{ route('admin.items.index') }}" class="fp-btn-reset">Reset</a>
        @endif
    </x-slot>
</x-filter-panel>

<!-- Table of Menu Items -->
<div class="card" style="padding: var(--space-md); overflow-x: auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 68px;">Foto</th>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Status Stok</th>
                <th>Label</th>
                <th style="text-align: right; width: 136px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menuItems as $item)
            <tr>
                <td>
                    <div style="width: 52px; height: 40px; border-radius: var(--radius-sm); overflow: hidden; background: var(--surface-hover); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: var(--text-dim); text-align: center;">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fa-solid fa-image" aria-hidden="true"></i>
                        @endif
                    </div>
                </td>
                <td>
                    <strong style="color: var(--text); display: block;">{{ $item->name }}</strong>
                    @if($item->description)
                        <div class="text-xs text-muted" style="max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                            {{ $item->description }}
                        </div>
                    @endif
                </td>
                <td style="white-space: nowrap;">
                    <span class="badge" style="background: var(--surface-hover); color: var(--text-muted); border: 1px solid var(--border); font-size: 0.7rem;">
                        {{ $item->category->name ?? '-' }}
                    </span>
                </td>
                <td style="color: var(--primary); font-weight: 700; white-space: nowrap;">
                    {{ $item->formatted_price }}
                </td>
                <td style="white-space: nowrap;">
                    <span class="badge {{ $item->is_available ? 'badge-open' : 'badge-closed' }}" style="font-size: 0.7rem;">
                        {{ $item->is_available ? 'Tersedia' : 'Habis' }}
                    </span>
                </td>
                <td style="white-space: nowrap;">
                    @if($item->is_best_seller || $item->is_package)
                        <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: nowrap;">
                            @if($item->is_best_seller)
                                <span class="badge badge-best-seller" style="font-size: 0.7rem;">Best Seller</span>
                            @endif
                            @if($item->is_package)
                                <span class="badge badge-promo" style="font-size: 0.7rem;">Paket</span>
                            @endif
                        </div>
                    @else
                        <span class="text-xs text-dim">—</span>
                    @endif
                </td>
                <td style="text-align: right; white-space: nowrap;">
                    <div class="flex gap-xs justify-end" style="justify-content: flex-end; flex-wrap: nowrap;">
                        <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-secondary btn-sm" style="padding: 5px 12px; min-height: 32px; font-size: 0.8rem;">
                            Ubah
                        </a>
                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu {{ addslashes($item->name) }}?');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-secondary btn-sm" style="color: #FF5A5F; padding: 5px 12px; min-height: 32px; font-size: 0.8rem;">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Belum ada menu yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        {{ $menuItems->appends(request()->query())->links() }}
    </div>
</div>
@endsection
