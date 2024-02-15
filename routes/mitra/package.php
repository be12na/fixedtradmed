<?php

use Illuminate\Support\Facades\Route;

Route::prefix('package')
    ->name('package.')
    ->group(function () {
        Route::get('/', [App\Http\Controllers\Mitra\PackageController::class, 'activationTransfer'])->name('index');
        Route::post('/transfer', [App\Http\Controllers\Mitra\PackageController::class, 'activationTransfer'])->name('transfer');

        Route::prefix('repeat-order')
            ->name('ro.')
            ->middleware(['auth.package', 'auth.ro'])
            ->group(function () {
                Route::get('/', [App\Http\Controllers\Mitra\PackageController::class, 'repeatOrder'])->name('index');
                Route::post('/store', [App\Http\Controllers\Mitra\PackageController::class, 'repeatOrder'])->name('store');
                Route::get('/transfer/{userPackageRO}', [App\Http\Controllers\Mitra\PackageController::class, 'repeatOrder'])->name('transfer');
                Route::post('/transfer/{userPackageRO}', [App\Http\Controllers\Mitra\PackageController::class, 'repeatOrder'])->name('saveTransfer');
            });

        Route::get('/histories', [App\Http\Controllers\Mitra\PackageController::class, 'history'])->name('history');
    });
