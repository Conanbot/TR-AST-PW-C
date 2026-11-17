<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
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
    });
});

/*
|--------------------------------------------------------------------------
| API Endpoints (PRD Section 4 Interface Contract)
|--------------------------------------------------------------------------
*/
Route::get('/api/menu', [ApiController::class, 'menu'])->name('api.menu');
Route::get('/api/settings', [ApiController::class, 'settings'])->name('api.settings');

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
        Route::get('/cashiers', [CashierController::class, 'index'])->name('cashiers.index');
        Route::post('/cashiers', [CashierController::class, 'store'])->name('cashiers.store');

        // Stock quick toggles
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::patch('/items/{id}/availability', [StockController::class, 'toggleMenuItem'])->name('items.availability');
        Route::patch('/cuts/{id}/availability', [StockController::class, 'toggleChickenCut'])->name('cuts.availability');
        Route::patch('/drinks/{id}/availability', [StockController::class, 'toggleDrinkOption'])->name('drinks.availability');

        // Menu CRUD
        Route::resource('/items', MenuItemController::class);

        // Promos CRUD
        Route::resource('/promos', PromoController::class);

        // Store Settings
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Change Password (FR-25)
        Route::get('/password', [AuthController::class, 'showChangePassword'])->name('password.edit');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
    });
});
