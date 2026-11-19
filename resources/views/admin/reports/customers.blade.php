@extends('layouts.admin')

@section('title', 'Laporan Analisis Pelanggan (Customer Report) — Panel Admin Geprek GLO')

@section('content')
@include('admin.reports.partials.header', [
    'title' => 'Laporan Aktivitas & Retensi Pelanggan',
    'subtitle' => 'Analisis pertumbuhan pelanggan baru, loyalitas repeat order, dan kontribusi omset per pelanggan.',
    'type' => 'customers',
    'period' => $period,
])

{{-- KPI Summary --}}
<div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom:var(--space-lg);">
    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--primary);">
        <div class="text-xs text-muted">Total Master Pelanggan</div>
        <div style="font-size:1.5rem;font-weight:800;margin-top:4px;">
            {{ number_format($data['summary']['total_customers'], 0, ',', '.') }} Orang
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Terdaftar di direktori database</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--ok);">
        <div class="text-xs text-muted">Pelanggan Baru (Periode Ini)</div>
        <div style="font-size:1.5rem;font-weight:800;color:#2ecc71;margin-top:4px;">
            +{{ number_format($data['summary']['new_customers_in_period'], 0, ',', '.') }} Orang
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Pertama kali terdata dari guest checkout</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid #3498db;">
        <div class="text-xs text-muted">Repeat Customers (Setia)</div>
        <div style="font-size:1.5rem;font-weight:800;color:#3498db;margin-top:4px;">
            {{ number_format($data['summary']['repeat_customers'], 0, ',', '.') }} Orang
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Memiliki 2 pesanan atau lebih</div>
    </div>

    <div class="card" style="padding:var(--space-md);border-top:3px solid var(--warning);">
        <div class="text-xs text-muted">Omset Pelanggan Terdata (Paid)</div>
        <div style="font-size:1.5rem;font-weight:800;color:var(--warning);margin-top:4px;">
            Rp{{ number_format($data['summary']['customer_spending_in_period'], 0, ',', '.') }}
        </div>
        <div class="text-xs text-muted" style="margin-top:2px;">Dari akun pelanggan pada periode ini</div>
    </div>
</div>

{{-- Top Customers Ranking Table --}}
<section class="card" style="padding:var(--space-md);overflow-x:auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3>Peringkat Kontribusi Pelanggan</h3>
        <small class="text-muted">🔒 Nomor telepon disamarkan demi kepatuhan privasi data pelanggan.</small>
    </div>

    <table class="table" style="width:100%;font-size:0.9rem;">
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Nama Pelanggan</th>
                <th>Kontak (Disamarkan)</th>
                <th>Status</th>
                <th style="text-align:right;">Pesanan Periode Ini</th>
                <th style="text-align:right;">Total Belanja Berhasil</th>
                <th>Terdaftar Sejak</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $rankOffset = ($data['customers']->currentPage() - 1) * $data['customers']->perPage(); @endphp
            @forelse($data['customers'] as $index => $customer)
                @php
                    $phone = $customer->phone ?: $customer->normalized_phone;
                    $maskedPhone = strlen($phone) > 7
                        ? substr($phone, 0, 4) . '****' . substr($phone, -3)
                        : '****';
                @endphp
                <tr>
                    <td>
                        <span class="badge" style="background:var(--surface-soft, rgba(0,0,0,0.05));font-weight:700;">
                            {{ $rankOffset + $index + 1 }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.customers.show', $customer) }}" style="text-decoration:none;font-weight:600;color:var(--text);">
                            {{ $customer->name }}
                        </a>
                    </td>
                    <td>
                        <span style="font-family:monospace;">{{ $maskedPhone }}</span>
                    </td>
                    <td>
                        <span class="badge {{ $customer->status === 'active' ? 'badge-open' : 'badge-closed' }}" style="font-size:0.75rem;">
                            {{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <strong>{{ $customer->orders_count }} pesanan</strong>
                    </td>
                    <td style="text-align:right;">
                        <strong class="text-primary">Rp{{ number_format((int)$customer->total_spent, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        {{ $customer->created_at->format('d/m/Y') }}
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-secondary btn-sm">
                            Profil
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:var(--space-lg);color:var(--text-muted);">
                        Belum ada aktivitas pelanggan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:var(--space-md);">
        {{ $data['customers']->links() }}
    </div>
</section>
@endsection
