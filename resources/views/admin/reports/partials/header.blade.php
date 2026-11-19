@props(['title', 'subtitle', 'type', 'period'])

<div class="page-heading" style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:var(--space-md);flex-wrap:wrap;">
    <div>
        <span class="eyebrow">Business Intelligence &amp; Reports</span>
        <h1 style="margin-top:2px;margin-bottom:4px;">{{ $title }}</h1>
        <p class="text-muted">{{ $subtitle }}</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        @if(in_array($type, ['sales', 'products', 'payments', 'promotions', 'inventory', 'purchasing', 'customers']))
            <a href="{{ route('admin.reports.export', array_merge(['type' => $type], request()->all())) }}" class="btn btn-secondary btn-sm" title="Unduh data dalam format CSV">
                📥 Ekspor CSV
            </a>
        @endif
    </div>
</div>

{{-- Navigation Tabs between Report Modules --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:var(--space-lg);border-bottom:1px solid var(--border);padding-bottom:12px;">
    <a href="{{ route('admin.reports.sales', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'sales' ? 'btn-primary' : 'btn-secondary' }}">
        Penjualan (Sales)
    </a>
    <a href="{{ route('admin.reports.products', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'products' ? 'btn-primary' : 'btn-secondary' }}">
        Performa Menu (Products)
    </a>
    <a href="{{ route('admin.reports.payments', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'payments' ? 'btn-primary' : 'btn-secondary' }}">
        Pembayaran (Payments)
    </a>
    <a href="{{ route('admin.reports.promotions', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'promotions' ? 'btn-primary' : 'btn-secondary' }}">
        Promosi (Promotions)
    </a>
    <a href="{{ route('admin.reports.inventory', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'inventory' ? 'btn-primary' : 'btn-secondary' }}">
        Bahan Baku (Inventory)
    </a>
    <a href="{{ route('admin.reports.purchasing', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'purchasing' ? 'btn-primary' : 'btn-secondary' }}">
        Pembelian (Purchasing)
    </a>
    <a href="{{ route('admin.reports.customers', request()->only(['preset', 'start_date', 'end_date'])) }}" class="btn btn-sm {{ $type === 'customers' ? 'btn-primary' : 'btn-secondary' }}">
        Pelanggan (Customers)
    </a>
</div>

{{-- Date Filter Bar --}}
<section class="card" style="padding:var(--space-md);margin-bottom:var(--space-lg);">
    <form method="GET" action="{{ url()->current() }}" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px;">
        <div style="flex:1 1 200px;">
            <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Rentang Periode Cepat</label>
            <select class="form-input" name="preset" id="report-preset-select" onchange="toggleCustomDates(this.value)">
                <option value="today" @selected(request('preset', $period['preset']) === 'today')>Hari Ini</option>
                <option value="yesterday" @selected(request('preset', $period['preset']) === 'yesterday')>Kemarin</option>
                <option value="last_7_days" @selected(request('preset', $period['preset']) === 'last_7_days')>7 Hari Terakhir</option>
                <option value="last_30_days" @selected(request('preset', $period['preset']) === 'last_30_days')>30 Hari Terakhir</option>
                <option value="this_month" @selected(request('preset', $period['preset']) === 'this_month')>Bulan Ini</option>
                <option value="last_month" @selected(request('preset', $period['preset']) === 'last_month')>Bulan Lalu</option>
                <option value="custom" @selected(request('preset', $period['preset']) === 'custom')>Custom Tanggal</option>
            </select>
        </div>

        <div id="custom-date-start-wrap" style="flex:1 1 160px; {{ request('preset', $period['preset']) === 'custom' ? '' : 'display:none;' }}">
            <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Dari Tanggal</label>
            <input class="form-input" type="date" name="start_date" id="report-start-date" value="{{ request('start_date', $period['start_date']) }}">
        </div>

        <div id="custom-date-end-wrap" style="flex:1 1 160px; {{ request('preset', $period['preset']) === 'custom' ? '' : 'display:none;' }}">
            <label class="text-xs text-muted" style="display:block;margin-bottom:4px;">Sampai Tanggal</label>
            <input class="form-input" type="date" name="end_date" id="report-end-date" value="{{ request('end_date', $period['end_date']) }}">
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="min-width:120px;">Terapkan Filter</button>
        </div>

        <div style="margin-left:auto;align-self:center;">
            <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));font-weight:600;font-size:0.85rem;">
                📅 {{ $period['label'] }}
            </span>
        </div>
    </form>
</section>

<script>
function toggleCustomDates(value) {
    const startWrap = document.getElementById('custom-date-start-wrap');
    const endWrap = document.getElementById('custom-date-end-wrap');
    if (value === 'custom') {
        startWrap.style.display = 'block';
        endWrap.style.display = 'block';
    } else {
        startWrap.style.display = 'none';
        endWrap.style.display = 'none';
    }
}
</script>
