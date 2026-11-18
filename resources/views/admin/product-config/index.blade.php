@extends('layouts.admin')

@section('title', 'Konfigurasi Produk — Geprek GLO')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">Menu management</span>
        <h1>Konfigurasi produk</h1>
        <p class="text-muted">Kelola pilihan produk, varian, dan hubungan modifier tanpa hardcode.</p>
    </div>
</div>

<div class="grid grid-2 gap-lg">
    <section class="card" style="padding: var(--space-lg);">
        <h2>Modifier group</h2>
        <form method="POST" action="{{ route('admin.modifier-groups.store') }}" class="stack-md" style="margin-top: 16px;">
            @csrf
            <input class="form-control" name="name" placeholder="Contoh: Pilihan Sambal" required>
            <input class="form-control" name="slug" placeholder="pilihan-sambal" required>
            <div class="grid grid-2 gap-sm">
                <input class="form-control" type="number" name="min_selections" value="0" min="0" placeholder="Minimum">
                <input class="form-control" type="number" name="max_selections" min="1" placeholder="Maksimum">
                <input class="form-control" type="number" name="sort_order" value="0" min="0" placeholder="Urutan">
                <label class="form-check"><input type="checkbox" name="is_required" value="1"> Wajib dipilih</label>
            </div>
            <button class="btn btn-primary" type="submit">Tambah group</button>
        </form>
    </section>

    <section class="card" style="padding: var(--space-lg);">
        <h2>Product variant</h2>
        <form method="POST" action="{{ route('admin.variants.store') }}" class="stack-md" style="margin-top: 16px;">
            @csrf
            <select class="form-control" name="menu_item_id" required>
                <option value="">Pilih produk</option>
                @foreach($menuItems as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach
            </select>
            <input class="form-control" name="name" placeholder="Contoh: Large" required>
            <div class="grid grid-2 gap-sm">
                <input class="form-control" type="number" name="price" min="0" placeholder="Harga" required>
                <input class="form-control" name="sku" placeholder="SKU (opsional)">
                <input class="form-control" type="number" name="sort_order" value="0" min="0" placeholder="Urutan">
                <label class="form-check"><input type="checkbox" name="is_available" value="1" checked> Tersedia</label>
            </div>
            <button class="btn btn-primary" type="submit">Tambah varian</button>
        </form>
    </section>
</div>

<section class="card" style="padding: var(--space-lg); margin-top: var(--space-lg);">
    <h2>Modifier groups dan pilihan</h2>
    <div class="stack-lg" style="margin-top: 16px;">
        @forelse($groups as $group)
            <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                <div class="flex justify-between items-center" style="gap: 12px;">
                    <div>
                        <strong>{{ $group->name }}</strong>
                        <span class="text-muted text-xs">({{ $group->is_active ? 'aktif' : 'nonaktif' }}, {{ $group->is_required ? 'wajib' : 'opsional' }})</span>
                    </div>
                    <form method="POST" action="{{ route('admin.modifier-groups.destroy', $group) }}">@csrf @method('DELETE')<button class="btn btn-secondary btn-sm" type="submit">Nonaktifkan</button></form>
                </div>
                <div class="grid grid-2 gap-sm" style="margin-top: 12px;">
                    @foreach($group->options as $option)
                        <div class="badge" style="display:flex; justify-content:space-between; padding: 10px 12px;">
                            <span>{{ $option->name }} <small>+Rp{{ number_format($option->price_delta, 0, ',', '.') }}</small></span>
                            @if(!$option->is_available)<small>Tidak tersedia</small>@endif
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('admin.modifier-options.store', $group) }}" class="flex gap-sm" style="margin-top: 12px; flex-wrap: wrap;">
                    @csrf
                    <input class="form-control" name="name" placeholder="Nama pilihan" required style="flex: 1 1 180px;">
                    <input class="form-control" type="number" name="price_delta" value="0" min="0" placeholder="Tambahan harga" required style="flex: 0 1 150px;">
                    <input class="form-control" type="number" name="sort_order" value="0" min="0" placeholder="Urutan" required style="flex: 0 1 100px;">
                    <button class="btn btn-secondary" type="submit">Tambah pilihan</button>
                </form>
            </div>
        @empty
            <p class="text-muted">Belum ada modifier group.</p>
        @endforelse
    </div>
</section>

<section class="card" style="padding: var(--space-lg); margin-top: var(--space-lg);">
    <h2>Assignment modifier ke produk</h2>
    <div class="stack-md" style="margin-top: 16px;">
        @foreach($menuItems as $item)
            <form method="POST" action="{{ route('admin.items.modifier-groups', $item) }}" class="flex justify-between items-center" style="gap: 12px; border-top: 1px solid var(--border); padding-top: 12px; flex-wrap: wrap;">
                @csrf @method('PUT')
                <strong>{{ $item->name }}</strong>
                <div class="flex gap-sm" style="flex-wrap: wrap;">
                    @foreach($groups as $group)
                        <label class="form-check text-xs"><input type="checkbox" name="modifier_groups[][id]" value="{{ $group->id }}" {{ $item->modifierGroups->contains($group) ? 'checked' : '' }}> {{ $group->name }}</label>
                    @endforeach
                </div>
                <button class="btn btn-secondary btn-sm" type="submit">Simpan</button>
            </form>
        @endforeach
    </div>
</section>
@endsection
