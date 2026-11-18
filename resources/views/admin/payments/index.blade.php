@extends('layouts.admin')

@section('title', 'Manajemen Pembayaran — Geprek GLO')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow">Payment management</span><h1>Pembayaran</h1></div>
    <a href="{{ route('admin.payments.settings') }}" class="btn btn-secondary">Pengaturan</a>
</div>

<div class="grid grid-3 gap-md" style="margin-bottom:20px;">
    <div class="card" style="padding:16px;"><span class="text-muted text-xs">Belum dibayar</span><h2>{{ $counts['unpaid'] }}</h2></div>
    <div class="card" style="padding:16px;"><span class="text-muted text-xs">Menunggu QRIS</span><h2>{{ $counts['pending'] }}</h2></div>
    <div class="card" style="padding:16px;"><span class="text-muted text-xs">Sudah dibayar</span><h2>{{ $counts['paid'] }}</h2></div>
</div>

<form method="GET" class="card flex gap-sm" style="padding:16px;margin-bottom:20px;">
    <select class="form-control" name="status"><option value="">Semua status</option>@foreach(['unpaid','pending_verification','paid','failed','refunded'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
    <select class="form-control" name="method"><option value="">Semua metode</option>@foreach(['cash_pickup','qris','cod'] as $method)<option value="{{ $method }}" @selected(request('method') === $method)>{{ str_replace('_',' ',strtoupper($method)) }}</option>@endforeach</select>
    <button class="btn btn-primary" type="submit">Filter</button>
</form>

<div class="card" style="overflow-x:auto;">
<table class="data-table">
    <thead><tr><th>Pesanan</th><th>Metode</th><th>Nominal</th><th>Status</th><th>Waktu</th><th>Staf</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($payments as $payment)
        <tr>
            <td><strong>{{ $payment->order->order_number }}</strong><br><small>{{ $payment->order->customer_name }}</small></td>
            <td>{{ strtoupper(str_replace('_',' ', $payment->method_code)) }}</td>
            <td>Rp{{ number_format($payment->amount,0,',','.') }}</td>
            <td><span class="badge badge-promo">{{ ucfirst(str_replace('_',' ', $payment->status)) }}</span></td>
            <td>{{ $payment->verified_at?->format('d/m/Y H:i') ?: $payment->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $payment->confirmedBy?->name ?: '—' }}</td>
            <td>
                @if(in_array($payment->method_code,['cash_pickup','cod']) && $payment->status !== 'paid')
                    <form method="POST" action="{{ route('admin.payments.confirm-cash',$payment) }}" class="flex gap-sm">@csrf @method('PATCH')
                        <input type="hidden" name="amount" value="{{ $payment->amount }}">
                        <button class="btn btn-primary btn-sm" type="submit">Konfirmasi tunai</button>
                    </form>
                @elseif($payment->method_code === 'qris')
                    @if($payment->proof_image)<a href="{{ route('admin.payments.proof',$payment) }}" target="_blank" rel="noreferrer" class="btn btn-secondary btn-sm">Bukti</a>@endif
                    @if($payment->status !== 'paid')
                        <form method="POST" action="{{ route('admin.payments.verify-qris',$payment) }}" class="stack-sm" style="margin-top:8px;">@csrf @method('PATCH')
                            <input type="hidden" name="decision" value="approve">
                            <button class="btn btn-primary btn-sm" type="submit">Terima</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.verify-qris',$payment) }}" class="stack-sm" style="margin-top:6px;">@csrf @method('PATCH')
                            <input type="hidden" name="decision" value="reject">
                            <input class="form-control" name="rejection_reason" placeholder="Alasan penolakan" required>
                            <button class="btn btn-secondary btn-sm" type="submit">Tolak</button>
                        </form>
                    @endif
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-muted">Belum ada transaksi.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div style="margin-top:16px;">{{ $payments->links() }}</div>
@endsection
