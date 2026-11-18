@extends('layouts.admin')

@section('title', 'Kitchen Display — Geprek GLO')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow">Kitchen display system</span><h1>Antrean dapur</h1><p class="text-muted">Pesanan diperbarui otomatis setiap 15 detik.</p></div>
</div>
<div id="kitchen-error" class="alert alert-error" style="display:none;" role="alert"></div>
<div class="grid grid-3 gap-md" id="kitchen-board">
    <section><h2>New orders</h2><div class="stack-md" id="kitchen-new"></div></section>
    <section><h2>Preparing</h2><div class="stack-md" id="kitchen-preparing"></div></section>
    <section><h2>Ready</h2><div class="stack-md" id="kitchen-ready"></div></section>
</div>
@push('scripts')
<script>
(() => {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const columns = {new: document.getElementById('kitchen-new'), preparing: document.getElementById('kitchen-preparing'), ready: document.getElementById('kitchen-ready')};
    const escapeHtml = value => String(value || '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const renderCard = (order, nextStatus) => `<article class="card kitchen-order-card ${order.wait_minutes >= 20 ? 'is-late' : ''}" style="padding: 16px;">
        <div class="flex justify-between"><strong>${escapeHtml(order.order_number)}</strong><span class="text-xs">${order.wait_minutes} menit</span></div>
        <div class="stack-sm" style="margin-top: 12px;">${order.items.map(item => `<div><strong>${item.quantity}x ${escapeHtml(item.name)}</strong>${item.variant ? `<div class="text-xs text-muted">Varian: ${escapeHtml(item.variant)}</div>` : ''}${item.cut ? `<div class="text-xs text-muted">Potongan: ${escapeHtml(item.cut)}</div>` : ''}${item.drink ? `<div class="text-xs text-muted">Minuman: ${escapeHtml(item.drink)}</div>` : ''}${item.modifiers.length ? `<div class="text-xs text-muted">Pilihan: ${item.modifiers.map(escapeHtml).join(', ')}</div>` : ''}${item.note ? `<div class="text-xs text-muted">Catatan: ${escapeHtml(item.note)}</div>` : ''}</div>`).join('')}</div>
        ${order.customer_note ? `<div class="text-xs" style="margin-top: 10px;">Catatan order: ${escapeHtml(order.customer_note)}</div>` : ''}
        ${nextStatus ? `<form method="POST" action="/kitchen/orders/${order.id}/status" style="margin-top: 14px;"><input type="hidden" name="_token" value="${csrf}"><input type="hidden" name="status" value="${nextStatus}"><button class="btn btn-primary btn-sm" type="submit">${nextStatus === 'preparing' ? 'Mulai masak' : 'Tandai ready'}</button></form>` : ''}
    </article>`;
    async function refresh() {
        try {
            const response = await fetch('{{ route('kitchen.feed') }}', {headers: {'Accept': 'application/json'}});
            if (!response.ok) throw new Error('Kitchen feed gagal dimuat.');
            const data = await response.json();
            columns.new.innerHTML = data.new.map(order => renderCard(order, 'preparing')).join('') || '<p class="text-muted">Tidak ada pesanan baru.</p>';
            columns.preparing.innerHTML = data.preparing.map(order => renderCard(order, 'ready')).join('') || '<p class="text-muted">Tidak ada pesanan diproses.</p>';
            columns.ready.innerHTML = data.ready.map(order => renderCard(order, null)).join('') || '<p class="text-muted">Belum ada pesanan ready.</p>';
            document.getElementById('kitchen-error').style.display = 'none';
        } catch (error) {
            document.getElementById('kitchen-error').textContent = error.message;
            document.getElementById('kitchen-error').style.display = 'block';
        }
    }
    refresh();
    setInterval(refresh, 15000);
})();
</script>
@endpush
@endsection
