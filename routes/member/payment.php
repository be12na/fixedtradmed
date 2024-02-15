<?php

use Illuminate\Support\Facades\Route;

Route::prefix('payment')
    ->name('payment.')
    ->middleware('auth.permit:member.payment.index')
    ->group(function () {
        // member.payment.index => /member/payment/
        Route::get('/', [\App\Http\Controllers\Member\PaymentController::class, 'index'])->name('index');
        // member.payment.detail => /member/payment/detail/{branchPayment}
        Route::get('/detail/{branchPayment}', [\App\Http\Controllers\Member\PaymentController::class, 'detail'])->name('detail');
        // member.payment.datatable => /member/payment/datatable
        Route::get('/datatable', [\App\Http\Controllers\Member\PaymentController::class, 'datatable'])->name('datatable');
        // member.payment.createItem => /member/payment/create-item
        Route::get('/create-item', [\App\Http\Controllers\Member\PaymentController::class, 'createItem'])->name('createItem');

        Route::middleware('auth.permit:member.payment.create')
            ->group(function () {
                // member.payment.create => /member/payment/create
                Route::get('/create', [\App\Http\Controllers\Member\PaymentController::class, 'create'])->name('create');
                // member.payment.create.confirm => /member/payment/create/confirm
                Route::get('/create/confirm', [\App\Http\Controllers\Member\PaymentController::class, 'createConfirm'])->name('createConfirm');
                // member.payment.store => /member/payment/store
                Route::post('/store', [\App\Http\Controllers\Member\PaymentController::class, 'store'])->name('store');
            });

        Route::middleware('auth.permit:member.payment.edit')
            ->group(function () {
                // member.payments.edit => /member/payment/edit/{branchPaymentModifiable}
                Route::get('/edit/{branchPaymentModifiable}', [\App\Http\Controllers\Member\PaymentController::class, 'edit'])->name('edit');
                // member.payments.update => /member/payment/update/{branchPaymentModifiable}
                Route::post('/update/{branchPaymentModifiable}', [\App\Http\Controllers\Member\PaymentController::class, 'update'])->name('update');
            });

        Route::middleware('auth.permit:member.payment.delete')
            ->group(function () {
                // member.payments.delete => /member/payment/delete/{branchPaymentModifiable}
                Route::get(
                    '/delete/{branchPaymentModifiable}',
                    [\App\Http\Controllers\Member\PaymentController::class, 'delete']
                )->name('delete');
                // member.payments.destroy => /member/payment/destroy/{branchPaymentModifiable}
                Route::post('/destroy/{branchPaymentModifiable}', [\App\Http\Controllers\Member\PaymentController::class, 'destroy'])->name('destroy');
            });

        Route::middleware('auth.permit:member.payment.transfer')
            ->group(function () {
                // member.payments.edit => /member/payment/edit/{branchPaymentModifiable}
                Route::get('/transfer/{branchPaymentTransferable}', [\App\Http\Controllers\Member\PaymentController::class, 'transfer'])->name('transfer');
                // member.payments.update => /member/payment/update/{branchPaymentModifiable}
                Route::post('/transfer/{branchPaymentTransferable}', [\App\Http\Controllers\Member\PaymentController::class, 'saveTransfer'])->name('saveTransfer');
            });
    });
