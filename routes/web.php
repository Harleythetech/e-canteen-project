<?php

use App\Http\Controllers\PayMongoWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Student routes (authenticated)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'staff' => redirect()->route('staff.dashboard'),
            default => redirect()->route('menu'),
        };
    })->name('dashboard');

    Route::livewire('menu', 'menu-browser')->name('menu');
    Route::livewire('checkout', 'checkout')->name('checkout');
    Route::livewire('orders', 'order-history')->name('orders.index');
    Route::livewire('orders/{order}', 'order-status')->name('orders.show');
    Route::livewire('orders/{order}/confirmed', 'order-confirmed')->name('orders.confirmed');
});

// Staff routes
Route::prefix('staff')->middleware(['auth', 'role:staff,admin'])->group(function () {
    Route::view('login', 'pages.auth.staff-login')->name('staff.login')->withoutMiddleware(['auth', 'role:staff,admin']);
    Route::livewire('/', 'staff.dashboard')->name('staff.dashboard');
});

// Admin routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::livewire('/', 'admin.overview')->name('admin.dashboard');
    Route::livewire('menu', 'admin.menu-management')->name('admin.menu');
    Route::livewire('users', 'admin.user-management')->name('admin.users');
    Route::livewire('reports', 'admin.sales-reports')->name('admin.reports');
});

// PayMongo webhook (outside web middleware — no CSRF, no auth)
Route::post('webhooks/paymongo', PayMongoWebhookController::class)
    ->withoutMiddleware('web')
    ->name('webhooks.paymongo');

require __DIR__ . '/settings.php';
