@extends(auth()->user()?->role === 'cashier' ? 'layouts.cashier' : 'layouts.admin')

@section('title', 'Pembayaran — Geprek GLO')

@section('content')
@php $isCashier = auth()->user()?->role === 'cashier'; @endphp

<div class="page-heading">
    <div>
        <span class="eyebrow">{{ $isCashier ? 'Kasir' : 'Admin' }} — Pembayaran</span>
        <h1>Manajemen Pembayaran</h1>
        <p class="text-muted">{{ $isCashier ? 'Konfirmasi dan kelola transaksi pembayaran.' : 'Riwayat dan status pembayaran seluruh transaksi.' }}</p>
    </div>
    @if($isCashier)
        <a href="{{ route('cashier.payments.settings') }}" class="btn btn-secondary">Pengaturan Pembayaran</a>
    @endif
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
    <div class="card" style="padding:16px;border-top:3px solid var(--warning);">
        <span class="text-muted text-xs">Belum dibayar</span>
        <div style="font-size:1.8rem;font-weight:800;margin-top:4px;">{{ $counts['unpaid'] }}</div>
    </div>
    <div class="card" style="padding:16px;border-top:3px solid var(--info);">
        <span class="text-muted text-xs">Menunggu verifikasi QRIS</span>
        <div style="font-size:1.8rem;font-weight:800;margin-top:4px;">{{ $counts['pending'] }}</div>
    </div>
    <div class="card" style="padding:16px;border-top:3px solid var(--ok);">
        <span class="text-muted text-xs">Sudah dibayar</span>
        <div style="font-size:1.8rem;font-weight:800;margin-top:4px;">{{ $counts['paid'] }}</div>
    </div>
</div>

<form method="GET" class="card" style="padding:16px;margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1 1 160px;">
        <label class="form-label text-xs">Status</label>
        <select class="form-input" name="status">
            <option value="">Semua status</option>
            @foreach(['unpaid','pending_verification','paid','failed','refunded'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1 1 160px;">
        <label class="form-label text-xs">Metode</label>
        <select class="form-input" name="method">
            <option value="">Semua metode</option>
            @foreach(['cash_pickup','qris','cod'] as $m)
                <option value="{{ $m }}" @selected(request('method') === $m)>{{ str_replace('_',' ',strtoupper($m)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <button class="btn btn-primary" type="submit">Filter</button>
    </div>
</form>

<div class="card" style="overflow-x:auto;">
    <table class="table">
        <thead>
            <tr>
                <th>Pesanan</th>
                <th>Metode</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Waktu</th>
                <th>Staf</th>
                @if($isCashier)<th>Aksi</th>@endif
            </tr>
        </thead>
        <tbody>
        @forelse($payments as $payment)
            @php
                $statusColor = match($payment->status) {
                    'paid' => '#2ecc71',
                    'pending_verification' => '#3498db',
                    'unpaid','pending' => '#f39c12',
                    'failed','rejected' => '#e74c3c',
                    default => 'var(--text-muted)',
                };
            @endphp
            <tr>
                <td>
                    <strong>{{ $payment->order->order_number }}</strong>
                    <div class="text-xs text-muted">{{ $payment->order->customer_name }}</div>
                </td>
                <td><span style="font-weight:600;text-transform:uppercase;font-size:0.8rem;">{{ str_replace('_',' ',$payment->method_code) }}</span></td>
                <td><strong>Rp{{ number_format($payment->amount,0,',','.') }}</strong></td>
                <td>
                    <span class="badge" style="background:{{ $statusColor }}22;color:{{ $statusColor }};border:1px solid {{ $statusColor }}44;">
                        {{ ucfirst(str_replace('_',' ',$payment->status)) }}
                    </span>
                </td>
                <td class="text-xs text-muted">{{ ($payment->verified_at ?? $payment->created_at)->format('d/m/Y H:i') }}</td>
                <td class="text-xs text-muted">{{ $payment->confirmedBy?->name ?: '—' }}</td>
                @if($isCashier)
                <td>
                    @if(in_array($payment->method_code,['cash_pickup','cod']) && $payment->status !== 'paid')
                        <form method="POST" action="{{ route('cashier.payments.confirm-cash',$payment) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="amount" value="{{ $payment->amount }}">
                            <button class="btn btn-primary btn-sm" type="submit">Konfirmasi tunai</button>
                        </form>
                    @elseif($payment->method_code === 'qris')
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            @if($payment->proof_image)
                                <a href="{{ route('cashier.payments.proof',$payment) }}" target="_blank" rel="noreferrer" class="btn btn-secondary btn-sm">Lihat Bukti</a>
                            @endif
                            @if($payment->status !== 'paid')
                                <form method="POST" action="{{ route('cashier.payments.verify-qris',$payment) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="decision" value="approve">
                                    <button class="btn btn-primary btn-sm" type="submit">Terima QRIS</button>
                                </form>
                                <form method="POST" action="{{ route('cashier.payments.verify-qris',$payment) }}" style="margin-top:4px;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="decision" value="reject">
                                    <input class="form-input" name="rejection_reason" placeholder="Alasan penolakan" required style="margin-bottom:4px;">
                                    <button class="btn btn-secondary btn-sm" type="submit">Tolak</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <span class="text-xs text-muted">—</span>
                    @endif
                </td>
                @endif
            </tr>
        @empty
            <tr><td colspan="{{ $isCashier ? 7 : 6 }}" style="text-align:center;padding:32px;color:var(--text-muted);">Belum ada transaksi pembayaran.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $payments->links() }}</div>
@endsection
