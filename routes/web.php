<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\ProductConfigurationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\PromotionCheckoutController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/profil', [ProfileController::class, 'index'])->name('profil');
Route::get('/menu', [MenuController::class, 'index'])->name('menu');
Route::get('/kontak', [ContactController::class, 'index'])->name('kontak');
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('orders.store');
Route::get('/orders/{orderNumber}/track/{token}', [OrderTrackingController::class, 'show'])
    ->middleware('throttle:30,1')
    ->name('orders.track');
Route::post('/orders/{orderNumber}/track/{token}/payment-proof', [PaymentProofController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('orders.payment-proof');

Route::get('/login', [AccessController::class, 'showCustomerLogin'])->name('login');
Route::get('/register', [AccessController::class, 'showCustomerRegister'])->name('register');

Route::prefix('akun')->name('account.')->group(function () {
    Route::get('/login', [AccessController::class, 'showCustomerLogin'])->name('login');
    Route::post('/login', [AccessController::class, 'loginCustomer'])->middleware('throttle:6,1')->name('login.submit');
    Route::get('/daftar', [AccessController::class, 'showCustomerRegister'])->name('register');
    Route::post('/daftar', [AccessController::class, 'registerCustomer'])->middleware('throttle:6,1')->name('register.submit');
    Route::post('/logout', [AccessController::class, 'logout'])->name('logout');
    Route::get('/', [AccessController::class, 'dashboardCustomer'])->middleware(['auth', 'role:customer'])->name('dashboard');
});

Route::prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/login', [AccessController::class, 'showCashierLogin'])->name('login');
    Route::post('/login', [AccessController::class, 'loginCashier'])->middleware('throttle:6,1')->name('login.submit');
    Route::post('/logout', [AccessController::class, 'logout'])->name('logout');
    Route::middleware(['auth', 'role:cashier'])->group(function () {
        Route::get('/', [CashierDashboardController::class, 'index'])->name('dashboard');
        Route::post('/orders', [CashierDashboardController::class, 'storeOrder'])->name('orders.store');
        Route::patch('/orders/{order}/status', [CashierDashboardController::class, 'updateStatus'])->name('orders.status');
        Route::patch('/payments/{payment}/confirm-cash', [AdminPaymentController::class, 'confirmCash'])->name('payments.confirm-cash');
    });
});

/*
|--------------------------------------------------------------------------
| API Endpoints (PRD Section 4 Interface Contract)
|--------------------------------------------------------------------------
*/
Route::get('/api/menu', [ApiController::class, 'menu'])->name('api.menu');
Route::get('/api/settings', [ApiController::class, 'settings'])->name('api.settings');
Route::post('/api/promotions/validate', [PromotionCheckoutController::class, 'check'])->name('api.promotions.validate');

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Admin Protected Routes (Middleware Auth)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::patch('/store/toggle', [DashboardController::class, 'toggleStore'])->name('store.toggle');
        Route::patch('/orders/{order}/kitchen-status', [DashboardController::class, 'updateKitchenStatus'])->name('orders.kitchen-status');
        Route::post('/orders/{order}/staff-note', [DashboardController::class, 'updateStaffNote'])->name('orders.staff-note');
        Route::get('/cashiers', [CashierController::class, 'index'])->name('cashiers.index');
        Route::post('/cashiers', [CashierController::class, 'store'])->name('cashiers.store');

        // Stock quick toggles
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::patch('/items/{id}/availability', [StockController::class, 'toggleMenuItem'])->name('items.availability');
        Route::patch('/cuts/{id}/availability', [StockController::class, 'toggleChickenCut'])->name('cuts.availability');
        Route::patch('/drinks/{id}/availability', [StockController::class, 'toggleDrinkOption'])->name('drinks.availability');

        // Inventory foundation: materials, auditable movements, and recipes.
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/materials', [InventoryController::class, 'storeMaterial'])->name('inventory.materials.store');
        Route::put('/inventory/materials/{rawMaterial}', [InventoryController::class, 'updateMaterial'])->name('inventory.materials.update');
        Route::post('/inventory/movements', [InventoryController::class, 'recordMovement'])->name('inventory.movements.store');
        Route::get('/inventory/recipes', [InventoryController::class, 'recipes'])->name('inventory.recipes');
        Route::post('/inventory/recipes', [InventoryController::class, 'storeRecipe'])->name('inventory.recipes.store');
        Route::post('/inventory/modifier-recipes', [InventoryController::class, 'storeModifierRecipe'])->name('inventory.modifier-recipes.store');
        Route::delete('/inventory/recipes/{type}/{id}', [InventoryController::class, 'destroyRecipe'])->name('inventory.recipes.destroy');

        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::patch('/suppliers/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('suppliers.toggle');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        Route::get('/purchasing', [PurchaseOrderController::class, 'index'])->name('purchasing.index');
        Route::get('/purchasing/create', [PurchaseOrderController::class, 'create'])->name('purchasing.create');
        Route::post('/purchasing', [PurchaseOrderController::class, 'store'])->name('purchasing.store');
        Route::get('/purchasing/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchasing.edit');
        Route::put('/purchasing/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('purchasing.update');
        Route::get('/purchasing/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchasing.show');
        Route::patch('/purchasing/{purchaseOrder}/ordered', [PurchaseOrderController::class, 'markOrdered'])->name('purchasing.ordered');
        Route::patch('/purchasing/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchasing.cancel');
        Route::post('/purchasing/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchasing.receive');

        // Menu CRUD
        Route::resource('/items', MenuItemController::class);
        Route::get('/product-configuration', [ProductConfigurationController::class, 'index'])->name('product-config.index');
        Route::post('/modifier-groups', [ProductConfigurationController::class, 'storeGroup'])->name('modifier-groups.store');
        Route::put('/modifier-groups/{group}', [ProductConfigurationController::class, 'updateGroup'])->name('modifier-groups.update');
        Route::delete('/modifier-groups/{group}', [ProductConfigurationController::class, 'destroyGroup'])->name('modifier-groups.destroy');
        Route::post('/modifier-groups/{group}/options', [ProductConfigurationController::class, 'storeOption'])->name('modifier-options.store');
        Route::put('/modifier-options/{option}', [ProductConfigurationController::class, 'updateOption'])->name('modifier-options.update');
        Route::delete('/modifier-options/{option}', [ProductConfigurationController::class, 'destroyOption'])->name('modifier-options.destroy');
        Route::post('/variants', [ProductConfigurationController::class, 'storeVariant'])->name('variants.store');
        Route::put('/variants/{variant}', [ProductConfigurationController::class, 'updateVariant'])->name('variants.update');
        Route::delete('/variants/{variant}', [ProductConfigurationController::class, 'destroyVariant'])->name('variants.destroy');
        Route::put('/items/{item}/modifier-groups', [ProductConfigurationController::class, 'assignGroups'])->name('items.modifier-groups');

        // Promos CRUD
        Route::resource('/promos', PromoController::class);
        Route::patch('/promos/{promo}/toggle', [PromoController::class, 'toggle'])->name('promos.toggle');

        // Store Settings
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Change Password (FR-25)
        Route::get('/password', [AuthController::class, 'showChangePassword'])->name('password.edit');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::patch('/payments/{payment}/confirm-cash', [AdminPaymentController::class, 'confirmCash'])->name('payments.confirm-cash');
        Route::patch('/payments/{payment}/verify-qris', [AdminPaymentController::class, 'verifyQris'])->name('payments.verify-qris');
        Route::get('/payments/{payment}/proof', [AdminPaymentController::class, 'proof'])->name('payments.proof');
        Route::get('/payment-settings', [AdminPaymentController::class, 'settings'])->name('payments.settings');
        Route::put('/payment-settings', [AdminPaymentController::class, 'updateSettings'])->name('payments.settings.update');
    });
});

Route::middleware(['auth', 'role.any:admin,cashier,kitchen'])->group(function () {
    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderManagementController::class, 'index'])->name('admin.orders.index');
    Route::get('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderManagementController::class, 'show'])->name('admin.orders.show');
    Route::patch('/admin/orders/{order}/transition', [\App\Http\Controllers\Admin\OrderManagementController::class, 'transition'])->name('admin.orders.transition');
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('/kitchen/feed', [KitchenController::class, 'feed'])->name('kitchen.feed');
    Route::post('/kitchen/orders/{order}/status', [KitchenController::class, 'transition'])->name('kitchen.orders.status');
});
