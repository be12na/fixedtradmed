<?php

use Illuminate\Support\Facades\Route;

Route::prefix('transfers')
    ->name('transfers.')
    ->group(function () {
        // Route::prefix('sales')
        //     ->name('sales.')
        //     ->middleware('auth.permit:main.transfers.sales.index')
        //     ->group(function () {
        //         // main.transfers.sales.index => main/transfers/sales
        //         Route::get('/', [App\Http\Controllers\Main\TransferController::class, 'index'])->name('index');
        //         // main.transfers.sales.datatable => main/transfers/sales/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\TransferController::class, 'datatable'])->name('datatable');
        //         // download
        //         Route::prefix('download')
        //             ->name('download.')
        //             ->group(function () {
        //                 // main.transfers.sales.download.excel => /main/transfer/sales/download/excel
        //                 Route::get('/excel', [\App\Http\Controllers\Main\TransferController::class, 'downloadExcel'])->name('excel');
        //             });
        //         // main.transfers.sales.detail => /main/transfers/sales/detail/{branchTransfer}
        //         Route::get('/detail/{branchTransfer}', [\App\Http\Controllers\Main\TransferController::class, 'detail'])->name('detail');

        //         Route::post('/action/{branchTransfer}', [\App\Http\Controllers\Main\TransferController::class, 'actionTransfer'])
        //             ->middleware('auth.permit:main.transfers.sales.action')
        //             ->name('action');
        //     });

        Route::prefix('member')
            ->name('mitra.')
            ->middleware('auth.permit:main.transfers.mitra.index')
            ->group(function () {
                // main.transfers.mitra.index => main/transfers/mitra
                Route::get('/', [App\Http\Controllers\Main\TransferMitraController::class, 'index'])->name('index');
                // main.transfers.mitra.datatable => main/transfers/mitra/datatable
                Route::get('/datatable', [App\Http\Controllers\Main\TransferMitraController::class, 'datatable'])->name('datatable');
                // download
                Route::prefix('download')
                    ->name('download.')
                    ->group(function () {
                        // main.transfers.mitra.download.excel => /main/transfer/mitra/download/excel
                        Route::get('/excel', [\App\Http\Controllers\Main\TransferMitraController::class, 'downloadExcel'])->name('excel');
                    });
                // main.transfers.mitra.detail => /main/transfers/mitra/detail/{mitraPurchase}
                Route::get('/detail/{mitraPurchase}', [\App\Http\Controllers\Main\TransferMitraController::class, 'detail'])->name('detail');

                Route::post('/action/{mitraPurchase}', [\App\Http\Controllers\Main\TransferMitraController::class, 'actionTransfer'])
                    ->middleware('auth.permit:main.transfers.mitra.action')
                    ->name('action');
            });
    });
