@extends('layouts.admin')

@section('title', 'Dashboard Operasional & Bisnis — Geprek GLO Salatiga')

@section('content')
<div class="card" style="padding: var(--space-lg); margin-bottom: var(--space-lg); background: var(--surface-card);">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
        <div>
            <div class="text-xs text-muted" style="margin-bottom: 4px;">
                Pusat Informasi Operasional &amp; Laporan Bisnis Restoran
            </div>
            <h2 style="font-size: 1.5rem; margin-bottom: 4px;">Halo, {{ auth()->user()->name }}!</h2>
            <p class="text-sm text-muted">
                Pantau omset harian, antrean dapur, dan performa pelanggan.
            </p>
        </div>

        <div id="store-status" style="display: flex; align-items: center; gap: 12px; background: var(--surface); padding: 8px 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
            <div>
                <span class="text-xs text-muted block">Status Kedai Publik:</span>
                <strong id="dashboard-store-label" class="{{ $settings->is_open ? 'text-ok' : 'text-off' }}" style="font-size: 1.1rem;">
                    {{ $settings->is_open ? 'SEDANG BUKA' : 'SEDANG TUTUP' }}
                </strong>
            </div>
            <button type="button" class="btn btn-sm {{ $settings->is_open ? 'btn-secondary' : 'btn-primary' }}" onclick="toggleStoreStatus()">
                {{ $settings->is_open ? 'Tutup Kedai' : 'Buka Kedai' }}
            </button>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" role="alert" style="background: rgba(39, 174, 96, 0.15); border: 1px solid var(--ok); color: #2ecc71; padding: 12px 16px; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: var(--space-lg);">
        {{ session('success') }}
    </div>
@endif

<div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-bottom: var(--space-xl); background: var(--surface); padding: 10px 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
        <a href="#ia-sales" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
            <span>Penjualan</span>
        </a>
        <a href="#ia-orders" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-receipt" aria-hidden="true"></i>
            <span>Pesanan</span>
        </a>
        <a href="#ia-customers" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span>Pelanggan</span>
        </a>
        <a href="#ia-kitchen" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-fire-burner" aria-hidden="true"></i>
            <span>Dapur</span>
        </a>
        <a href="#ia-menu" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-utensils" aria-hidden="true"></i>
            <span>Menu</span>
        </a>
    </div>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
        <span>Laporan Bisnis</span>
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
</div>

<section id="ia-sales" style="margin-bottom: var(--space-2xl);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
        <div>
            <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Operational Analytics: Sales</span>
            <h3 style="font-size: 1.35rem; margin-top: 2px;">Ringkasan Penjualan Hari Ini</h3>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('admin.reports.sales') }}" class="btn btn-secondary btn-sm">Laporan Sales Lengkap</a>
            <a href="{{ route('admin.reports.products') }}" class="btn btn-secondary btn-sm">Performa Produk</a>
        </div>
    </div>

    <div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom: var(--space-lg);">
        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Pendapatan Berhasil (Paid Hari Ini)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #2ecc71; margin-top: 4px;">
                Rp{{ number_format($operational['sales_summary']['paid_revenue'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-dim" style="margin-top: 2px;">
                @if($operational['sales_summary']['revenue_growth'] !== null)
                    <span style="color: {{ $operational['sales_summary']['revenue_growth'] >= 0 ? '#2ecc71' : '#e74c3c' }}; font-weight: 600;">
                        {{ $operational['sales_summary']['revenue_growth'] >= 0 ? '+' : '' }}{{ $operational['sales_summary']['revenue_growth'] }}%
                    </span> vs kemarin
                @else
                    Kemarin: Rp{{ number_format($operational['sales_summary']['paid_revenue_yesterday'], 0, ',', '.') }}
                @endif
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Volume Order Hari Ini</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #FFFFFF; margin-top: 4px;">
                {{ $operational['sales_summary']['order_count'] }} Pesanan
            </div>
            <div class="text-xs text-dim" style="margin-top: 2px;">
                <span style="color:#10b981;"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $operational['sales_summary']['completed_count'] }} selesai</span> ·
                <span style="color:#ef4444;"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i> {{ $operational['sales_summary']['cancelled_count'] }} batal</span>
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Rata-rata Transaksi (AOV)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--warning); margin-top: 4px;">
                Rp{{ number_format($operational['sales_summary']['aov'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-dim" style="margin-top: 2px;">Per transaksi terbayar hari ini</div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Net Sales (Valid Orders)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #3498db; margin-top: 4px;">
                Rp{{ number_format($operational['sales_summary']['net_sales'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-dim" style="margin-top: 2px;">Diskon: -Rp{{ number_format($operational['sales_summary']['discount'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="card" style="padding: var(--space-md); margin-bottom: var(--space-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div>
                <h4 style="font-size: 1.15rem; margin: 0; color: #FFFFFF;">Grafik Tren Penjualan</h4>
                <span class="text-xs text-muted">Visualisasi pendapatan omset berdasarkan rentang waktu operasional</span>
            </div>
            <div style="display: flex; gap: 6px;">
                <button type="button" class="btn btn-sm btn-primary" id="btn-chart-today" onclick="switchSalesChart('today')">Hari Ini (Per Jam)</button>
                <button type="button" class="btn btn-sm btn-secondary" id="btn-chart-7d" onclick="switchSalesChart('last_7_days')">7 Hari Terakhir</button>
                <button type="button" class="btn btn-sm btn-secondary" id="btn-chart-30d" onclick="switchSalesChart('last_30_days')">30 Hari Terakhir</button>
            </div>
        </div>

        @foreach(['today' => 'Hari Ini', 'last_7_days' => '7 Hari Terakhir', 'last_30_days' => '30 Hari Terakhir'] as $chartKey => $chartTitle)
            @php
                $chart = $operational['chart_data'][$chartKey];
                $maxVal = max(1, max($chart['values'] ?: [1]));
            @endphp
            <div id="chart-wrap-{{ $chartKey }}" class="sales-chart-view" style="{{ $chartKey === 'today' ? '' : 'display:none;' }}">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span class="text-xs text-muted">Total Omset: <strong style="color:#2ecc71;">Rp{{ number_format($chart['total'], 0, ',', '.') }}</strong></span>
                    <span class="text-xs text-muted">Tampilan: <strong>{{ $chartTitle }}</strong></span>
                </div>
                <div style="display: flex; align-items: flex-end; gap: {{ $chartKey === 'last_30_days' ? '4px' : '10px' }}; height: 180px; padding: 20px 8px 0; background: var(--surface); border-radius: var(--radius-md); border: 1px solid var(--border); overflow-x: auto;">
                    @foreach($chart['values'] as $idx => $val)
                        @php
                            $heightPct = $val > 0 ? max(8, round(($val / $maxVal) * 90)) : 3;
                        @endphp
                        <div style="flex: 1; min-width: {{ $chartKey === 'last_30_days' ? '18px' : '28px' }}; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end;"
                             title="{{ $chart['labels'][$idx] }}: Rp{{ number_format($val, 0, ',', '.') }} ({{ $chart['counts'][$idx] }} order)">
                            <div style="width: 100%; height: {{ $heightPct }}%; background: {{ $val > 0 ? '#2ecc71' : 'rgba(255,255,255,0.06)' }}; border-radius: 3px 3px 0 0; transition: all 0.3s ease;"></div>
                            <span class="text-muted" style="font-size: 0.65rem; margin-top: 6px; white-space: nowrap;">{{ $chart['labels'][$idx] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div class="card" style="padding: var(--space-md);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
                <h4 style="font-size: 1.1rem; color: #FFFFFF;">Top Selling Products (Menu Terlaris)</h4>
                <p class="text-xs text-muted">Berdasarkan jumlah porsi dan total penjualan.</p>
            </div>
            <a href="{{ route('admin.reports.products') }}" class="btn btn-secondary btn-sm">Lihat Semua Menu &rarr;</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Menu / Item</th>
                        <th>Varian</th>
                        <th style="text-align: right;">Unit Terjual</th>
                        <th style="text-align: right;">Total Nilai Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operational['top_selling_products'] as $idx => $p)
                        <tr>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); font-weight: 700;">{{ $idx + 1 }}</span>
                            </td>
                            <td><strong style="color: var(--text);">{{ $p->item_name }}</strong></td>
                            <td>
                                @if($p->variant_name)
                                    <span class="badge" style="background: rgba(52, 152, 219, 0.15); color: #3498db; font-size: 0.75rem;">{{ $p->variant_name }}</span>
                                @else
                                    <span class="text-muted text-xs">Standar</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <strong>{{ number_format($p->total_qty, 0, ',', '.') }} pcs</strong>
                            </td>
                            <td style="text-align: right; color: var(--primary); font-weight: 700;">
                                Rp{{ number_format($p->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                Belum ada data penjualan produk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="ia-orders" style="margin-bottom: var(--space-2xl);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
        <div>
            <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Operational Status</span>
            <h3 style="font-size: 1.35rem; margin-top: 2px;">Status Pesanan &amp; Antrean Aktif</h3>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">Semua Pesanan ({{ $operational['order_status']['total'] }})</a>
            <a href="{{ route('admin.cashiers.index') }}" class="btn btn-secondary btn-sm">Akun Kasir ({{ $cashierCount }})</a>
        </div>
    </div>

    <div class="grid grid-cols-2 grid-cols-6 gap-sm" style="margin-bottom: var(--space-md);">
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="card" style="padding: 10px; text-decoration: none; color: inherit; text-align: center;">
            <div class="text-xs text-muted">Pending</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: var(--warning); margin-top: 2px;">{{ $operational['order_status']['pending'] }}</div>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}" class="card" style="padding: 10px; text-decoration: none; color: inherit; text-align: center;">
            <div class="text-xs text-muted">Confirmed</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: #3498db; margin-top: 2px;">{{ $operational['order_status']['confirmed'] }}</div>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'preparing']) }}" class="card" style="padding: 10px; text-decoration: none; color: inherit; text-align: center;">
            <div class="text-xs text-muted">Preparing</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: #9b59b6; margin-top: 2px;">{{ $operational['order_status']['preparing'] }}</div>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready']) }}" class="card" style="padding: 10px; text-decoration: none; color: inherit; text-align: center;">
            <div class="text-xs text-muted">Ready</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: #2ecc71; margin-top: 2px;">{{ $operational['order_status']['ready'] }}</div>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="card" style="padding: 10px; text-decoration: none; color: inherit; text-align: center;">
            <div class="text-xs text-muted">Completed</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: #27ae60; margin-top: 2px;">{{ $operational['order_status']['completed'] }}</div>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="card" style="padding: 10px; text-decoration: none; color: inherit; text-align: center;">
            <div class="text-xs text-muted">Cancelled</div>
            <div style="font-size: 1.3rem; font-weight: 800; color: #e74c3c; margin-top: 2px;">{{ $operational['order_status']['cancelled'] }}</div>
        </a>
    </div>

    <div class="card" style="padding: var(--space-md);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h4 style="font-size: 1.1rem; color: #FFFFFF;">Pesanan Terbaru Masuk</h4>
            <span class="badge badge-open">{{ $recentOrders->count() }} Pesanan Terkini</span>
        </div>

        @if($recentOrders->isEmpty())
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                Belum ada pesanan aktif tercatat.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nomor &amp; Waktu</th>
                            <th>Pelanggan</th>
                            <th>Layanan &amp; Meja</th>
                            <th>Items &amp; Catatan</th>
                            <th>Status Dapur</th>
                            <th>Total Tagihan</th>
                            <th>Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        @php
                            $kStatus = $order->kitchen_status ?: 'queued';
                            $kBadgeClass = match($kStatus) {
                                'queued' => 'badge-closed',
                                'prepared' => 'badge-promo',
                                'ready' => 'badge-best-seller',
                                'completed' => 'badge-open',
                                default => 'badge-closed',
                            };
                            $kLabel = match($kStatus) {
                                'queued' => 'Antrean Masuk',
                                'prepared' => 'Sedang Dimasak',
                                'ready' => 'Siap Saji',
                                'completed' => 'Selesai',
                                default => 'Antrean Masuk',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="font-weight: 700; color: var(--text); text-decoration: none;">
                                    {{ $order->order_number }}
                                </a>
                                <div class="text-xs text-muted">{{ $order->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td>
                                <strong style="color: var(--text);">{{ $order->customer_name }}</strong>
                                <div class="text-xs text-muted">{{ $order->customer_phone ?: 'Tanpa nomor' }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,255,255,0.06); font-size: 0.75rem;">
                                    {{ ucfirst(str_replace('_', ' ', $order->service_method)) }}
                                </span>
                                @if($order->table_number)
                                    <div class="text-xs" style="color: var(--primary); font-weight: 600; margin-top: 2px;">
                                        Meja #{{ $order->table_number }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-xs" style="max-width: 260px;">
                                    @foreach($order->items as $it)
                                        <div>&bull; {{ $it->quantity }}x {{ $it->item_name }} @if($it->variant_name)({{ $it->variant_name }})@endif</div>
                                    @endforeach
                                    @if($order->order_notes)
                                        <div style="color: var(--warning); margin-top: 2px; font-style: italic;">
                                            "{{ $order->order_notes }}"
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $kBadgeClass }}" style="font-size: 0.75rem;">
                                    {{ $kLabel }}
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--primary);">{{ $order->formatted_total }}</strong>
                            </td>
                            <td>
                                <form action="{{ route('admin.orders.kitchen-status', $order) }}" method="POST" style="display: flex; gap: 4px;">
                                    @csrf
                                    @method('PATCH')
                                    @if($kStatus === 'queued')
                                        <input type="hidden" name="kitchen_status" value="prepared">
                                        <button type="submit" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 2px 8px;">Mulai Masak</button>
                                    @elseif($kStatus === 'prepared')
                                        <input type="hidden" name="kitchen_status" value="ready">
                                        <button type="submit" class="btn btn-sm" style="background: #27ae60; color: #fff; font-size: 0.75rem; padding: 2px 8px;">Siap Saji</button>
                                    @elseif($kStatus === 'ready')
                                        <input type="hidden" name="kitchen_status" value="completed">
                                        <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 2px 8px;">Selesai</button>
                                    @else
                                        <span class="text-xs text-ok">Selesai</span>
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<section id="ia-customers" style="margin-bottom: var(--space-2xl);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
        <div>
            <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Customer &amp; Finance</span>
            <h3 style="font-size: 1.35rem; margin-top: 2px;">Ringkasan Pelanggan, Pembayaran &amp; Promo</h3>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm">Direktori Pelanggan</a>
            <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary btn-sm">Daftar Promo</a>
        </div>
    </div>

    <div class="grid grid-cols-1 grid-cols-3 gap-md">
        <div class="card" style="padding: var(--space-md);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h4 style="font-size:1.05rem;color:#FFFFFF;margin:0;">Pelanggan (Customers)</h4>
                <a href="{{ route('admin.reports.customers') }}" class="text-xs text-muted">Laporan &rarr;</a>
            </div>
            <div class="grid grid-cols-3 gap-xs" style="margin-bottom:10px;">
                <div style="background:var(--surface);padding:8px 4px;border-radius:var(--radius-sm);text-align:center;border:1px solid var(--border);">
                    <div class="text-xs text-muted">Total</div>
                    <strong style="font-size:1.05rem;">{{ $operational['customer_summary']['total_customers'] }}</strong>
                </div>
                <div style="background:var(--surface);padding:8px 4px;border-radius:var(--radius-sm);text-align:center;border:1px solid var(--border);">
                    <div class="text-xs text-muted">Baru Bln Ini</div>
                    <strong style="font-size:1.05rem;color:#2ecc71;">+{{ $operational['customer_summary']['new_this_month'] }}</strong>
                </div>
                <div style="background:var(--surface);padding:8px 4px;border-radius:var(--radius-sm);text-align:center;border:1px solid var(--border);">
                    <div class="text-xs text-muted">Repeat</div>
                    <strong style="font-size:1.05rem;color:#3498db;">{{ $operational['customer_summary']['repeat_customers'] }}</strong>
                </div>
            </div>
            <h5 style="font-size:0.8rem;color:var(--text-muted);margin-bottom:6px;">Top Customers:</h5>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @forelse($operational['customer_summary']['top_customers']->take(3) as $c)
                    <div style="display:flex;justify-content:space-between;font-size:0.8rem;background:var(--surface);padding:6px 8px;border-radius:var(--radius-sm);border:1px solid var(--border);">
                        <span>{{ $c->name }} ({{ $c->orders_count }}x)</span>
                        <strong class="text-primary">Rp{{ number_format((int)$c->total_spent, 0, ',', '.') }}</strong>
                    </div>
                @empty
                    <div class="text-xs text-muted" style="text-align:center;padding:10px;">Belum ada transaksi pelanggan.</div>
                @endforelse
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h4 style="font-size:1.05rem;color:#FFFFFF;margin:0;">Pembayaran (Payments)</h4>
                <a href="{{ route('admin.reports.payments') }}" class="text-xs text-muted">Laporan &rarr;</a>
            </div>
            <div style="background:var(--surface);padding:8px 12px;border-radius:var(--radius-sm);border:1px solid var(--border);margin-bottom:10px;">
                <span class="text-xs text-muted">Total Pembayaran Berhasil:</span>
                <div style="font-size:1.25rem;font-weight:700;color:#2ecc71;">
                    Rp{{ number_format($operational['payment_summary']['total_paid_amount'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-muted" style="margin-top:2px;">{{ $operational['payment_summary']['total_paid_count'] }} transaksi lunas</div>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;font-size:0.8rem;">
                @foreach($operational['payment_summary']['by_method'] as $pm)
                    <div style="display:flex;justify-content:space-between;background:var(--surface);padding:5px 8px;border-radius:var(--radius-sm);border:1px solid var(--border);">
                        <span>{{ match($pm->method_code) { 'cash_pickup' => 'Cash Pickup', 'qris' => 'QRIS', 'cod' => 'COD', default => $pm->method_code } }}</span>
                        <strong>Rp{{ number_format($pm->paid_amount, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h4 style="font-size:1.05rem;color:#FFFFFF;margin:0;">Promosi (Promotions)</h4>
                <a href="{{ route('admin.reports.promotions') }}" class="text-xs text-muted">Laporan &rarr;</a>
            </div>
            <div style="background:var(--surface);padding:8px 12px;border-radius:var(--radius-sm);border:1px solid var(--border);margin-bottom:10px;">
                <span class="text-xs text-muted">Total Diskon Diberikan:</span>
                <div style="font-size:1.25rem;font-weight:700;color:var(--warning);">
                    Rp{{ number_format($operational['promotion_summary']['total_discount_given'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-muted" style="margin-top:2px;">{{ $operational['promotion_summary']['total_usages'] }} kali pemakaian promo</div>
            </div>
            <div style="font-size:0.85rem;background:var(--surface);padding:8px;border-radius:var(--radius-sm);border:1px solid var(--border);">
                <span class="text-xs text-muted block">Promo Paling Sering Dipakai:</span>
                @if($operational['promotion_summary']['top_promo'])
                    <strong style="color:var(--primary);font-size:0.95rem;">{{ $operational['promotion_summary']['top_promo']->code }}</strong>
                    <div class="text-xs text-muted">{{ $operational['promotion_summary']['top_promo']->count }}x klaim &middot; Diskon: Rp{{ number_format($operational['promotion_summary']['top_promo']->total_discount, 0, ',', '.') }}</div>
                @else
                    <span class="text-xs text-muted">Belum ada pemakaian promo.</span>
                @endif
            </div>
        </div>
    </div>
</section>

<section id="ia-kitchen" style="margin-bottom: var(--space-2xl);">
    <div style="margin-bottom: 12px;">
        <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Kitchen</span>
        <h3 style="font-size: 1.35rem; margin-top: 2px;">Cooking Queue &amp; Ready Orders</h3>
        <p class="text-sm text-muted">Alur kerja tim dapur: Antrean Masuk (Queued) &rarr; Sedang Dimasak (Prepared) &rarr; Siap Disajikan (Ready).</p>
    </div>

    <div class="grid grid-cols-1 grid-cols-3 gap-md" style="margin-bottom: var(--space-lg);">
        <div class="card" style="padding: var(--space-md);">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <h4 style="font-size: 1rem; color: #FFFFFF;">1. Queued (Menunggu)</h4>
                <span class="badge" style="background: rgba(255,255,255,0.08);">{{ $queuedOrders->count() }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($queuedOrders as $qOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-start" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.9rem;">{{ $qOrder->order_number }}</strong>
                        <span class="badge" style="font-size: 0.7rem; background: rgba(255,255,255,0.06);">{{ $qOrder->table_number ?: $qOrder->service_method }}</span>
                    </div>
                    <div class="text-xs text-muted" style="margin-bottom: 6px;">{{ $qOrder->customer_name }} &middot; {{ $qOrder->created_at->format('H:i') }}</div>

                    <div class="text-xs" style="margin-bottom: 8px; border-top: 1px dashed var(--border); padding-top: 6px;">
                        @foreach($qOrder->items as $it)
                            <div>&bull; {{ $it->quantity }}x {{ $it->item_name }} @if($it->chicken_cut)({{ $it->chicken_cut }})@endif</div>
                        @endforeach
                    </div>

                    @if($qOrder->order_notes)
                        <div class="text-xs" style="color: var(--warning); margin-bottom: 6px;">
                            Note: {{ $qOrder->order_notes }}
                        </div>
                    @endif

                    <form action="{{ route('admin.orders.kitchen-status', $qOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kitchen_status" value="prepared">
                        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; font-size: 0.8rem;">Mulai Masak &rarr;</button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Tidak ada antrean pesanan masuk.
                </div>
                @endforelse
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <h4 style="font-size: 1rem; color: #FFFFFF;">2. Prepared (Sedang Dimasak)</h4>
                <span class="badge" style="background: rgba(243,156,18,0.15); color: var(--warning);">{{ $preparedOrders->count() }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($preparedOrders as $pOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-start" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.9rem;">{{ $pOrder->order_number }}</strong>
                        <span class="badge" style="font-size: 0.7rem; background: rgba(255,255,255,0.06);">{{ $pOrder->table_number ?: $pOrder->service_method }}</span>
                    </div>
                    <div class="text-xs text-muted" style="margin-bottom: 6px;">{{ $pOrder->customer_name }} &middot; {{ $pOrder->created_at->format('H:i') }}</div>

                    <div class="text-xs" style="margin-bottom: 8px; border-top: 1px dashed var(--border); padding-top: 6px;">
                        @foreach($pOrder->items as $it)
                            <div>&bull; {{ $it->quantity }}x {{ $it->item_name }} @if($it->chicken_cut)({{ $it->chicken_cut }})@endif</div>
                        @endforeach
                    </div>

                    <form action="{{ route('admin.orders.kitchen-status', $pOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kitchen_status" value="ready">
                        <button type="submit" class="btn btn-wa btn-sm" style="width: 100%; font-size: 0.82rem;">Siap Disajikan</button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Tidak ada menu yang sedang digoreng/dimasak.
                </div>
                @endforelse
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <div class="flex justify-between items-center" style="margin-bottom: 12px;">
                <h4 style="font-size: 1rem; color: #FFFFFF;">3. Ready (Siap Disajikan)</h4>
                <span class="badge" style="background: rgba(39, 174, 96, 0.15); color: var(--ok);">{{ $readyOrders->count() }}</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @forelse($readyOrders as $rOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-start" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.9rem;">{{ $rOrder->order_number }}</strong>
                        <span class="badge" style="font-size: 0.7rem; background: rgba(39, 174, 96, 0.15); color: var(--ok);">SIAP</span>
                    </div>
                    <div class="text-xs text-muted" style="margin-bottom: 6px;">
                        {{ $rOrder->customer_name }} &middot; {{ $rOrder->table_number ? 'Meja #' . $rOrder->table_number : ucfirst(str_replace('_', ' ', $rOrder->service_method)) }}
                    </div>

                    <form action="{{ route('admin.orders.kitchen-status', $rOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kitchen_status" value="completed">
                        <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; font-size: 0.8rem;">Pesanan Telah Selesai</button>
                    </form>
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Tidak ada pesanan yang siap disajikan.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section id="ia-menu" style="margin-bottom: var(--space-2xl);">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
        <div>
            <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Menu Categories</span>
            <h3 style="font-size: 1.35rem; margin-top: 2px;">Menu Items, Food Items &amp; Add-Ons</h3>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('admin.items.index') }}" class="btn btn-primary btn-sm">+ Kelola Semua Menu</a>
            <a href="{{ route('admin.stock.index') }}" class="btn btn-secondary btn-sm">Kontrol Stok</a>
        </div>
    </div>

    <div class="grid grid-cols-2 grid-cols-4 gap-md" style="margin-bottom: var(--space-md);">
        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Total Menu Items</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #FFFFFF; margin-top: 4px;">{{ $totalMenu }}</div>
            <div class="text-xs text-dim">Semua kategori menu</div>
        </div>
        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Food Items (Makanan)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--primary); margin-top: 4px;">{{ $foodItemsCount }}</div>
            <div class="text-xs text-dim">Paket, aneka ayam &amp; sup</div>
        </div>
        <div class="card" style="padding: var(--space-md);">
            <div class="text-xs text-muted">Menu Tersedia</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--ok); margin-top: 4px;">{{ $availableMenu }}</div>
            <div class="text-xs text-dim">Dapat dipesan pelanggan</div>
        </div>
        <div class="card" style="padding: var(--space-md);'var(--off)' : 'var(--border)' }};">
            <div class="text-xs text-muted">Menu Habis (Stok Kosong)</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: {{ $outOfStockMenu > 0 ? 'var(--off)' : '#FFFFFF' }}; margin-top: 4px;">{{ $outOfStockMenu }}</div>
            <div class="text-xs text-dim">Dinonaktifkan di web</div>
        </div>
    </div>

    <div class="card" style="padding: var(--space-md); margin-bottom: var(--space-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div>
                <h4 style="font-size: 1.05rem; color: #FFFFFF;">Daftar Pilihan Add-Ons (Tambahan Kuliner)</h4>
                <p class="text-xs text-muted">Pilihan pelengkap makanan sesuai arsitektur informasi Geprek GLO.</p>
            </div>
            <span class="badge" style="background: rgba(255,255,255,0.06);">6 Add-Ons Terdaftar</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama Add-On</th>
                        <th>Kategori / Jenis</th>
                        <th>Tambahan Harga</th>
                        <th>Status Layanan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($addOns as $addon)
                    <tr>
                        <td><strong style="color: var(--text);">{{ $addon['name'] }}</strong></td>
                        <td><span class="badge" style="background: rgba(255,255,255,0.06); font-size: 0.75rem;">{{ $addon['type'] }}</span></td>
                        <td style="color: var(--primary); font-weight: 700;">+Rp{{ number_format($addon['price'], 0, ',', '.') }}</td>
                        <td><span class="badge badge-open">Tersedia</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="ia-support" style="margin-bottom: var(--space-2xl);">
    <div style="margin-bottom: 12px;">
        <span class="eyebrow" style="color: var(--primary); font-weight: 700;">Information Architecture: Support / Notes</span>
        <h3 style="font-size: 1.35rem; margin-top: 2px;">Order Notes, Customer Requests &amp; Staff Messages</h3>
    </div>

    <div class="grid grid-cols-1 grid-cols-2 gap-md">
        <div class="card" style="padding: var(--space-md);">
            <h4 style="font-size: 1.05rem; margin-bottom: 10px; color: #FFFFFF;">
                Order Notes &amp; Customer Requests Terbaru
            </h4>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @forelse($ordersWithNotes as $nOrder)
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 10px;">
                    <div class="flex justify-between items-center" style="margin-bottom: 4px;">
                        <strong style="color: var(--text); font-size: 0.85rem;">{{ $nOrder->order_number }} ({{ $nOrder->customer_name }})</strong>
                        <span class="text-xs text-muted">{{ $nOrder->table_number ?: $nOrder->service_method }}</span>
                    </div>
                    @if($nOrder->order_notes)
                        <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                            @foreach(explode(',', $nOrder->order_notes) as $tag)
                                <span class="badge" style="background: rgba(243,156,18,0.15); color: var(--warning); font-size: 0.7rem; border: 1px solid rgba(243,156,18,0.3);">
                                    {{ trim($tag) }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if($nOrder->staff_notes)
                        <div class="text-xs text-dim" style="margin-top: 4px; font-style: italic;">
                            Pesan Staf: {{ $nOrder->staff_notes }}
                        </div>
                    @endif
                </div>
                @empty
                <div class="text-xs text-muted" style="text-align: center; padding: 20px;">
                    Belum ada pesanan dengan catatan khusus pelanggan.
                </div>
                @endforelse
            </div>
        </div>

        <div class="card" style="padding: var(--space-md);">
            <h4 style="font-size: 1.05rem; margin-bottom: 10px; color: #FFFFFF;">
                Staff Messages &amp; Pengumuman Kedai
            </h4>
            <p class="text-xs text-muted" style="margin-bottom: 14px;">
                Informasi penting antarstaf untuk mengantisipasi lonjakan antrean atau bahan habis.
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="background: rgba(243, 156, 18, 0.1); border: 1px solid rgba(243, 156, 18, 0.3); border-radius: var(--radius-md); padding: 12px;">
                    <div class="flex items-center gap-xs" style="margin-bottom: 4px;">
                        <span class="badge" style="background: var(--warning); color: #000; font-weight: 700; font-size: 0.7rem;">Kitchen Delay</span>
                        <strong style="color: var(--warning); font-size: 0.85rem;">Status Antrean Dapur</strong>
                    </div>
                    <p class="text-xs text-muted" style="line-height: 1.4;">
                        @if($queuedOrders->count() + $preparedOrders->count() >= 5)
                            Antrean dapur sedang padat ({{ $queuedOrders->count() + $preparedOrders->count() }} pesanan berjalan). Perkiraan waktu masak 15 - 20 menit.
                        @else
                            Kapasitas dapur normal. Pesanan dapat diproses tepat waktu dalam 5 - 10 menit.
                        @endif
                    </p>
                </div>

                <div style="background: {{ $outOfStockMenu > 0 ? 'rgba(215, 38, 61, 0.1)' : 'rgba(39, 174, 96, 0.1)' }}; border: 1px solid {{ $outOfStockMenu > 0 ? 'rgba(215, 38, 61, 0.3)' : 'rgba(39, 174, 96, 0.3)' }}; border-radius: var(--radius-md); padding: 12px;">
                    <div class="flex items-center gap-xs" style="margin-bottom: 4px;">
                        <span class="badge {{ $outOfStockMenu > 0 ? 'badge-closed' : 'badge-open' }}" style="font-size: 0.7rem;">
                            {{ $outOfStockMenu > 0 ? 'Item Unavailable' : 'Stock Ready' }}
                        </span>
                        <strong style="font-size: 0.85rem; color: {{ $outOfStockMenu > 0 ? 'var(--primary)' : 'var(--ok)' }};">
                            Ketersediaan Stok Bahan
                        </strong>
                    </div>
                    <p class="text-xs text-muted" style="line-height: 1.4;">
                        @if($outOfStockMenu > 0)
                            Terdapat <strong>{{ $outOfStockMenu }} menu</strong> yang berstatus HABIS. Pastikan beri tahu kasir dan perbarui di pengatur stok.
                        @else
                            Seluruh menu, potongan ayam, dan minuman berstatus TERSEDIA.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="grid grid-cols-1 grid-cols-2 gap-lg" style="margin-bottom: var(--space-xl);">
    <div class="card" style="padding: var(--space-lg);">
        <h4 style="margin-bottom: 12px; font-size: 1.05rem;">Status Stok Potongan Ayam</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($chickenCuts as $cut)
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <span style="font-weight: 500;">{{ $cut->name }}</span>
                <button type="button"
                        class="badge {{ $cut->is_available ? 'badge-open' : 'badge-closed' }}"
                        onclick="quickToggleCut({{ $cut->id }}, this)"
                        style="cursor: pointer; border: 1px solid currentColor;">
                    {{ $cut->is_available ? 'Tersedia' : 'Habis' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card" style="padding: var(--space-lg);">
        <h4 style="margin-bottom: 12px; font-size: 1.05rem;">Status Opsi Minuman Paket</h4>
        <div style="display: flex; flex-direction: column; gap: 8px;">
            @foreach($drinkOptions as $drink)
            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--surface); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <span style="font-weight: 500;">{{ $drink->name }}</span>
                <button type="button"
                        class="badge {{ $drink->is_available ? 'badge-open' : 'badge-closed' }}"
                        onclick="quickToggleDrink({{ $drink->id }}, this)"
                        style="cursor: pointer; border: 1px solid currentColor;">
                    {{ $drink->is_available ? 'Tersedia' : 'Habis' }}
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function switchSalesChart(key) {
        document.querySelectorAll('.sales-chart-view').forEach(el => el.style.display = 'none');
        const target = document.getElementById('chart-wrap-' + key);
        if (target) target.style.display = 'block';

        const buttons = {
            'today': document.getElementById('btn-chart-today'),
            'last_7_days': document.getElementById('btn-chart-7d'),
            'last_30_days': document.getElementById('btn-chart-30d')
        };
        for (let k in buttons) {
            if (buttons[k]) {
                buttons[k].className = (k === key) ? 'btn btn-sm btn-primary' : 'btn btn-sm btn-secondary';
            }
        }
    }

    async function toggleStoreStatus() {
        try {
            const res = await fetch("{{ route('admin.store.toggle') }}", {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                const label = document.getElementById('dashboard-store-label');
                if (label) {
                    label.textContent = data.is_open ? 'SEDANG BUKA' : 'SEDANG TUTUP';
                    label.className = data.is_open ? 'text-ok' : 'text-off';
                }
                const headerText = document.getElementById('header-store-text');
                if (headerText) {
                    headerText.textContent = 'Kedai: ' + (data.is_open ? 'BUKA' : 'TUTUP');
                }
                alert(data.message);
                window.location.reload();
            }
        } catch (err) {
            console.error(err);
            alert('Gagal mengubah status kedai.');
        }
    }

    async function quickToggleCut(id, btn) {
        try {
            const res = await fetch(`/admin/cuts/${id}/availability`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                btn.className = 'badge ' + (data.is_available ? 'badge-open' : 'badge-closed');
                btn.textContent = data.is_available ? 'Tersedia' : 'Habis';
            }
        } catch (err) { console.error(err); }
    }

    async function quickToggleDrink(id, btn) {
        try {
            const res = await fetch(`/admin/drinks/${id}/availability`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.success) {
                btn.className = 'badge ' + (data.is_available ? 'badge-open' : 'badge-closed');
                btn.textContent = data.is_available ? 'Tersedia' : 'Habis';
            }
        } catch (err) { console.error(err); }
    }
</script>
@endpush
