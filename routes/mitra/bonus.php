<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bonus')
    ->name('bonus.')
    ->group(function () {
        // bonus sponsor
        Route::prefix('sponsor')
            ->name('sponsor.')
            ->middleware('auth.permit:mitra.bonus.sponsor.index')
            ->group(function () {
                // mitra.bonus.sponsor.index => {mitra}/bonus/sponsor
                Route::get('/', [App\Http\Controllers\Mitra\UserBonusController::class, 'indexBonusSponsor'])->name('index');
                // mitra.bonus.sponsor.datatable => {mitra}/bonus/sponsor/datatable
                Route::get('/datatable', [App\Http\Controllers\Mitra\UserBonusController::class, 'dataTableBonusSponsor'])->name('datatable');
                // mitra.bonus.sponsor.total => {mitra}/bonus/sponsor/total
                Route::get('/total', [App\Http\Controllers\Mitra\UserBonusController::class, 'totalBonusSponsor'])->name('total');
            });

        // bonus sponsor RO
        // Route::prefix('sponsor-ro')
        //     ->name('sponsor-ro.')
        //     ->middleware('auth.permit:mitra.bonus.sponsor-ro.index')
        //     ->group(function () {
        //         // mitra.bonus.sponsor-ro.index => {mitra}/bonus/sponsor-ro
        //         Route::get('/', [App\Http\Controllers\Mitra\UserBonusController::class, 'indexBonusSponsorRO'])->name('index');
        //         // mitra.bonus.sponsor-ro.datatable => {mitra}/bonus/sponsor-ro/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Mitra\UserBonusController::class, 'dataTableBonusSponsorRO'])->name('datatable');
        //         // mitra.bonus.sponsor-ro.total => {mitra}/bonus/sponsor-ro/total
        //         Route::get('/total', [App\Http\Controllers\Mitra\UserBonusController::class, 'totalBonusSponsorRO'])->name('total');
        //     });

        // bonus cashback
        Route::prefix('cashback')
            ->name('cashback.')
            ->middleware('auth.permit:mitra.bonus.cashback.index')
            ->group(function () {
                // mitra.bonus.cashback.index => {mitra}/bonus/cashback
                Route::get('/', [App\Http\Controllers\Mitra\UserBonusController::class, 'indexBonusCashback'])->name('index');
                // mitra.bonus.cashback.datatable => {mitra}/bonus/cashback/datatable
                Route::get('/datatable', [App\Http\Controllers\Mitra\UserBonusController::class, 'dataTableBonusCashback'])->name('datatable');
                // mitra.bonus.cashback.total => {mitra}/bonus/cashback/total
                Route::get('/total', [App\Http\Controllers\Mitra\UserBonusController::class, 'totalBonusCashback'])->name('total');
            });

        // bonus point ro
        Route::prefix('point-ro')
            ->name('point-ro.')
            ->middleware('auth.permit:mitra.bonus.cashback.index')
            ->group(function () {
                // mitra.bonus.cashback.index => {mitra}/bonus/cashback
                Route::get('/', [App\Http\Controllers\Mitra\UserBonusController::class, 'indexBonusPointRO'])->name('index');
                // mitra.bonus.cashback.datatable => {mitra}/bonus/cashback/datatable
                Route::get('/datatable', [App\Http\Controllers\Mitra\UserBonusController::class, 'dataTableBonusPointRO'])->name('datatable');
                // mitra.bonus.cashback.total => {mitra}/bonus/cashback/total
                Route::get('/total', [App\Http\Controllers\Mitra\UserBonusController::class, 'totalBonusPointRO'])->name('total');
            });

        // bonus generasi
        // Route::prefix('generasi')
        //     ->name('generasi.')
        //     ->middleware('auth.permit:mitra.bonus.generasi.index')
        //     ->group(function () {
        //         // mitra.bonus.generasi.index => {mitra}/bonus/generasi
        //         Route::get('/', [App\Http\Controllers\Mitra\UserBonusController::class, 'indexBonusGenerasi'])->name('index');
        //         // mitra.bonus.generasi.datatable => {mitra}/bonus/generasi/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Mitra\UserBonusController::class, 'dataTableBonusGenerasi'])->name('datatable');
        //         // mitra.bonus.generasi.total => {mitra}/bonus/generasi/total
        //         Route::get('/total', [App\Http\Controllers\Mitra\UserBonusController::class, 'totalBonusGenerasi'])->name('total');
        //     });

        // bonus prestasi
        Route::prefix('prestasi')
            ->name('prestasi.')
            ->middleware('auth.permit:mitra.bonus.prestasi.index')
            ->group(function () {
                // mitra.bonus.generasi.index => {mitra}/bonus/prestasi
                Route::get('/', [App\Http\Controllers\Mitra\UserBonusController::class, 'indexBonusPrestasi'])->name('index');
                // mitra.bonus.prestasi.datatable => {mitra}/bonus/prestasi/datatable
                Route::get('/datatable', [App\Http\Controllers\Mitra\UserBonusController::class, 'dataTableBonusPrestasi'])->name('datatable');
                // mitra.bonus.prestasi.total => {mitra}/bonus/prestasi/total
                Route::get('/total', [App\Http\Controllers\Mitra\UserBonusController::class, 'totalBonusPrestasi'])->name('total');
            });
    });
