<?php

use Illuminate\Support\Facades\Route;

Route::prefix('purchase')
    ->name('purchase.')
    ->middleware('auth.permit:mitra.purchase.index')
    ->group(function () {
        // mitra.purchase.index => /mitra/purchase/
        Route::get('/', [\App\Http\Controllers\Mitra\PurchaseController::class, 'index'])->name('index');
        // mitra.purchase.detail => /mitra/purchase/detail/{mitraPurchase}
        Route::get('/detail/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'detail'])->name('detail');
        // mitra.purchase.datatable => /mitra/purchase/datatable
        Route::get('/datatable', [\App\Http\Controllers\Mitra\PurchaseController::class, 'datatable'])->name('datatable');

        Route::prefix('transfer')
            ->name('transfer.')
            ->group(function () {
                // mitra.purchase.transfer.index => /mitra/purchase/transfer/{mitraPurchase}
                Route::get('/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'transfer'])->name('index');
                // mitra.purchase.transfer.saveTransfer => /mitra/purchase/transfer/save/{mitraPurchase}
                Route::post('/save/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'saveTransfer'])->name('saveTransfer');
            });

        Route::middleware('auth.permit:mitra.purchase.create,mitra.purchase.edit')
            ->group(function () {
                // mitra.purchase.createItem => /mitra/purchase/create-item
                Route::get('/create-item', [\App\Http\Controllers\Mitra\PurchaseController::class, 'createItem'])->name('createItem');
                // mitra.purchase.productQty => /mitra/purchase/create-item
                Route::get('/product-qty', [\App\Http\Controllers\Mitra\PurchaseController::class, 'productQty'])->name('productQty');
            });

        Route::middleware('auth.permit:mitra.purchase.create')
            ->group(function () {
                // mitra.purchase.create => /mitra/purchase/create
                Route::get('/create', [\App\Http\Controllers\Mitra\PurchaseController::class, 'create'])->name('create');
                // mitra.purchase.store => /mitra/purchase/store
                Route::post('/store', [\App\Http\Controllers\Mitra\PurchaseController::class, 'store'])->name('store');
            });

        Route::middleware('auth.permit:mitra.purchase.edit')
            ->group(function () {
                // mitra.purchase.edit => /mitra/purchase/edit/{mitraPurchase}
                Route::get('/edit/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'edit'])->name('edit');
                // mitra.purchase.update => /mitra/purchase/update/{mitraPurchase}
                Route::post('/update/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'update'])->name('update');
            });

        Route::middleware('auth.permit:mitra.purchase.delete')
            ->group(function () {
                // mitra.purchase.delete => /mitra/purchase/delete/{mitraPurchase}
                Route::get('/delete/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'remove'])->name('delete');
                // mitra.purchase.destroy => /mitra/purchase/destroy/{mitraPurchase}
                Route::post('/destroy/{mitraPurchase}', [\App\Http\Controllers\Mitra\PurchaseController::class, 'destroy'])->name('destroy');
            });
    });
