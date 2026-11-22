@extends('layouts.admin')

@section('title', 'Kelola Stok Cepat — Panel Admin Geprek GLO')

@section('content')
<div style="margin-bottom: var(--space-lg);">
    <h2 style="font-size: 1.4rem; margin-bottom: 4px;"><i class="fa-solid fa-bolt" aria-hidden="true"></i> Kelola Ketersediaan Stok Cepat</h2>
    <p class="text-sm text-muted">
        Ubah status menu, potongan ayam, dan minuman menjadi Tersedia atau Habis hanya dengan satu ketukan.
    </p>
</div>

<div class="grid grid-cols-1 grid-cols-2 gap-md" style="margin-bottom: var(--space-xl);">
    <div class="card" style="padding: var(--space-md);">
        <h4 style="margin-bottom: 10px;">Potongan Ayam</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($chickenCuts as $cut)
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md);">
                <span>{{ $cut->name }}</span>
                <button type="button"
                        class="badge {{ $cut->is_available ? 'badge-open' : 'badge-closed' }}"
                        onclick="toggleCutStatus({{ $cut->id }}, this)">
                    {{ $cut->is_available ? 'Tersedia' : 'Habis' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card" style="padding: var(--space-md);">
        <h4 style="margin-bottom: 10px;">Pilihan Minuman</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($drinkOptions as $drink)
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md);">
                <span>{{ $drink->name }}</span>
                <button type="button"
                        class="badge {{ $drink->is_available ? 'badge-open' : 'badge-closed' }}"
                        onclick="toggleDrinkStatus({{ $drink->id }}, this)">
                    {{ $drink->is_available ? 'Tersedia' : 'Habis' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card" style="padding: var(--space-md);">
    <h4 style="margin-bottom: 12px;">Daftar Seluruh Menu</h4>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        @foreach($menuItems as $item)
        <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div>
                <strong style="color: var(--text); font-size: 0.95rem;">{{ $item->name }}</strong>
                <div class="text-xs text-muted" style="margin-top: 2px;">
                    {{ $item->category->name ?? '-' }} &middot; {{ $item->formatted_price }}
                </div>
            </div>

            <button type="button"
                    class="badge {{ $item->is_available ? 'badge-open' : 'badge-closed' }}"
                    onclick="toggleItemStatus({{ $item->id }}, this)"
                    style="cursor: pointer; padding: 6px 12px; font-size: 0.8rem;">
                {{ $item->is_available ? 'Tersedia' : 'Habis' }}
            </button>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function toggleItemStatus(id, badge) {
        if (badge.disabled) return;
        badge.disabled = true;
        badge.style.opacity = '0.6';
        try {
            const res = await fetch(`{{ url('/admin/items') }}/${id}/availability`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                throw new Error(`HTTP Error: ${res.status}`);
            }
            const data = await res.json();
            if (data.success) {
                badge.className = data.is_available ? 'badge badge-open' : 'badge badge-closed';
                badge.textContent = data.is_available ? 'Tersedia' : 'Habis';
                window.showToast(data.message, 'success');
            } else {
                throw new Error(data.message || 'Gagal mengubah status.');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Gagal mengubah status menu. Terjadi gangguan server.', 'error');
        } finally {
            badge.disabled = false;
            badge.style.opacity = '1';
        }
    }

    async function toggleCutStatus(id, badge) {
        if (badge.disabled) return;
        badge.disabled = true;
        badge.style.opacity = '0.6';
        try {
            const res = await fetch(`{{ url('/admin/cuts') }}/${id}/availability`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                throw new Error(`HTTP Error: ${res.status}`);
            }
            const data = await res.json();
            if (data.success) {
                badge.className = data.is_available ? 'badge badge-open' : 'badge badge-closed';
                badge.textContent = data.is_available ? 'Tersedia' : 'Habis';
                window.showToast(data.message, 'success');
            } else {
                throw new Error(data.message || 'Gagal mengubah status.');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Gagal mengubah status potongan.', 'error');
        } finally {
            badge.disabled = false;
            badge.style.opacity = '1';
        }
    }

    async function toggleDrinkStatus(id, badge) {
        if (badge.disabled) return;
        badge.disabled = true;
        badge.style.opacity = '0.6';
        try {
            const res = await fetch(`{{ url('/admin/drinks') }}/${id}/availability`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) {
                throw new Error(`HTTP Error: ${res.status}`);
            }
            const data = await res.json();
            if (data.success) {
                badge.className = data.is_available ? 'badge badge-open' : 'badge badge-closed';
                badge.textContent = data.is_available ? 'Tersedia' : 'Habis';
                window.showToast(data.message, 'success');
            } else {
                throw new Error(data.message || 'Gagal mengubah status.');
            }
        } catch (e) {
            console.error(e);
            window.showToast('Gagal mengubah status minuman.', 'error');
        } finally {
            badge.disabled = false;
            badge.style.opacity = '1';
        }
    }
</script>
@endpush
