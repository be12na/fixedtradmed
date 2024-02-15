<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bank')
    ->name('bank.')
    ->middleware('auth.permit:mitra.bank.index')
    ->group(function () {
        // main.settings.bank.index => main/settings/bank
        Route::get('/', [App\Http\Controllers\Mitra\BankController::class, 'index'])->name('index');

        // main.settings.bank.create => main/settings/bank/create
        Route::get('/create', [App\Http\Controllers\Mitra\BankController::class, 'create'])->name('create');
        // main.settings.bank.store => main/settings/bank/store
        Route::post('/store', [App\Http\Controllers\Mitra\BankController::class, 'store'])->name('store');

        // main.settings.bank.edit => main/settings/bank/edit/{mainBank}
        Route::get('/edit/{memberBank}', [App\Http\Controllers\Mitra\BankController::class, 'edit'])->name('edit');
        // main.settings.bank.update => main/settings/bank/update/{mainBank}
        Route::post('/update/{memberBank}', [App\Http\Controllers\Mitra\BankController::class, 'update'])->name('update');
    });
