<?php

use Illuminate\Support\Facades\Route;

Route::prefix('payments')
    ->name('payments.')
    ->middleware('auth.permit:main.payments.index')
    ->group(function () {
        // main.payment.index => main/payment
        Route::get('/', [App\Http\Controllers\Main\PaymentController::class, 'index'])->name('index');
        // main.payment.datatable => main/payment/datatable
        Route::get('/datatable', [App\Http\Controllers\Main\PaymentController::class, 'datatable'])->name('datatable');
        // download
        Route::prefix('download')
            ->name('download.')
            ->group(function () {
                // main.payment.download.excel => /main/transfer/sales/download/excel
                Route::get('/excel', [\App\Http\Controllers\Main\PaymentController::class, 'downloadExcel'])->name('excel');
            });
        // main.payment.detail => /main/payment/detail/{branchPayment}
        Route::get('/detail/{branchPayment}', [\App\Http\Controllers\Main\PaymentController::class, 'detail'])->name('detail');

        Route::post('/action/{branchPaymentApproving}', [\App\Http\Controllers\Main\PaymentController::class, 'actionTransfer'])
            ->middleware('auth.permit:main.payments.action')
            ->name('action');
    });
