<?php

use Illuminate\Support\Facades\Route;

Route::prefix('transfer')
    ->name('transfer.')
    ->middleware('auth.permit:member.transfer.index')
    ->group(function () {
        // member.transfer.index => member/transfer
        Route::get('/', [App\Http\Controllers\Member\TransferController::class, 'index'])->name('index');
        // member.transfer.datatable => member/transfer/datatable
        Route::get('/datatable', [App\Http\Controllers\Member\TransferController::class, 'datatable'])->name('datatable');
        // member.transfer.detail => /member/transfer/detail/{branchTransfer}
        Route::get('/detail/{branchTransfer}', [\App\Http\Controllers\Member\TransferController::class, 'detail'])->name('detail');

        Route::middleware('auth.permit:member.transfer.create')
            ->group(function () {
                // member.transfer.create => member/transfer/create
                Route::get('/create', [App\Http\Controllers\Member\TransferController::class, 'create'])->name('create');
                // member.transfer.datatable => member/transfer/datatable
                Route::post('/store', [App\Http\Controllers\Member\TransferController::class, 'store'])->name('store');
                // member.transfer.inputSummary => member/transfer/input-summary
                Route::get('/input-summary', [App\Http\Controllers\Member\TransferController::class, 'inputSummary'])->name('inputSummary');
            });
    });
