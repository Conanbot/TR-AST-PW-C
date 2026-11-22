<?php

use App\Http\Controllers\Admin\AuthController;

use App\Http\Controllers\Admin\CashierController;

use App\Http\Controllers\Admin\CustomerController;

use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\Admin\MenuItemController;

use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\Admin\PromoController;

use App\Http\Controllers\Admin\ProductConfigurationController;

use App\Http\Controllers\Admin\SettingController;

use App\Http\Controllers\Admin\StockController;

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

Route::get('/menu', [MenuController::class, 'index'])->middleware('auth')->name('menu');

Route::get('/kontak', [ContactController::class, 'index'])->name('kontak');

Route::post('/orders', [OrderController::class, 'store'])->middleware(['auth', 'throttle:10,1'])->name('orders.store');

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

    Route::middleware(['auth', 'role:customer'])->group(function () {

        Route::get('/', [AccessController::class, 'dashboardCustomer'])->name('dashboard');

        Route::put('/profil', [AccessController::class, 'updateCustomerProfile'])->name('profile.update');

        Route::get('/riwayat', [AccessController::class, 'ordersCustomer'])->name('orders');

        Route::get('/riwayat/{order}/tracking', [AccessController::class, 'trackCustomerOrder'])->name('orders.track');

    });

});

/*

|--------------------------------------------------------------------------

| Cashier Routes

|--------------------------------------------------------------------------

*/

Route::prefix('kasir')->name('cashier.')->group(function () {

    Route::get('/login', [AccessController::class, 'showCashierLogin'])->name('login');

    Route::post('/login', [AccessController::class, 'loginCashier'])->middleware('throttle:6,1')->name('login.submit');

    Route::post('/logout', [AccessController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role.any:admin,cashier'])->group(function () {

        Route::get('/', [CashierDashboardController::class, 'index'])->name('dashboard');

        Route::post('/orders', [CashierDashboardController::class, 'storeOrder'])->name('orders.store');

        Route::patch('/orders/{order}/status', [CashierDashboardController::class, 'updateStatus'])->name('orders.status');

        // Payment management — handled by cashier

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');

        Route::patch('/payments/{payment}/confirm-cash', [AdminPaymentController::class, 'confirmCash'])->name('payments.confirm-cash');

        Route::patch('/payments/{payment}/verify-qris', [AdminPaymentController::class, 'verifyQris'])->name('payments.verify-qris');

        Route::get('/payments/{payment}/proof', [AdminPaymentController::class, 'proof'])->name('payments.proof');

        Route::get('/payment-settings', [AdminPaymentController::class, 'settings'])->name('payments.settings');

        Route::put('/payment-settings', [AdminPaymentController::class, 'updateSettings'])->name('payments.settings.update');

    });

});

/*

|--------------------------------------------------------------------------

| API Endpoints

|--------------------------------------------------------------------------

*/

Route::get('/api/menu', [ApiController::class, 'menu'])->name('api.menu');

Route::get('/api/settings', [ApiController::class, 'settings'])->name('api.settings');

Route::post('/api/promotions/validate', [PromotionCheckoutController::class, 'check'])->name('api.promotions.validate');

/*

|--------------------------------------------------------------------------

| Admin Routes

|--------------------------------------------------------------------------

*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.submit');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:admin'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::patch('/store/toggle', [DashboardController::class, 'toggleStore'])->name('store.toggle');

        Route::patch('/orders/{order}/kitchen-status', [DashboardController::class, 'updateKitchenStatus'])->name('orders.kitchen-status');

        Route::post('/orders/{order}/staff-note', [DashboardController::class, 'updateStaffNote'])->name('orders.staff-note');

        // User Management

        Route::get('/cashiers', [CashierController::class, 'index'])->name('cashiers.index');

        Route::post('/cashiers', [CashierController::class, 'store'])->name('cashiers.store');

        Route::put('/cashiers/{cashier}/password', [CashierController::class, 'resetPassword'])->name('cashiers.password');

        Route::delete('/cashiers/{cashier}', [CashierController::class, 'destroy'])->name('cashiers.destroy');

        // Stock quick toggles

        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');

        Route::patch('/items/{id}/availability', [StockController::class, 'toggleMenuItem'])->name('items.availability');

        Route::patch('/cuts/{id}/availability', [StockController::class, 'toggleChickenCut'])->name('cuts.availability');

        Route::patch('/drinks/{id}/availability', [StockController::class, 'toggleDrinkOption'])->name('drinks.availability');

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

        // Customer Management

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

        Route::patch('/customers/{customer}/toggle', [CustomerController::class, 'toggleStatus'])->name('customers.toggle');

        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Store Settings

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');

        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::patch('/settings/toggle-delivery', [SettingController::class, 'toggleDelivery'])->name('settings.toggle-delivery');

        // Change Password

        Route::get('/password', [AuthController::class, 'showChangePassword'])->name('password.edit');

        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');

        Route::prefix('reports')->name('reports.')->group(function () {

            Route::get('/', [ReportController::class, 'index'])->name('index');

            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');

            Route::get('/products', [ReportController::class, 'products'])->name('products');

            Route::get('/payments', [ReportController::class, 'payments'])->name('payments');

            Route::get('/promotions', [ReportController::class, 'promotions'])->name('promotions');

            Route::get('/customers', [ReportController::class, 'customers'])->name('customers');

        });

    });

});

/*

|--------------------------------------------------------------------------

| Shared Routes — Admin, Cashier, Kitchen

|--------------------------------------------------------------------------

*/

Route::middleware(['auth', 'role.any:admin,cashier,kitchen'])->group(function () {

    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderManagementController::class, 'index'])->name('admin.orders.index');

    Route::get('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderManagementController::class, 'show'])->name('admin.orders.show');

    Route::patch('/admin/orders/{order}/transition', [\App\Http\Controllers\Admin\OrderManagementController::class, 'transition'])->name('admin.orders.transition');

    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');

    Route::get('/kitchen/feed', [KitchenController::class, 'feed'])->name('kitchen.feed');

    Route::post('/kitchen/orders/{order}/status', [KitchenController::class, 'transition'])->name('kitchen.orders.status');

});

