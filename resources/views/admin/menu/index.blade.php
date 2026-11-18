@extends('layouts.admin')

@section('title', 'Manajemen Menu — Panel Admin Geprek GLO')

@section('content')
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: var(--space-lg);">
    <div>
        <h2 style="font-size: 1.4rem; margin-bottom: 4px;">Manajemen Menu</h2>
        <p class="text-sm text-muted">Tambah, ubah, perbarui harga, ganti foto, dan atur ketersediaan menu.</p>
    </div>
    <a href="{{ route('admin.items.create') }}" class="btn btn-primary">
        + Tambah Menu Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card" style="padding: var(--space-md); margin-bottom: var(--space-lg);">
    <form method="GET" action="{{ route('admin.items.index') }}" class="flex gap-sm flex-wrap items-center">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama menu..." class="form-control" style="max-width: 280px;">
        <select name="category_id" class="form-control" style="max-width: 200px;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        @if(request()->hasAny(['search', 'category_id']))
            <a href="{{ route('admin.items.index') }}" class="btn btn-secondary btn-sm text-dim">Reset</a>
        @endif
    </form>
</div>

<!-- Table of Menu Items -->
<div class="card" style="padding: var(--space-md); overflow-x: auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 70px;">Foto</th>
                <th>Nama Menu</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Status Stok</th>
                <th>Label</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menuItems as $item)
            <tr>
                <td>
                    <div style="width: 50px; height: 38px; border-radius: var(--radius-sm); overflow: hidden; background: #171719; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: var(--text-dim); text-align: center;">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <span>Placeholder</span>
                        @endif
                    </div>
                </td>
                <td>
                    <strong style="color: var(--text);">{{ $item->name }}</strong>
                    @if($item->description)
                        <div class="text-xs text-muted" style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $item->description }}
                        </div>
                    @endif
                </td>
                <td>
                    <span class="badge" style="background: rgba(255,255,255,0.06);">{{ $item->category->name ?? '-' }}</span>
                </td>
                <td style="color: var(--primary); font-weight: 700;">
                    {{ $item->formatted_price }}
                </td>
                <td>
                    <span class="badge {{ $item->is_available ? 'badge-open' : 'badge-closed' }}">
                        {{ $item->is_available ? 'Tersedia' : 'Habis' }}
                    </span>
                </td>
                <td>
                    @if($item->is_best_seller)
                        <span class="badge badge-best-seller" style="font-size: 0.65rem;">Best Seller</span>
                    @endif
                    @if($item->is_package)
                        <span class="badge badge-promo" style="font-size: 0.65rem;">Paket</span>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="flex gap-xs justify-end" style="justify-content: flex-end;">
                        <a href="{{ route('admin.items.edit', $item->id) }}" class="btn btn-secondary btn-sm" style="padding: 4px 10px;">
                            Ubah
                        </a>
                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu {{ $item->name }}?');" style="margin: 0;">
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
