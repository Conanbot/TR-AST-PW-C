@props(['title', 'subtitle', 'type', 'period'])

<div class="page-heading" style="margin-bottom:var(--space-md);">
    <div>
        <span class="eyebrow">Laporan Bisnis</span>
        <h1 style="margin-top:2px;margin-bottom:4px;">{{ $title }}</h1>
        <p class="text-muted text-sm">{{ $subtitle }}</p>
    </div>
</div>

{{-- Report Navigation Tabs --}}
<div class="report-tabs" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:var(--space-lg);border-bottom:1px solid var(--border);padding-bottom:12px;">
    @php
        $tabs = [
            ['type' => 'sales',      'label' => 'Penjualan',   'route' => 'admin.reports.sales'],
            ['type' => 'products',   'label' => 'Menu',         'route' => 'admin.reports.products'],
            ['type' => 'payments',   'label' => 'Pembayaran',   'route' => 'admin.reports.payments'],
            ['type' => 'promotions', 'label' => 'Promosi',      'route' => 'admin.reports.promotions'],
            ['type' => 'customers',  'label' => 'Pelanggan',    'route' => 'admin.reports.customers'],
        ];
    @endphp
    @foreach($tabs as $tab)
        <a href="{{ route($tab['route'], request()->only(['preset', 'start_date', 'end_date'])) }}"
           class="btn btn-sm {{ $type === $tab['type'] ? 'btn-primary' : 'btn-secondary' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>

{{-- Date Filter Bar --}}
<section class="card" style="padding:var(--space-md);margin-bottom:var(--space-lg);">
    <form method="GET" action="{{ url()->current() }}" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px;">
        <div style="flex:1 1 180px;">
            <label class="form-label text-xs">Periode</label>
            <select class="form-input" name="preset" id="report-preset-select" onchange="toggleCustomDates(this.value)">
                <option value="today" @selected(request('preset', $period['preset']) === 'today')>Hari Ini</option>
                <option value="yesterday" @selected(request('preset', $period['preset']) === 'yesterday')>Kemarin</option>
                <option value="last_7_days" @selected(request('preset', $period['preset']) === 'last_7_days')>7 Hari Terakhir</option>
                <option value="last_30_days" @selected(request('preset', $period['preset']) === 'last_30_days')>30 Hari Terakhir</option>
                <option value="this_month" @selected(request('preset', $period['preset']) === 'this_month')>Bulan Ini</option>
                <option value="last_month" @selected(request('preset', $period['preset']) === 'last_month')>Bulan Lalu</option>
                <option value="custom" @selected(request('preset', $period['preset']) === 'custom')>Custom</option>
            </select>
        </div>
        <div id="custom-date-start-wrap" style="flex:1 1 140px;{{ request('preset', $period['preset']) === 'custom' ? '' : 'display:none;' }}">
            <label class="form-label text-xs">Dari</label>
            <input class="form-input" type="date" name="start_date" value="{{ request('start_date', $period['start_date']) }}">
        </div>
        <div id="custom-date-end-wrap" style="flex:1 1 140px;{{ request('preset', $period['preset']) === 'custom' ? '' : 'display:none;' }}">
            <label class="form-label text-xs">Sampai</label>
            <input class="form-input" type="date" name="end_date" value="{{ request('end_date', $period['end_date']) }}">
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </div>
        <div style="margin-left:auto;align-self:center;">
            <span class="badge badge-secondary" style="font-weight:600;">{{ $period['label'] }}</span>
        </div>
    </form>
</section>

@push('scripts')
<script>
function toggleCustomDates(val) {
    const show = val === 'custom';
    ['custom-date-start-wrap','custom-date-end-wrap'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = show ? '' : 'none';
    });
}
</script>
@endpush
