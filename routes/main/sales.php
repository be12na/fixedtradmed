<?php

use Illuminate\Support\Facades\Route;

Route::prefix('sales')
    ->name('sales.')
    ->middleware('auth.permit:main.sales.index')
    ->group(function () {
        // main.sales.index => main/sales
        Route::get('/', [\App\Http\Controllers\Main\SalesController::class, 'index'])->name('index');
        // main.sales.detail => main/sales/detail/{branchSale}
        Route::get('/detail/{branchSale}', [\App\Http\Controllers\Main\SalesController::class, 'detail'])->name('detail');
        // main.sales.datatable => /main/sales/datatable
        Route::get('/datatable', [\App\Http\Controllers\Main\SalesController::class, 'datatable'])->name('datatable');
        // download
        Route::prefix('download')
            ->name('download.')
            ->group(function () {
                // main.sales.download.excel => /main/sales/download/excel
                Route::get('/excel', [\App\Http\Controllers\Main\SalesController::class, 'downloadExcel'])->name('excel');
            });
        // main.sales.crew => /main/sales/crew
        Route::get('/crew', [\App\Http\Controllers\Main\SalesController::class, 'salesCrew'])->name('crew');
        // main.sales.createItem => /main/sales/create-item
        Route::get('/create-item', [\App\Http\Controllers\Main\SalesController::class, 'createItem'])->name('createItem');

        Route::middleware('auth.permit:main.sales.edit')
            ->group(function () {
                // main.sales.edit => /main/sales/edit/{branchSaleModifiable}
                Route::get('/edit/{branchSaleModifiable}', [\App\Http\Controllers\Main\SalesController::class, 'edit'])->name('edit');
                // main.sales.update => /main/sales/update/{branchSaleModifiable}
                Route::post('/update/{branchSaleModifiable}', [\App\Http\Controllers\Main\SalesController::class, 'update'])->name('update');
            });

        Route::middleware('auth.permit:main.sales.delete')
            ->group(function () {
                // main.sales.delete => /main/sales/delete/{branchSaleModifiable}
                Route::get('/delete/{branchSaleModifiable}', [\App\Http\Controllers\Main\SalesController::class, 'delete'])->name('delete');
                // main.sales.destroy => /main/sales/destroy/{branchSaleModifiable}
                Route::post('/destroy/{branchSaleModifiable}', [\App\Http\Controllers\Main\SalesController::class, 'destroy'])->name('destroy');
            });
    });
