<?php

use Illuminate\Support\Facades\Route;

Route::prefix('sale')
    ->name('sale.')
    ->middleware('auth.permit:member.sale.index')
    ->group(function () {
        // member.sale.index => /member/sale/
        Route::get('/', [\App\Http\Controllers\Member\SaleController::class, 'index'])->name('index');
        // member.sale.detail => /member/sale/detail/{branchSale}
        Route::get('/detail/{branchSale}', [\App\Http\Controllers\Member\SaleController::class, 'detail'])->name('detail');
        // member.sale.datatable => /member/sale/datatable
        Route::get('/datatable', [\App\Http\Controllers\Member\SaleController::class, 'datatable'])->name('datatable');
        // member.sale.crew => /member/team/crew
        Route::get('/crew', [\App\Http\Controllers\Member\SaleController::class, 'salesCrew'])->name('crew');
        // member.sale.createItem => /member/sale/create-item
        Route::get('/create-item', [\App\Http\Controllers\Member\SaleController::class, 'createItem'])->name('createItem');

        Route::middleware('auth.permit:member.sale.create')
            ->group(function () {
                // member.sale.create => /member/sale/create
                Route::get('/create', [\App\Http\Controllers\Member\SaleController::class, 'create'])->name('create');
                // member.sale.store => /member/sale/store
                Route::post('/store', [\App\Http\Controllers\Member\SaleController::class, 'store'])->name('store');
            });

        Route::middleware('auth.permit:member.sale.edit')
            ->group(function () {
                // member.sales.edit => /member/sale/edit/{branchSaleModifiable}
                Route::get('/edit/{branchSaleModifiable}', [\App\Http\Controllers\Member\SaleController::class, 'edit'])->name('edit');
                // member.sales.update => /member/sale/update/{branchSaleModifiable}
                Route::post('/update/{branchSaleModifiable}', [\App\Http\Controllers\Member\SaleController::class, 'update'])->name('update');
            });

        Route::middleware('auth.permit:member.sale.delete')
            ->group(function () {
                // member.sales.delete => /member/sale/delete/{branchSaleModifiable}
                Route::get('/delete/{branchSaleModifiable}', [\App\Http\Controllers\Member\SaleController::class, 'delete'])->name('delete');
                // member.sales.destroy => /member/sale/destroy/{branchSaleModifiable}
                Route::post('/destroy/{branchSaleModifiable}', [\App\Http\Controllers\Member\SaleController::class, 'destroy'])->name('destroy');
            });
    });
