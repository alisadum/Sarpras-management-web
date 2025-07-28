<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\KategoriBarangController;

Route::get('/', fn() => view('landing'));

// Route untuk Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'loginPage'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('profil', [AdminAuthController::class, 'profil'])->name('profil');
        Route::get('profil/edit', [AdminAuthController::class, 'editProfil'])->name('profil.edit');
        Route::put('profil/update', [AdminAuthController::class, 'updateProfil'])->name('profil.update');

        Route::prefix('manajemen-user')->name('manajemen-user.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::post('store', [UserController::class, 'store'])->name('store');
            Route::get('{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('{id}/update', [UserController::class, 'update'])->name('update');
            Route::post('{id}/destroy', [UserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('barang')->name('barang.')->group(function () {
            Route::get('/', [BarangController::class, 'index'])->name('index');
            Route::get('create', [BarangController::class, 'create'])->name('create');
            Route::post('store', [BarangController::class, 'store'])->name('store');
            Route::get('{id}/edit', [BarangController::class, 'edit'])->name('edit');
            Route::put('{id}/update', [BarangController::class, 'update'])->name('update');
            Route::post('{id}/destroy', [BarangController::class, 'destroy'])->name('destroy');
            Route::get('export/pdf', [BarangController::class, 'exportPdf'])->name('export.pdf');
            Route::get('export/excel', [BarangController::class, 'exportExcel'])->name('export.excel');

            Route::prefix('{barang}/units')->name('units.')->group(function () {
                Route::get('/', [ItemController::class, 'index'])->name('index');
                Route::get('create', [ItemController::class, 'create'])->name('create');
                Route::post('/', [ItemController::class, 'store'])->name('store');
                Route::get('{unit}/edit', [ItemController::class, 'edit'])->name('edit');
                Route::put('{unit}', [ItemController::class, 'update'])->name('update');
                Route::delete('{unit}', [ItemController::class, 'destroy'])->name('destroy');
            });
        });

        Route::prefix('borrows')->name('borrows.')->group(function () {
            Route::get('/', [BorrowController::class, 'index'])->name('index');
            Route::get('create', [BorrowController::class, 'create'])->name('create');
            Route::post('/', [BorrowController::class, 'store'])->name('store');
            Route::get('{id}/edit', [BorrowController::class, 'edit'])->name('edit');
            Route::get('{id}/assign', [BorrowController::class, 'assign'])->name('assign');
            Route::post('{id}/assign', [BorrowController::class, 'assignUnit'])->name('assign.unit');
            Route::post('{id}/reject', [BorrowController::class, 'reject'])->name('reject');
            Route::post('{id}/cancel-expired', [BorrowController::class, 'cancelExpired'])->name('cancelExpired');
            Route::delete('{id}', [BorrowController::class, 'destroy'])->name('destroy');
            Route::get('notifications', [BorrowController::class, 'showNotifications'])->name('notifications');
        });

        Route::prefix('return')->name('return.')->group(function () {
            Route::get('/', [ReturnController::class, 'index'])->name('index');
            Route::post('{id}/approve-or-reject', [ReturnController::class, 'approveOrReject'])->name('approveOrReject');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('borrows', [ReportController::class, 'borrowReport'])->name('borrows');
            Route::get('returns', [ReportController::class, 'returnReport'])->name('returns');
        });
    });
});

// Route untuk User (non-admin)
Route::resource('kategori', KategoriBarangController::class);
Route::resource('barang', BarangController::class);
Route::get('barang/export/pdf', [BarangController::class, 'exportPdf'])->name('barang.export.pdf');
Route::get('barang/export/excel', [BarangController::class, 'exportExcel'])->name('barang.export.excel');

Route::prefix('barang/{barang}')->name('items.')->group(function () {
    Route::get('units', [ItemController::class, 'index'])->name('index');
    Route::get('units/create', [ItemController::class, 'create'])->name('create');
    Route::post('units', [ItemController::class, 'store'])->name('store');
    Route::get('units/{unit}/edit', [ItemController::class, 'edit'])->name('edit');
    Route::put('units/{unit}', [ItemController::class, 'update'])->name('update');
    Route::delete('units/{unit}', [ItemController::class, 'destroy'])->name('destroy');
});
