@extends('layouts.app')

@section('title', 'Lacak Pesanan ' . $order->order_number . ' — Geprek GLO')

@section('content')
<section class="home-section">
    <div class="container" style="max-width: 760px;">
        <div class="home-kicker">Pesanan {{ $order->order_number }}</div>
        <h1 style="margin: 14px 0 8px;">Status pesanan Anda</h1>
        <p class="text-muted">Halaman ini hanya dapat diakses melalui tautan aman pesanan Anda.</p>

        <div class="card" style="padding: var(--space-lg); margin-top: var(--space-xl);">
            <div class="flex justify-between items-center" style="gap: 16px; margin-bottom: var(--space-lg);">
                <div>
                    <span class="text-xs text-muted">Status</span>
                    <h2 style="margin-top: 4px; text-transform: capitalize;">{{ $order->status }}</h2>
                </div>
                <span class="badge {{ in_array($order->status, ['completed', 'ready']) ? 'badge-open' : ($order->status === 'cancelled' ? 'badge-closed' : 'badge-promo') }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            <div class="flex flex-col gap-sm">
                @foreach($order->items as $item)
                    <div class="flex justify-between" style="gap: 16px; border-top: 1px solid var(--border); padding-top: 10px;">
                        <span>{{ $item->quantity }}x {{ $item->item_name }}</span>
                        <strong>{{ $item->formatted_line_total ?? 'Rp' . number_format($item->line_total, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between" style="border-top: 1px solid var(--border); margin-top: 16px; padding-top: 16px;">
                <strong>Total</strong>
                <strong class="text-primary">{{ $order->formatted_total }}</strong>
            </div>
        </div>
        @php
            $payment = $order->payments->firstWhere('method_code', $order->payment_method);
        @endphp
        <div class="card" style="padding: var(--space-lg); margin-top: var(--space-lg);">
            <h2>Pembayaran</h2>
            <p><strong>{{ strtoupper(str_replace('_', ' ', $order->payment_method ?: 'unpaid')) }}</strong> · <span>{{ ucfirst(str_replace('_', ' ', $order->payment_status ?: 'unpaid')) }}</span></p>
            <p class="text-muted">Tagihan: {{ $order->formatted_total }}</p>
            @if($order->payment_method === 'qris')
                <img src="{{ !empty($paymentSettings->qris_image) ? asset('storage/' . $paymentSettings->qris_image) : asset('assets/img/qris-placeholder.png') }}"
                     alt="QRIS {{ $paymentSettings->qris_merchant_name ?: 'Geprek GLO' }}"
                     style="max-width:240px;width:100%;border-radius:12px;background:#fff;padding:10px;border:1px solid var(--border);">
                @if($paymentSettings->qris_instructions)<p style="margin-top:8px;">{{ $paymentSettings->qris_instructions }}</p>@endif
                @if($payment && $payment->status !== 'paid')
                    <form method="POST" action="{{ route('orders.payment-proof', ['orderNumber' => $order->order_number, 'token' => request()->route('token')]) }}" enctype="multipart/form-data" class="stack-sm" style="margin-top:12px;">
                        @csrf
                        <input type="hidden" name="claimed_amount" value="{{ $order->total }}">
                        <label class="form-label" for="payment-proof">Unggah bukti pembayaran</label>
                        <input id="payment-proof" class="form-control" type="file" name="proof" accept="image/jpeg,image/png,image/webp" required>
                        <button class="btn btn-primary" type="submit">Kirim bukti QRIS</button>
                    </form>
                @endif
            @elseif($order->payment_method === 'cash_pickup')
                <p class="text-muted">Siapkan uang tunai sesuai total tagihan saat mengambil pesanan.</p>
            @elseif($order->payment_method === 'cod')
                <p class="text-muted">Siapkan uang tunai sesuai total tagihan saat pesanan diterima.</p>
            @endif
            @if($payment && $payment->rejection_reason)
                <div class="alert alert-error">Bukti ditolak: {{ $payment->rejection_reason }}</div>
            @endif
        </div>
        <div class="card" style="padding: var(--space-lg); margin-top: var(--space-lg);">
            <h2 style="margin-bottom: 18px;">Perjalanan Pesanan</h2>

            @if($order->status === 'cancelled')
                <div class="alert alert-error" style="display: flex; align-items: center; gap: 12px; margin: 0;">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 1.35rem;" aria-hidden="true"></i>
                    <div>
                        <strong style="display: block;">Pesanan Dibatalkan</strong>
                        @if($order->cancellation_reason)
                            <span class="text-sm">{{ $order->cancellation_reason }}</span>
                        @else
                            <span class="text-sm">Pesanan ini telah dibatalkan.</span>
                        @endif
                    </div>
                </div>
            @else
                @php
                    $steps = [
                        'pending'   => ['label' => 'Pesanan Diterima', 'desc' => 'Pesanan Anda telah masuk ke sistem.', 'icon' => 'fa-receipt', 'rank' => 1],
                        'confirmed' => ['label' => 'Dikonfirmasi', 'desc' => 'Kedai telah mengonfirmasi pesanan Anda.', 'icon' => 'fa-clipboard-check', 'rank' => 2],
                        'preparing' => ['label' => 'Sedang Diproses', 'desc' => 'Hidangan sedang dimasak di dapur.', 'icon' => 'fa-fire-burner', 'rank' => 3],
                        'ready'     => ['label' => 'Siap Diambil / Diantar', 'desc' => 'Pesanan sudah siap disajikan atau diantar.', 'icon' => 'fa-bag-shopping', 'rank' => 4],
                        'completed' => ['label' => 'Pesanan Selesai', 'desc' => 'Terima kasih sudah memesan di Geprek GLO.', 'icon' => 'fa-check', 'rank' => 5],
                    ];
                    $currentRank = $steps[$order->status]['rank'] ?? 1;
                @endphp

                <div class="order-timeline">
                    @foreach($steps as $key => $step)
                        @php
                            $isDone = $step['rank'] < $currentRank || ($order->status === 'completed' && $step['rank'] === 5);
                            $isCurrent = $step['rank'] === $currentRank && $order->status !== 'completed';
                        @endphp
                        <div class="order-step {{ $isDone ? 'is-done' : ($isCurrent ? 'is-current' : 'is-upcoming') }}">
                            <div class="order-step-marker">
                                <span class="order-step-dot">
                                    <i class="fa-solid {{ $isDone ? 'fa-check' : $step['icon'] }}" aria-hidden="true"></i>
                                </span>
                                @if(!$loop->last)
                                    <span class="order-step-line"></span>
                                @endif
                            </div>
                            <div class="order-step-body">
                                <div class="order-step-title">
                                    <strong>{{ $step['label'] }}</strong>
                                    @if($isCurrent)
                                        <span class="order-step-pill">Saat ini</span>
                                    @endif
                                </div>
                                <p class="order-step-desc">{{ $step['desc'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
