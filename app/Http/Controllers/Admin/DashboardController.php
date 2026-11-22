<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ChickenCut;
use App\Models\DrinkOption;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Promo;
use App\Models\StoreSetting;
use App\Services\OrderStatusService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class DashboardController extends Controller
{
    public function index(ReportService $reportService): View
    {
        $settings = StoreSetting::current();
        $operational = $reportService->getDashboardData();
        // 1. Menu Categories & Items (Information Architecture)
        $totalMenu = MenuItem::count();
        $availableMenu = MenuItem::where('is_available', true)->count();
        $outOfStockMenu = MenuItem::where('is_available', false)->count();
        $foodItemsCount = MenuItem::whereHas('category', function ($q) {
            $q->whereIn('slug', ['paket-hemat', 'aneka-ayam', 'sup']);
        })->count();
        $activePromos = Promo::active()->count();
        // Add-Ons reference list (Information Architecture)
        $addOns = [
            ['name' => 'Nasi Daun Jeruk', 'price' => 2000, 'type' => 'Nasi Upgrade'],
            ['name' => 'Telur Goreng (Dadar/Ceplok)', 'price' => 5000, 'type' => 'Lauk Tambahan'],
            ['name' => 'Sambal Bawang Spesial GLO', 'price' => 2000, 'type' => 'Varian Sambal'],
            ['name' => 'Sambal Ijo Segar', 'price' => 2000, 'type' => 'Varian Sambal'],
            ['name' => 'Sambal Bajak Gurih Manis', 'price' => 2000, 'type' => 'Varian Sambal'],
            ['name' => 'Sambal Matah Bali', 'price' => 2000, 'type' => 'Varian Sambal'],
        ];
        // 2. Orders & Sales Overview (Information Architecture)
        $today = now()->toDateString();
        $dailyRevenue = (int) Order::whereDate('created_at', $today)->sum('total');
        if ($dailyRevenue === 0) {
            $dailyRevenue = (int) Order::sum('total'); // fallback if seeded on different date
        }
        $orderVolume = Order::whereDate('created_at', $today)->count() ?: Order::count();
        $peakHours = '11:00 - 14:00 & 17:00 - 20:00 WIB';
        $bestSellers = OrderItem::select('item_name', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('item_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();
        // 3. Kitchen & Cooking Queue (Information Architecture)
        // Queued, Prepared, Ready, Completed
        $queuedOrders = Order::with('items')->where('kitchen_status', 'queued')->latest()->get();
        $preparedOrders = Order::with('items')->where('kitchen_status', 'prepared')->latest()->get();
        $readyOrders = Order::with('items')->where('kitchen_status', 'ready')->latest()->get();
        $completedOrders = Order::with('items')->where('kitchen_status', 'completed')->latest()->take(10)->get();
        $activeOrders = Order::with('items')
            ->whereIn('kitchen_status', ['queued', 'prepared', 'ready'])
            ->latest()
            ->get();
        $recentOrders = Order::with('items')->latest()->take(10)->get();
        // 4. Support / Notes (Information Architecture: Priority Service, Add Cutlery, Birthday Message, Table Change)
        $ordersWithNotes = Order::with('items')
            ->where(function ($q) {
                $q->whereNotNull('order_notes')->where('order_notes', '!=', '')
                  ->orWhereNotNull('staff_notes')->where('staff_notes', '!=', '');
            })
            ->latest()
            ->take(8)
            ->get();
        $chickenCuts = ChickenCut::orderBy('sort_order')->get();
        $drinkOptions = DrinkOption::orderBy('sort_order')->get();
        $outOfStockItems = MenuItem::where('is_available', false)->with('category')->get();
        $cashierCount = \App\Models\User::where('role', 'cashier')->count();
        return view('admin.dashboard', compact(
            'settings',
            'totalMenu',
            'availableMenu',
            'outOfStockMenu',
            'foodItemsCount',
            'activePromos',
            'addOns',
            'dailyRevenue',
            'orderVolume',
            'peakHours',
            'bestSellers',
            'queuedOrders',
            'preparedOrders',
            'readyOrders',
            'completedOrders',
            'activeOrders',
            'recentOrders',
            'ordersWithNotes',
            'chickenCuts',
            'drinkOptions',
            'outOfStockItems',
            'cashierCount',
            'operational'
        ));
    }
    /**
     * One-tap toggle for store Open/Closed status.
     */
    public function toggleStore(Request $request): JsonResponse
    {
        $settings = StoreSetting::current();
        $settings->is_open = !$settings->is_open;
        $settings->save();
        return response()->json([
            'success' => true,
            'is_open' => (bool) $settings->is_open,
            'message' => $settings->is_open
                ? 'Status kedai berhasil diubah menjadi BUKA.'
                : 'Status kedai berhasil diubah menjadi TUTUP.',
        ]);
    }
    /**
     * Update kitchen cooking queue status (Queued -> Prepared -> Ready -> Completed).
     */
    public function updateKitchenStatus(Request $request, Order $order, OrderStatusService $statuses): RedirectResponse|JsonResponse
    {
        $request->validate([
            'kitchen_status' => ['required', 'in:queued,prepared,ready,completed'],
        ]);
        $kitchenStatus = $request->input('kitchen_status');
        $status = match ($kitchenStatus) {
            'prepared' => 'preparing',
            'ready' => 'ready',
            'completed' => 'completed',
            default => null,
        };
        if ($status && $order->status !== $status) {
            $nextStatus = [
                'pending' => 'preparing',
                'confirmed' => 'preparing',
                'preparing' => 'ready',
                'ready' => 'completed',
            ];
            while ($order->status !== $status && isset($nextStatus[$order->status])) {
                $order = $statuses->transition($order, $nextStatus[$order->status], $request->user());
            }
            if ($order->status !== $status) {
                abort(422, 'Status pesanan tidak dapat mengikuti status dapur yang dipilih.');
            }
        }
        $order->kitchen_status = $kitchenStatus;
        $order->save();
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'kitchen_status' => $order->kitchen_status]);
        }
        return back()->with('success', "Status dapur pesanan {$order->order_number} berhasil diperbarui menjadi {$order->kitchen_status}.");
    }
    /**
     * Update staff notes / announcements (Kitchen Delay, Item Unavailable, etc.).
     */
    public function updateStaffNote(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'staff_notes' => ['nullable', 'string', 'max:255'],
        ]);
        $order->staff_notes = $request->input('staff_notes');
        $order->save();
        return back()->with('success', "Catatan staf untuk pesanan {$order->order_number} berhasil disimpan.");
    }
}
