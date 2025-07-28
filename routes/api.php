<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\BorrowController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\KategoriBarangController;

// Rute publik (tanpa autentikasi)
Route::post('/register', [UserController::class, 'apiRegister'])->name('register');
Route::post('/login', [UserController::class, 'login'])->name('login');

// Rute admin dengan prefix 'admin/api' dan guard 'admin-api'
Route::prefix('admin/api')->as('admin.api.')->group(function () {
    // Login admin (tanpa middleware auth)
    Route::post('/login', [AdminAuthController::class, 'apiLogin'])->name('login');

    // Rute yang memerlukan autentikasi admin
    Route::middleware('auth:admin-api')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/user/{id}', [UserController::class, 'getUserById'])->name('users.show');
        Route::apiResource('/kategori-barang', KategoriBarangController::class);
        Route::apiResource('/barang', BarangController::class);
        Route::post('/barang/{barang}/update-photo', [BarangController::class, 'updatePhoto'])->name('barang.update-photo');
        Route::get('/borrows', [BorrowController::class, 'index'])->name('borrows.index');
        Route::post('/borrows', [BorrowController::class, 'store'])->name('borrows.store');
        Route::post('/borrows/{id}/assign', [BorrowController::class, 'assignUnit'])->name('borrows.assign');
        Route::post('/borrows/{id}/reject', [BorrowController::class, 'reject'])->name('borrows.reject');
        Route::post('/borrows/{id}/cancel-expired', [BorrowController::class, 'cancelExpired'])->name('borrows.cancelExpired');
        Route::get('/reports/borrows', [ReportController::class, 'borrowReport'])->name('reports.borrows');
        Route::get('/reports/returns', [ReportController::class, 'returnReport'])->name('reports.returns');
        Route::post('/return/{returnLogId}/approve-or-reject', [ReturnController::class, 'approveOrReject'])->name('return.approveOrReject');
        Route::get('/server-time', [BorrowController::class, 'getServerTime'])->name('server-time');
    });
});

// Rute user dengan prefix 'user/api' dan middleware 'auth:sanctum'
Route::prefix('user/api')->as('user.api.')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/user', [UserController::class, 'getUser'])->name('user.profile');
    Route::post('/user/update', [UserController::class, 'updateProfile'])->name('user.update');
    Route::get('/user/{id}', [UserController::class, 'getUserById'])->name('user.show');
    Route::apiResource('/barang', BarangController::class)->only(['index', 'show']);
    Route::get('/kategori-barang', [KategoriBarangController::class, 'index'])->name('kategori-barang.index');
    Route::get('/borrows', [BorrowController::class, 'index'])->name('borrows.index');
    Route::post('/borrows', [BorrowController::class, 'store'])->name('borrows.store');
    Route::post('/return-barang', [ReturnController::class, 'returnBarang'])->name('return-barang');
    Route::get('/return-barang', [ReturnController::class, 'index'])->name('return-barang.index');
    Route::get('/reports/borrows', [ReportController::class, 'borrowReport'])->name('reports.borrows');
    Route::get('/reports/returns', [ReportController::class, 'returnReport'])->name('reports.returns');
});

// Rute umum yang memerlukan autentikasi sanctum (untuk user dan admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

// Rute dengan middleware tambahan 'check.status.api' untuk peminjaman dan pengembalian
Route::middleware(['auth:sanctum', 'check.status.api'])->group(function () {
    Route::post('/borrows', [BorrowController::class, 'store'])->name('borrows.store');
    Route::post('/returns', [ReturnController::class, 'returnBarang'])->name('returns.store');
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
});