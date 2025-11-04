<?php

use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
    Route::post('/refresh', [App\Http\Controllers\Auth\AuthController::class, 'refresh']);
});

// Protected routes
Route::middleware(['auth:api'])->group(function () {
    // Auth
    Route::post('/auth/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
    Route::get('/me', [App\Http\Controllers\Auth\AuthController::class, 'me']);
    
    // Properties
    Route::get('/properties', [App\Http\Controllers\PropertyController::class, 'index']);
    Route::get('/properties/{id}', [App\Http\Controllers\PropertyController::class, 'show']);
    Route::get('/properties/{id}/reviews', [App\Http\Controllers\ReviewController::class, 'index']);
    
    // Bookings
    Route::get('/bookings', [App\Http\Controllers\BookingController::class, 'index']);
    Route::post('/bookings', [App\Http\Controllers\BookingController::class, 'store'])
        ->middleware([App\Http\Middleware\IdempotencyMiddleware::class]);
    
    // Owner bookings
    Route::post('/owner/bookings/{id}/decision', [App\Http\Controllers\BookingController::class, 'ownerDecision']);
    
    // Payments
    Route::post('/payments/mock', [App\Http\Controllers\PaymentController::class, 'mock'])
        ->middleware([App\Http\Middleware\IdempotencyMiddleware::class]);
    
    // Proposals
    Route::post('/proposals', [App\Http\Controllers\ProposalController::class, 'store']);
    Route::get('/owner/proposals', [App\Http\Controllers\ProposalController::class, 'ownerIndex']);
    
    // Admin
    Route::middleware('admin')->group(function () {
        Route::get('/admin/proposals', [App\Http\Controllers\Admin\ProposalController::class, 'index']);
        Route::post('/admin/proposals/{id}/review', [App\Http\Controllers\Admin\ProposalController::class, 'review'])
            ->middleware([App\Http\Middleware\IdempotencyMiddleware::class]);

        // Admin - Users management
        Route::get('/admin/users', [App\Http\Controllers\Admin\UserController::class, 'index']);
        Route::patch('/admin/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'update']);
        Route::delete('/admin/users/{id}', [App\Http\Controllers\Admin\UserController::class, 'destroy']);
    });
    
    // Reviews
    Route::post('/properties/{id}/reviews', [App\Http\Controllers\ReviewController::class, 'store']);
    
    // FCM Tokens
    Route::post('/me/fcm-tokens', [App\Http\Controllers\FcmTokenController::class, 'store']);
    Route::delete('/me/fcm-tokens/{token}', [App\Http\Controllers\FcmTokenController::class, 'destroy']);
});

