<?php

use Illuminate\Support\Facades\Route;

Route::prefix('branches')
    ->name('branch.')
    ->group(function () {
        Route::prefix('list')
            ->name('list.')
            ->middleware('auth.permit:main.branch.list.index')
            ->group(function () {
                // main.branch.list.index => /main/branch/list
                Route::get('/', [App\Http\Controllers\Main\BranchController::class, 'index'])->name('index');

                Route::middleware('auth.permit:main.branch.list.create')
                    ->group(function () {
                        // main.branch.list.create => /main/branch/list/create
                        Route::get('/create', [App\Http\Controllers\Main\BranchController::class, 'create'])->name('create');
                        // main.branch.list.store => /main/branch/list/store
                        Route::post('/store', [App\Http\Controllers\Main\BranchController::class, 'store'])->name('store');
                    });
                Route::middleware('auth.permit:main.branch.list.edit')
                    ->group(function () {
                        // main.branch.list.edit => /main/branch/list/edit/{branch}
                        Route::get('/edit/{branch}', [App\Http\Controllers\Main\BranchController::class, 'edit'])->name('edit');
                        // main.branch.list.update => /main/branch/list/update/{branch}
                        Route::post('/update{branch}', [App\Http\Controllers\Main\BranchController::class, 'update'])->name('update');
                    });
            });

        Route::prefix('products')
            ->name('product.')
            ->middleware('auth.permit:main.branch.product.index')
            ->group(function () {
                // main.branch.product.index => /main/branch/products
                Route::get('/', [App\Http\Controllers\Main\BranchProductController::class, 'index'])->name('index');
                // main.branch.product.datatable => /main/branch/products/datatable
                Route::get('/datatable', [App\Http\Controllers\Main\BranchProductController::class, 'datatable'])->name('datatable');
                // download
                Route::prefix('download')
                    ->name('download.')
                    ->group(function () {
                        // main.branch.product.download.excel => /main/branch/products/download/excel
                        Route::get('/excel', [\App\Http\Controllers\Main\BranchProductController::class, 'downloadExcel'])->name('excel');
                    });

                Route::middleware('auth.permit:main.branch.product.stock')
                    ->group(function () {
                        // main.branch.product.stock => /main/branch/products/create/{branch}/{product}/{stockType}
                        Route::get('/stock/{branch}/{product}', [App\Http\Controllers\Main\BranchProductController::class, 'editStock'])->name('stock');
                        // main.branch.product.update-stock => /main/branch/products/update-stock/{branch}/{product}/{stockType}
                        Route::post('/update-stock/{branch}/{product}', [App\Http\Controllers\Main\BranchProductController::class, 'updateStock'])->name('update-stock');
                    });
            });
    });
