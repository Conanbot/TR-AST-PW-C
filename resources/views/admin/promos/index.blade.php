@extends('layouts.admin')

@section('title', 'Manajemen Promo — Panel Admin Geprek GLO')

@section('content')
<div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: var(--space-lg);">
    <div>
        <h2 style="font-size: 1.4rem; margin-bottom: 4px;">🎉 Manajemen Banner Promo</h2>
        <p class="text-sm text-muted">Atur banner promosi dan penawaran spesial yang tampil di halaman beranda.</p>
    </div>
    <a href="{{ route('admin.promos.create') }}" class="btn btn-primary">
        + Tambah Banner Promo
    </a>
</div>

<div class="card" style="padding: var(--space-md); overflow-x: auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Judul Promo</th>
                <th>Keterangan / Subtitle</th>
                <th>Status</th>
                <th>Periode Tayang</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($promos as $promo)
            <tr>
                <td>
                    <strong style="color: var(--text);">{{ $promo->title }}</strong>
                </td>
                <td style="max-width: 320px; font-size: 0.85rem; color: var(--text-muted);">
                    {{ $promo->subtitle ?? '-' }}
                </td>
                <td>
                    <span class="badge {{ $promo->is_active ? 'badge-open' : 'badge-closed' }}">
                        {{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
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
                <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
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
