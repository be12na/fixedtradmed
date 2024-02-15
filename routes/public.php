<?php

use Illuminate\Support\Facades\Route;

Route::middleware('guest')
    ->group(function () {
        // toko mitra
        Route::get('/toko/{mitraStore}', [App\Http\Controllers\Mitra\Market\PublicController::class, 'index'])->name('mitraStore.index');
        // // referral mitra basic. ini ke mitra premium
        // Route::prefix('reg-mitra/{mitraPremium}')
        //     ->name('regMitraBasic.')
        //     ->group(function () {
        //         Route::get('/', [App\Http\Controllers\RegisterMitraController::class, 'create'])->name('create');
        //         Route::post('/', [App\Http\Controllers\RegisterMitraController::class, 'store'])->name('store');
        //     });

        // // referral mitra premium. ini ke perusahaan
        // Route::prefix('reg-mitra-premium')
        //     ->name('regMitraPremium.')
        //     ->group(function () {
        //         Route::get('/', [App\Http\Controllers\RegisterMitraController::class, 'create'])->name('create');
        //         Route::post('/', [App\Http\Controllers\RegisterMitraController::class, 'store'])->name('store');
        //     });

        Route::prefix('register/{mitraReferral?}')
            ->name('regMitra.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\RegisterMitraController::class, 'create'])->name('create');
                Route::post('/', [App\Http\Controllers\RegisterMitraController::class, 'store'])->name('store');
            });

        // find.reseller => /reseller
        Route::prefix('reseller')
            ->name('reseller.')
            ->group(function () {
                Route::get('/', [App\Http\Controllers\ResellerController::class, 'index'])->name('index');
                Route::get('/data', [App\Http\Controllers\ResellerController::class, 'dataTable'])->name('dataTable');
            });
    });
