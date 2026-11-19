<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\GoodsReceiving;
use App\Models\GoodsReceivingItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Promo;
use App\Models\PromotionUsage;
use App\Models\PurchaseOrder;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    /**
     * Resolve start and end dates from preset or custom input.
     */
    public function parsePeriod(?string $preset = 'today', ?string $customStart = null, ?string $customEnd = null): array
    {
        $now = now();
        $preset = $preset ?: 'today';

        switch ($preset) {
            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $prevStart = $now->copy()->subDays(2)->startOfDay();
                $prevEnd = $now->copy()->subDays(2)->endOfDay();
                $label = 'Kemarin (' . $start->format('d/m/Y') . ')';
                break;

            case 'last_7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDays(13)->startOfDay();
                $prevEnd = $now->copy()->subDays(7)->endOfDay();
                $label = '7 Hari Terakhir (' . $start->format('d/m') . ' - ' . $end->format('d/m/Y') . ')';
                break;

            case 'last_30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDays(59)->startOfDay();
                $prevEnd = $now->copy()->subDays(30)->endOfDay();
                $label = '30 Hari Terakhir (' . $start->format('d/m') . ' - ' . $end->format('d/m/Y') . ')';
                break;

            case 'this_month':
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subMonth()->startOfMonth()->startOfDay();
                $prevEnd = $now->copy()->subMonth()->endOfMonth()->endOfDay();
                $label = 'Bulan Ini (' . $start->format('F Y') . ')';
                break;

            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth()->startOfDay();
                $end = $now->copy()->subMonth()->endOfMonth()->endOfDay();
                $prevStart = $now->copy()->subMonths(2)->startOfMonth()->startOfDay();
                $prevEnd = $now->copy()->subMonths(2)->endOfMonth()->endOfDay();
                $label = 'Bulan Lalu (' . $start->format('F Y') . ')';
                break;

            case 'custom':
                if ($customStart && $customEnd) {
                    $start = Carbon::parse($customStart)->startOfDay();
                    $end = Carbon::parse($customEnd)->endOfDay();
                    if ($end->lt($start)) {
                        $temp = $start;
                        $start = $end->copy()->startOfDay();
                        $end = $temp->copy()->endOfDay();
                    }
                } else {
                    $start = $now->copy()->startOfDay();
                    $end = $now->copy()->endOfDay();
                }
                $diffDays = max(1, $start->diffInDays($end) + 1);
                $prevEnd = $start->copy()->subDay()->endOfDay();
                $prevStart = $prevEnd->copy()->subDays($diffDays - 1)->startOfDay();
                $label = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
                break;

            case 'today':
            default:
                $preset = 'today';
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDay()->startOfDay();
                $prevEnd = $now->copy()->subDay()->endOfDay();
                $label = 'Hari Ini (' . $start->format('d/m/Y') . ')';
                break;
        }

        return [
            'preset' => $preset,
            'start' => $start,
            'end' => $end,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'prev_start' => $prevStart,
            'prev_end' => $prevEnd,
            'label' => $label,
        ];
    }

    /**
     * Get operational data for Admin Dashboard.
     */
    public function getDashboardData(): array
    {
        $todayPeriod = $this->parsePeriod('today');
        $todayStart = $todayPeriod['start'];
        $todayEnd = $todayPeriod['end'];
        $yesterdayStart = $todayPeriod['prev_start'];
        $yesterdayEnd = $todayPeriod['prev_end'];

        // 1. Sales Summary Today
        $ordersToday = Order::whereBetween('created_at', [$todayStart, $todayEnd])->get();
        $ordersYesterday = Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->get();

        $validOrdersToday = $ordersToday->where('status', '!=', 'cancelled');
        $validOrdersYesterday = $ordersYesterday->where('status', '!=', 'cancelled');

        $paidOrdersToday = $validOrdersToday->filter(fn($o) => $o->payment_status === 'paid' || $o->status === 'completed');
        $paidOrdersYesterday = $validOrdersYesterday->filter(fn($o) => $o->payment_status === 'paid' || $o->status === 'completed');

        $grossSalesToday = (int) $validOrdersToday->sum(fn($o) => $o->subtotal + $o->packaging_fee + $o->delivery_fee);
        $discountToday = (int) $validOrdersToday->sum('discount_amount');
        $netSalesToday = (int) $validOrdersToday->sum('total');
        $paidRevenueToday = (int) $paidOrdersToday->sum('total');

        $paidRevenueYesterday = (int) $paidOrdersYesterday->sum('total');
        $netSalesYesterday = (int) $validOrdersYesterday->sum('total');

        $aovToday = $paidOrdersToday->count() > 0 ? (int) round($paidRevenueToday / $paidOrdersToday->count()) : 0;

        // Percentage comparison with yesterday
        $revenueDiff = $paidRevenueToday - $paidRevenueYesterday;
        $revenueGrowth = $paidRevenueYesterday > 0 ? round(($revenueDiff / $paidRevenueYesterday) * 100, 1) : null;

        $ordersCountToday = $ordersToday->count();
        $completedCountToday = $ordersToday->where('status', 'completed')->count();
        $cancelledCountToday = $ordersToday->where('status', 'cancelled')->count();

        // 2. Orders Overview Breakdown (By Actual Status)
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $orderStatusSummary = [
            'pending' => $statusCounts['pending'] ?? 0,
            'confirmed' => $statusCounts['confirmed'] ?? 0,
            'preparing' => $statusCounts['preparing'] ?? 0,
            'ready' => $statusCounts['ready'] ?? 0,
            'completed' => $statusCounts['completed'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
            'total' => array_sum($statusCounts),
        ];

        // 3. Sales Chart Data (Today hourly, 7 days, 30 days)
        $chartData = [
            'today' => $this->getHourlySalesChart($todayStart, $todayEnd),
            'last_7_days' => $this->getDailySalesChart(now()->subDays(6)->startOfDay(), now()->endOfDay()),
            'last_30_days' => $this->getDailySalesChart(now()->subDays(29)->startOfDay(), now()->endOfDay()),
        ];

        // 4. Top Selling Products (Last 30 days)
        $topSellingProducts = OrderItem::select(
                'order_items.item_name',
                'order_items.variant_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.line_total) as total_revenue')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subDays(30)->startOfDay())
            ->groupBy('order_items.item_name', 'order_items.variant_name')
            ->orderByDesc('total_qty')
            ->take(6)
            ->get();

        // Fallback to all-time if last 30 days has no data
        if ($topSellingProducts->isEmpty()) {
            $topSellingProducts = OrderItem::select(
                    'order_items.item_name',
                    'order_items.variant_name',
                    DB::raw('SUM(order_items.quantity) as total_qty'),
                    DB::raw('SUM(order_items.line_total) as total_revenue')
                )
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('order_items.item_name', 'order_items.variant_name')
                ->orderByDesc('total_qty')
                ->take(6)
                ->get();
        }

        // 5. Payment Overview (By Method & Actual Status)
        $paymentsByMethod = Payment::select(
                'method_code',
                DB::raw("COUNT(*) as total_count"),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount"),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count"),
                DB::raw("SUM(CASE WHEN status IN ('pending', 'pending_verification') THEN 1 ELSE 0 END) as pending_count"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count")
            )
            ->groupBy('method_code')
            ->get();

        $paymentSummary = [
            'total_paid_amount' => (int) Payment::where('status', 'paid')->sum('amount'),
            'total_paid_count' => Payment::where('status', 'paid')->count(),
            'total_pending_count' => Payment::whereIn('status', ['pending', 'pending_verification'])->count(),
            'total_rejected_count' => Payment::where('status', 'rejected')->count(),
            'by_method' => $paymentsByMethod,
        ];

        // 6. Promotion Overview
        $promotionSummary = [
            'total_usages' => PromotionUsage::count(),
            'total_discount_given' => (int) PromotionUsage::sum('discount_amount'),
            'active_promos_count' => Promo::active()->count(),
            'top_promo' => PromotionUsage::select('code', DB::raw('count(*) as count'), DB::raw('sum(discount_amount) as total_discount'))
                ->groupBy('code')
                ->orderByDesc('count')
                ->first(),
        ];

        // 7. Inventory Dashboard Summary
        $materials = RawMaterial::all();
        $lowStockMaterials = $materials->filter(fn($m) => (float) $m->current_stock <= (float) $m->minimum_stock);
        $outOfStockMaterials = $materials->filter(fn($m) => (float) $m->current_stock <= 0);

        $inventorySummary = [
            'total_materials' => $materials->count(),
            'low_stock_count' => $lowStockMaterials->count(),
            'out_of_stock_count' => $outOfStockMaterials->count(),
            'low_stock_items' => $lowStockMaterials->take(5),
            'latest_movements' => StockMovement::with(['rawMaterial', 'user'])->latest()->take(5)->get(),
        ];

        // 8. Purchasing Dashboard Summary
        $poCounts = PurchaseOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $purchasingSummary = [
            'draft' => $poCounts['draft'] ?? 0,
            'ordered' => $poCounts['ordered'] ?? 0,
            'partially_received' => $poCounts['partially_received'] ?? 0,
            'received' => $poCounts['received'] ?? 0,
            'cancelled' => $poCounts['cancelled'] ?? 0,
            'total_spend' => (int) PurchaseOrder::where('status', 'received')->sum('total'),
            'latest_receivings' => GoodsReceiving::with(['purchaseOrder.supplier', 'receiver'])->latest()->take(5)->get(),
        ];

        // 9. Customer Overview
        $customerSummary = [
            'total_customers' => Customer::count(),
            'new_this_month' => Customer::where('created_at', '>=', now()->startOfMonth())->count(),
            'repeat_customers' => Customer::has('orders', '>=', 2)->count(),
            'top_customers' => Customer::withCount('orders')
                ->withSum(['orders as total_spent' => function ($q) {
                    $q->where('status', '!=', 'cancelled')
                        ->where(fn($sub) => $sub->where('payment_status', 'paid')->orWhere('status', 'completed'));
                }], 'total')
                ->orderByDesc('total_spent')
                ->take(5)
                ->get(),
        ];

        return [
            'sales_summary' => [
                'gross_sales' => $grossSalesToday,
                'discount' => $discountToday,
                'net_sales' => $netSalesToday,
                'paid_revenue' => $paidRevenueToday,
                'paid_revenue_yesterday' => $paidRevenueYesterday,
                'revenue_growth' => $revenueGrowth,
                'aov' => $aovToday,
                'order_count' => $ordersCountToday,
                'completed_count' => $completedCountToday,
                'cancelled_count' => $cancelledCountToday,
            ],
            'order_status' => $orderStatusSummary,
            'chart_data' => $chartData,
            'top_selling_products' => $topSellingProducts,
            'payment_summary' => $paymentSummary,
            'promotion_summary' => $promotionSummary,
            'inventory_summary' => $inventorySummary,
            'purchasing_summary' => $purchasingSummary,
            'customer_summary' => $customerSummary,
        ];
    }

    /**
     * Hourly sales chart aggregation.
     */
    private function getHourlySalesChart(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get();

        $labels = [];
        $values = [];
        $counts = [];

        for ($h = 9; $h <= 21; $h++) {
            $label = sprintf('%02d:00', $h);
            $labels[] = $label;

            $hourOrders = $orders->filter(function ($order) use ($h) {
                return (int) $order->created_at->format('H') === $h;
            });

            $values[] = (int) $hourOrders->sum('total');
            $counts[] = $hourOrders->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'counts' => $counts,
            'total' => array_sum($values),
        ];
    }

    /**
     * Daily sales chart aggregation.
     */
    private function getDailySalesChart(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->get();

        $labels = [];
        $values = [];
        $counts = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            $labels[] = $current->format('d/m');

            $dayOrders = $orders->filter(function ($order) use ($dateStr) {
                return $order->created_at->toDateString() === $dateStr;
            });

            $values[] = (int) $dayOrders->sum('total');
            $counts[] = $dayOrders->count();

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'counts' => $counts,
            'total' => array_sum($values),
        ];
    }

    /**
     * Sales Report data.
     */
    public function getSalesReport(array $period, int $perPage = 15): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $ordersQuery = Order::whereBetween('created_at', [$start, $end]);

        $allOrders = (clone $ordersQuery)->get();
        $validOrders = $allOrders->where('status', '!=', 'cancelled');
        $completedOrders = $allOrders->where('status', 'completed');
        $cancelledOrders = $allOrders->where('status', 'cancelled');

        $paidOrders = $validOrders->filter(fn($o) => $o->payment_status === 'paid' || $o->status === 'completed');
        $unpaidOrders = $validOrders->filter(fn($o) => $o->payment_status !== 'paid' && $o->status !== 'completed');

        $grossSales = (int) $validOrders->sum(fn($o) => $o->subtotal + $o->packaging_fee + $o->delivery_fee);
        $totalDiscount = (int) $validOrders->sum('discount_amount');
        $netSales = (int) $validOrders->sum('total');
        $totalPaidRevenue = (int) $paidOrders->sum('total');
        $totalUnpaidAmount = (int) $unpaidOrders->sum('total');

        $aov = $paidOrders->count() > 0 ? (int) round($totalPaidRevenue / $paidOrders->count()) : 0;

        $paginatedOrders = (clone $ordersQuery)
            ->withCount('items')
            ->with(['customer', 'latestPayment'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return [
            'summary' => [
                'total_orders' => $allOrders->count(),
                'completed_orders' => $completedOrders->count(),
                'cancelled_orders' => $cancelledOrders->count(),
                'gross_sales' => $grossSales,
                'total_discount' => $totalDiscount,
                'net_sales' => $netSales,
                'paid_revenue' => $totalPaidRevenue,
                'unpaid_amount' => $totalUnpaidAmount,
                'aov' => $aov,
            ],
            'orders' => $paginatedOrders,
            'period' => $period,
        ];
    }

    /**
     * Product Sales Report data.
     */
    public function getProductSalesReport(array $period, int $perPage = 20): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $query = OrderItem::select(
                'order_items.item_name',
                'order_items.variant_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.line_total) as total_revenue'),
                DB::raw('ROUND(AVG(order_items.unit_price)) as avg_price')
            )
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('order_items.item_name', 'order_items.variant_name')
            ->orderByDesc('total_quantity');

        $items = (clone $query)->paginate($perPage)->withQueryString();

        $allAggregated = (clone $query)->get();
        $totalUnits = (int) $allAggregated->sum('total_quantity');
        $totalRevenue = (int) $allAggregated->sum('total_revenue');

        return [
            'items' => $items,
            'summary' => [
                'total_units' => $totalUnits,
                'total_revenue' => $totalRevenue,
                'unique_products_sold' => $allAggregated->count(),
            ],
            'period' => $period,
        ];
    }

    /**
     * Payment Report data.
     */
    public function getPaymentReport(array $period, int $perPage = 20): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $paymentsQuery = Payment::whereBetween('created_at', [$start, $end]);

        $byMethod = (clone $paymentsQuery)->select(
                'method_code',
                DB::raw('COUNT(*) as total_tx'),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount"),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count"),
                DB::raw("SUM(CASE WHEN status IN ('pending', 'pending_verification') THEN 1 ELSE 0 END) as pending_count"),
                DB::raw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count")
            )
            ->groupBy('method_code')
            ->get();

        $totalPaidAmount = (int) (clone $paymentsQuery)->where('status', 'paid')->sum('amount');
        $totalPaidCount = (clone $paymentsQuery)->where('status', 'paid')->count();
        $totalPendingCount = (clone $paymentsQuery)->whereIn('status', ['pending', 'pending_verification'])->count();
        $totalRejectedCount = (clone $paymentsQuery)->where('status', 'rejected')->count();

        $paginatedPayments = (clone $paymentsQuery)
            ->with(['order.customer'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return [
            'by_method' => $byMethod,
            'summary' => [
                'total_paid_amount' => $totalPaidAmount,
                'total_paid_count' => $totalPaidCount,
                'total_pending_count' => $totalPendingCount,
                'total_rejected_count' => $totalRejectedCount,
            ],
            'payments' => $paginatedPayments,
            'period' => $period,
        ];
    }

    /**
     * Promotion Report data.
     */
    public function getPromotionReport(array $period, int $perPage = 20): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $usagesQuery = PromotionUsage::whereBetween('created_at', [$start, $end]);

        $promoBreakdown = (clone $usagesQuery)
            ->select(
                'code',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('COUNT(DISTINCT COALESCE(customer_phone, customer_user_id)) as unique_customers')
            )
            ->groupBy('code')
            ->orderByDesc('usage_count')
            ->get();

        $totalUsages = (clone $usagesQuery)->count();
        $totalDiscount = (int) (clone $usagesQuery)->sum('discount_amount');

        $paginatedUsages = (clone $usagesQuery)
            ->with(['order', 'promo'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return [
            'breakdown' => $promoBreakdown,
            'summary' => [
                'total_usages' => $totalUsages,
                'total_discount' => $totalDiscount,
                'promos_used_count' => $promoBreakdown->count(),
            ],
            'usages' => $paginatedUsages,
            'period' => $period,
        ];
    }

    /**
     * Inventory Report data.
     */
    public function getInventoryReport(array $period, int $perPage = 20): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $movementsQuery = StockMovement::whereBetween('created_at', [$start, $end]);

        $byType = (clone $movementsQuery)
            ->select('movement_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(ABS(quantity_delta)) as total_qty'))
            ->groupBy('movement_type')
            ->pluck('total_qty', 'movement_type')
            ->all();

        $materials = RawMaterial::withCount('stockMovements')
            ->orderBy('name')
            ->get();

        $paginatedMovements = (clone $movementsQuery)
            ->with(['rawMaterial', 'user'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return [
            'materials' => $materials,
            'by_type' => $byType,
            'summary' => [
                'total_materials' => $materials->count(),
                'low_stock_count' => $materials->filter(fn($m) => (float) $m->current_stock <= (float) $m->minimum_stock)->count(),
                'total_movements' => (clone $movementsQuery)->count(),
            ],
            'movements' => $paginatedMovements,
            'period' => $period,
        ];
    }

    /**
     * Purchasing Report data.
     */
    public function getPurchasingReport(array $period, int $perPage = 20): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $poQuery = PurchaseOrder::whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()]);

        $statusCounts = (clone $poQuery)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total) as total_val'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $receivingsQuery = GoodsReceiving::whereBetween('received_at', [$start->toDateString(), $end->toDateString()]);

        $totalReceivings = (clone $receivingsQuery)->count();

        // Calculate received value from goods_receiving_items
        $totalReceivedSpend = (int) GoodsReceivingItem::join('goods_receivings', 'goods_receivings.id', '=', 'goods_receiving_items.goods_receiving_id')
            ->whereBetween('goods_receivings.received_at', [$start->toDateString(), $end->toDateString()])
            ->sum(DB::raw('accepted_quantity * actual_unit_price'));

        $paginatedReceivings = (clone $receivingsQuery)
            ->with(['purchaseOrder.supplier', 'receiver', 'items.purchaseOrderItem'])
            ->latest('received_at')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'po_status' => $statusCounts,
            'summary' => [
                'total_po' => (clone $poQuery)->count(),
                'received_po_count' => $statusCounts->get('received')?->count ?? 0,
                'total_po_value' => (int) (clone $poQuery)->whereIn('status', ['ordered', 'partially_received', 'received'])->sum('total'),
                'total_receivings' => $totalReceivings,
                'total_received_spend' => $totalReceivedSpend,
            ],
            'receivings' => $paginatedReceivings,
            'period' => $period,
        ];
    }

    /**
     * Customer Report data.
     */
    public function getCustomerReport(array $period, int $perPage = 20): array
    {
        $start = $period['start'];
        $end = $period['end'];

        $totalCustomers = Customer::count();
        $newCustomers = Customer::whereBetween('created_at', [$start, $end])->count();
        $repeatCustomers = Customer::has('orders', '>=', 2)->count();

        $topCustomers = Customer::withCount(['orders' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            }])
            ->withSum(['orders as total_spent' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', 'cancelled')
                    ->where(fn($sub) => $sub->where('payment_status', 'paid')->orWhere('status', 'completed'));
            }], 'total')
            ->orderByDesc('total_spent')
            ->paginate($perPage)
            ->withQueryString();

        $totalPaidSpending = (int) Order::whereBetween('created_at', [$start, $end])
            ->whereNotNull('customer_id')
            ->where('status', '!=', 'cancelled')
            ->where(fn($q) => $q->where('payment_status', 'paid')->orWhere('status', 'completed'))
            ->sum('total');

        return [
            'summary' => [
                'total_customers' => $totalCustomers,
                'new_customers_in_period' => $newCustomers,
                'repeat_customers' => $repeatCustomers,
                'customer_spending_in_period' => $totalPaidSpending,
            ],
            'customers' => $topCustomers,
            'period' => $period,
        ];
    }

    /**
     * Export reports to CSV.
     */
    public function exportCsv(string $type, array $period): StreamedResponse
    {
        $filename = 'report_' . $type . '_' . $period['start']->format('Ymd') . '_' . $period['end']->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($type, $period) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            switch ($type) {
                case 'sales':
                    fputcsv($handle, ['Nomor Pesanan', 'Tanggal & Waktu', 'Pelanggan', 'Metode Layanan', 'Subtotal', 'Biaya Antar', 'Biaya Kemasan', 'Diskon', 'Total', 'Status Pesanan', 'Status Pembayaran', 'Metode Bayar']);
                    Order::whereBetween('created_at', [$period['start'], $period['end']])
                        ->orderBy('created_at')
                        ->chunk(200, function ($orders) use ($handle) {
                            foreach ($orders as $order) {
                                fputcsv($handle, [
                                    $order->order_number,
                                    $order->created_at->format('Y-m-d H:i:s'),
                                    $order->customer_name,
                                    $order->service_method,
                                    $order->subtotal,
                                    $order->delivery_fee,
                                    $order->packaging_fee,
                                    $order->discount_amount,
                                    $order->total,
                                    $order->status,
                                    $order->payment_status ?? 'unpaid',
                                    $order->payment_method ?? '-',
                                ]);
                            }
                        });
                    break;

                case 'products':
                    fputcsv($handle, ['Nama Menu', 'Varian', 'Jumlah Terjual (Unit)', 'Rata-rata Harga Satuan', 'Total Penjualan (Rp)']);
                    OrderItem::select(
                            'order_items.item_name',
                            'order_items.variant_name',
                            DB::raw('SUM(order_items.quantity) as total_quantity'),
                            DB::raw('ROUND(AVG(order_items.unit_price)) as avg_price'),
                            DB::raw('SUM(order_items.line_total) as total_revenue')
                        )
                        ->join('orders', 'orders.id', '=', 'order_items.order_id')
                        ->where('orders.status', '!=', 'cancelled')
                        ->whereBetween('orders.created_at', [$period['start'], $period['end']])
                        ->groupBy('order_items.item_name', 'order_items.variant_name')
                        ->orderByDesc('total_quantity')
                        ->chunk(200, function ($rows) use ($handle) {
                            foreach ($rows as $row) {
                                fputcsv($handle, [
                                    $row->item_name,
                                    $row->variant_name ?: '-',
                                    $row->total_quantity,
                                    $row->avg_price,
                                    $row->total_revenue,
                                ]);
                            }
                        });
                    break;

                case 'payments':
                    fputcsv($handle, ['Referensi Pembayaran', 'Nomor Order', 'Metode', 'Nominal', 'Status', 'Tanggal Bayar', 'Waktu']);
                    Payment::with('order')
                        ->whereBetween('created_at', [$period['start'], $period['end']])
                        ->orderBy('created_at')
                        ->chunk(200, function ($payments) use ($handle) {
                            foreach ($payments as $p) {
                                fputcsv($handle, [
                                    $p->payment_reference,
                                    $p->order?->order_number ?: '-',
                                    $p->method_code,
                                    $p->amount,
                                    $p->status,
                                    $p->paid_at ? $p->paid_at->format('Y-m-d H:i:s') : '-',
                                    $p->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }
                        });
                    break;

                case 'inventory':
                    fputcsv($handle, ['Waktu Pergerakan', 'Bahan Baku', 'Tipe Pergerakan', 'Perubahan Jumlah', 'Satuan', 'Saldo Setelah', 'Alasan / Referensi', 'Dicatat Oleh']);
                    StockMovement::with(['rawMaterial', 'user'])
                        ->whereBetween('created_at', [$period['start'], $period['end']])
                        ->orderBy('created_at')
                        ->chunk(200, function ($movements) use ($handle) {
                            foreach ($movements as $m) {
                                fputcsv($handle, [
                                    $m->created_at->format('Y-m-d H:i:s'),
                                    $m->rawMaterial?->name ?: '-',
                                    $m->movement_type,
                                    $m->quantity_delta,
                                    $m->unit,
                                    $m->balance_after,
                                    $m->reason ?: ($m->reference_type ? class_basename($m->reference_type) . ' #' . $m->reference_id : '-'),
                                    $m->user?->name ?: 'Sistem',
                                ]);
                            }
                        });
                    break;

                case 'purchasing':
                    fputcsv($handle, ['Nomor Penerimaan', 'Nomor PO', 'Supplier', 'Tanggal Diterima', 'Penerima', 'Item Diterima (Qty)', 'Catatan']);
                    GoodsReceiving::with(['purchaseOrder.supplier', 'receiver', 'items.purchaseOrderItem'])
                        ->whereBetween('received_at', [$period['start']->toDateString(), $period['end']->toDateString()])
                        ->orderBy('received_at')
                        ->chunk(200, function ($receivings) use ($handle) {
                            foreach ($receivings as $gr) {
                                $itemDetails = $gr->items->map(function ($i) {
                                    return ($i->purchaseOrderItem?->rawMaterial?->name ?: 'Item') . ' (' . $i->accepted_quantity . ' ' . $i->purchase_unit . ')';
                                })->implode('; ');

                                fputcsv($handle, [
                                    $gr->receiving_number,
                                    $gr->purchaseOrder?->po_number ?: '-',
                                    $gr->purchaseOrder?->supplier?->name ?: '-',
                                    $gr->received_at->format('Y-m-d'),
                                    $gr->receiver?->name ?: '-',
                                    $itemDetails,
                                    $gr->notes ?: '-',
                                ]);
                            }
                        });
                    break;

                case 'customers':
                    fputcsv($handle, ['ID', 'Nama Pelanggan', 'Telepon (Masked)', 'Email', 'Status', 'Total Pesanan Periode', 'Total Belanja Berhasil Periode', 'Terdaftar']);
                    Customer::withCount(['orders' => function ($q) use ($period) {
                            $q->whereBetween('created_at', [$period['start'], $period['end']]);
                        }])
                        ->withSum(['orders as total_spent' => function ($q) use ($period) {
                            $q->whereBetween('created_at', [$period['start'], $period['end']])
                                ->where('status', '!=', 'cancelled')
                                ->where(fn($sub) => $sub->where('payment_status', 'paid')->orWhere('status', 'completed'));
                        }], 'total')
                        ->orderByDesc('total_spent')
                        ->chunk(200, function ($customers) use ($handle) {
                            foreach ($customers as $c) {
                                $maskedPhone = strlen($c->phone) > 7
                                    ? substr($c->phone, 0, 4) . '****' . substr($c->phone, -3)
                                    : '****';

                                fputcsv($handle, [
                                    $c->id,
                                    $c->name,
                                    $maskedPhone,
                                    $c->email ?: '-',
                                    $c->status,
                                    $c->orders_count,
                                    (int) $c->total_spent,
                                    $c->created_at->format('Y-m-d'),
                                ]);
                            }
                        });
                    break;
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
