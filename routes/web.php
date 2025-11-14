<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App as LaravelApp;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;

Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Locale switching (public, but typically used in admin panel)
Route::get('/locale/{locale}', function (string $locale) {
    if (!in_array($locale, ['en', 'ar'])) {
        abort(404);
    }
    
    LaravelApp::setLocale($locale);
    session()->put('locale', $locale);
    
    return redirect()->back();
})->name('locale.switch');

// Admin Login (guest only)
Route::middleware(['guest:web', 'throttle:login'])->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
});

// Admin Logout (authenticated only)
Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

// Admin Panel (Blade) - session guard
Route::prefix('admin')
    ->middleware(['web', 'auth', 'admin', 'throttle:60,1'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\AdminPanel\DashboardController::class, 'index'])->name('dashboard');

        // Proposals
        Route::get('/proposals', [App\Http\Controllers\AdminPanel\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/{id}', [App\Http\Controllers\AdminPanel\ProposalController::class, 'show'])->name('proposals.show');
        Route::post('/proposals/{id}/review', [App\Http\Controllers\AdminPanel\ProposalController::class, 'review'])->name('proposals.review');

        // Properties
        Route::get('/properties', [App\Http\Controllers\AdminPanel\PropertyController::class, 'index'])->name('properties.index');
        Route::get('/properties/{id}', [App\Http\Controllers\AdminPanel\PropertyController::class, 'show'])->name('properties.show');
        Route::get('/properties/{id}/edit', [App\Http\Controllers\AdminPanel\PropertyController::class, 'edit'])->name('properties.edit');
        Route::put('/properties/{id}', [App\Http\Controllers\AdminPanel\PropertyController::class, 'update'])->name('properties.update');
        Route::delete('/properties/{id}', [App\Http\Controllers\AdminPanel\PropertyController::class, 'destroy'])->name('properties.destroy');

        // Bookings
        Route::get('/bookings', [App\Http\Controllers\AdminPanel\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{id}', [App\Http\Controllers\AdminPanel\BookingController::class, 'show'])->name('bookings.show');

        // Users
        Route::get('/users', [App\Http\Controllers\AdminPanel\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [App\Http\Controllers\AdminPanel\UserController::class, 'show'])->name('users.show');
        Route::post('/users/{id}/toggle-active', [App\Http\Controllers\AdminPanel\UserController::class, 'toggleActive'])->name('users.toggle');

        // Audit
        Route::get('/audit', [App\Http\Controllers\AdminPanel\AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/{id}', [App\Http\Controllers\AdminPanel\AuditController::class, 'show'])->name('audit.show');

        // Reports
        Route::get('/reports', [App\Http\Controllers\AdminPanel\ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [App\Http\Controllers\AdminPanel\ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/download/{export}', [App\Http\Controllers\AdminPanel\ReportController::class, 'download'])->name('reports.download');

        // Notifications Log (optional)
        Route::get('/notifications', [App\Http\Controllers\AdminPanel\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [App\Http\Controllers\AdminPanel\NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
        Route::post('/notifications/{notification}/read', [App\Http\Controllers\AdminPanel\NotificationController::class, 'markAsRead'])->name('notifications.read');
    });
