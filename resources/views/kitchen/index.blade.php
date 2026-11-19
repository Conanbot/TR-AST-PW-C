@extends('layouts.admin')

@section('title', 'Kitchen Display — Geprek GLO')

@section('content')
<div class="page-heading" style="flex-wrap:wrap;gap:12px;">
    <div>
        <span class="eyebrow">Kitchen Display System</span>
        <h1>Antrean Dapur</h1>
        <p class="text-muted">Diperbarui otomatis setiap 15 detik. <span id="kds-last-updated" class="text-xs"></span></p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <span class="text-xs text-muted">Filter status:</span>
        @php
            $filters = [
                '' => ['label' => 'Semua', 'color' => 'btn-secondary'],
                'confirmed' => ['label' => 'Baru', 'color' => 'btn-secondary'],
                'preparing' => ['label' => 'Dimasak', 'color' => 'btn-secondary'],
                'ready' => ['label' => 'Siap', 'color' => 'btn-secondary'],
            ];
            $activeFilter = request('status', '');
        @endphp
        @foreach($filters as $val => $f)
            <a href="{{ route('kitchen.index', $val ? ['status' => $val] : []) }}"
               class="btn btn-sm {{ $activeFilter === $val ? 'btn-primary' : $f['color'] }}">
                {{ $f['label'] }}
            </a>
        @endforeach
        <button onclick="window.kdsRefresh()" class="btn btn-secondary btn-sm" title="Refresh sekarang">↺ Refresh</button>
    </div>
</div>

<div id="kds-error" class="alert alert-error" style="display:none;" role="alert"></div>

<div id="kds-board" style="display:grid;gap:14px;">
    {{-- Filled by JS polling --}}
</div>

<div id="kds-empty" style="display:none;text-align:center;padding:60px 20px;color:var(--text-muted);">
    <div style="font-size:2rem;margin-bottom:12px;">✓</div>
    <h3>Tidak ada pesanan aktif</h3>
    <p class="text-sm">Semua pesanan sudah selesai atau belum ada pesanan masuk.</p>
</div>

@push('scripts')
<script>
(function() {
    const csrf   = document.querySelector('meta[name="csrf-token"]').content;
    const board  = document.getElementById('kds-board');
    const empty  = document.getElementById('kds-empty');
    const errBox = document.getElementById('kds-error');
    const updated = document.getElementById('kds-last-updated');
    const activeFilter = {{ json_encode(request('status', '')) }};

    function escHtml(v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    }

    function statusBadge(status) {
        const map = {
            confirmed: { label: 'Baru', bg: '#3498db22', color: '#3498db', border: '#3498db44' },
            preparing: { label: 'Dimasak', bg: '#9b59b622', color: '#9b59b6', border: '#9b59b644' },
            ready:     { label: 'Siap', bg: '#2ecc7122', color: '#2ecc71', border: '#2ecc7144' },
        };
        const s = map[status] || { label: status, bg: 'var(--surface)', color: 'var(--text-muted)', border: 'var(--border)' };
        return `<span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;background:${s.bg};color:${s.color};border:1px solid ${s.border};">${s.label}</span>`;
    }

    function nextAction(order) {
        if (order.status === 'confirmed') {
            return `<form method="POST" action="/kitchen/orders/${order.id}/status" style="display:inline;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="status" value="preparing">
                <button class="btn btn-primary btn-sm" type="submit">Mulai Masak</button>
            </form>`;
        }
        if (order.status === 'preparing') {
            return `<form method="POST" action="/kitchen/orders/${order.id}/status" style="display:inline;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="status" value="ready">
                <button class="btn btn-sm" style="background:#2ecc71;color:#fff;" type="submit">Tandai Ready</button>
            </form>`;
        }
        return `<span class="text-xs text-muted" style="color:#2ecc71;font-weight:700;">✓ Siap diambil</span>`;
    }

    function renderCard(order) {
        const wait = order.wait_minutes;
        const isLate = wait >= 20;
        const items = order.items.map(item => {
            let detail = `<div style="margin-bottom:6px;"><strong style="font-size:0.9rem;">${item.quantity}x ${escHtml(item.name)}</strong>`;
            if (item.variant) detail += `<div class="text-xs text-muted">Varian: ${escHtml(item.variant)}</div>`;
            if (item.cut)     detail += `<div class="text-xs text-muted">Potongan: ${escHtml(item.cut)}</div>`;
            if (item.drink)   detail += `<div class="text-xs text-muted">Minuman: ${escHtml(item.drink)}</div>`;
            if (item.modifiers && item.modifiers.length)
                detail += `<div class="text-xs text-muted">Pilihan: ${item.modifiers.map(escHtml).join(', ')}</div>`;
            if (item.note)    detail += `<div class="text-xs" style="color:var(--warning);font-style:italic;">Catatan: ${escHtml(item.note)}</div>`;
            detail += '</div>';
            return detail;
        }).join('');

        return `<article class="card" style="padding:16px;border-left:4px solid ${isLate ? '#e74c3c' : (order.status === 'ready' ? '#2ecc71' : (order.status === 'preparing' ? '#9b59b6' : '#3498db'))};${isLate ? 'background:rgba(231,76,60,0.05);' : ''}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;gap:12px;">
                <div>
                    <strong style="font-size:1rem;">${escHtml(order.order_number)}</strong>
                    <div class="text-xs text-muted" style="margin-top:2px;">${order.service_label} ${order.table ? '— Meja ' + escHtml(order.table) : ''}</div>
                </div>
                <div style="text-align:right;">
                    ${statusBadge(order.status)}
                    <div class="text-xs" style="margin-top:4px;color:${isLate ? '#e74c3c' : 'var(--text-muted)'};">
                        ${isLate ? '⚠ ' : ''}${wait} menit lalu
                    </div>
                </div>
            </div>
            <div style="border-top:1px solid var(--border);padding-top:12px;margin-bottom:12px;">${items}</div>
            ${order.customer_note ? `<div class="text-xs" style="background:rgba(243,156,18,0.1);border:1px solid rgba(243,156,18,0.3);border-radius:8px;padding:8px 10px;margin-bottom:12px;color:var(--warning);">
                <strong>Catatan:</strong> ${escHtml(order.customer_note)}
            </div>` : ''}
            <div>${nextAction(order)}</div>
        </article>`;
    }

    async function refresh() {
        try {
            const params = activeFilter ? `?status=${encodeURIComponent(activeFilter)}` : '';
            const resp = await fetch(`{{ route('kitchen.feed') }}${params}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            });
            if (!resp.ok) throw new Error('Feed gagal dimuat (HTTP ' + resp.status + ').');
            const data = await resp.json();

            const allOrders = [...(data.new || []), ...(data.preparing || []), ...(data.ready || [])];
            // Filter by active status client-side (feed already filtered server-side too)
            const filtered = activeFilter
                ? allOrders.filter(o => o.status === activeFilter)
                : allOrders;

            if (filtered.length === 0) {
                board.innerHTML = '';
                board.style.display = 'none';
                empty.style.display = 'block';
            } else {
                board.style.display = 'grid';
                empty.style.display = 'none';
                board.innerHTML = filtered.map(renderCard).join('');
            }

            errBox.style.display = 'none';
            updated.textContent = 'Diperbarui ' + new Date().toLocaleTimeString('id-ID');
        } catch(e) {
            errBox.textContent = e.message;
            errBox.style.display = 'block';
        }
    }

    window.kdsRefresh = refresh;
    refresh();
    setInterval(refresh, 15000);
})();
</script>
@endpush
@endsection
