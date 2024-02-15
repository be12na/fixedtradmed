<?php

use Illuminate\Support\Facades\Route;

Route::prefix('withdraw')
    ->name('withdraw.')
    ->group(function () {
        // // withdraw bonus sponsor
        // Route::prefix('sponsor')
        //     ->name('sponsor.')
        //     ->middleware('auth.permit:main.withdraw.sponsor.index')
        //     ->group(function () {
        //         // main.withdraw.sponsor.index => main/withdraw/sponsor
        //         Route::get('/', [App\Http\Controllers\Main\UserWithdrawController::class, 'indexBonusSponsor'])->name('index');
        //         // main.withdraw.sponsor.datatable => main/withdraw/sponsor/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableBonusSponsor'])->name('datatable');
        //         // main.withdraw.sponsor.total => main/withdraw/sponsor/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalBonusSponsor'])->name('total');
        //         // main.withdraw.sponsor.download => main/withdraw/sponsor/download/{fileType}
        //         // Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadFile'])->name('download');
        //     });

        // withdraw bonus sponsor RO
        // Route::prefix('sponsor-ro')
        //     ->name('sponsor-ro.')
        //     ->middleware('auth.permit:main.withdraw.sponsor-ro.index')
        //     ->group(function () {
        //         // main.withdraw.sponsor-ro.index => main/withdraw/sponsor-ro
        //         Route::get('/', [App\Http\Controllers\Main\UserWithdrawController::class, 'indexBonusSponsorRO'])->name('index');
        //         // main.withdraw.sponsor-ro.datatable => main/withdraw/sponsor-ro/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableBonusSponsorRO'])->name('datatable');
        //         // main.withdraw.sponsor-ro.total => main/withdraw/sponsor/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalBonusSponsorRO'])->name('total');
        //         // main.withdraw.sponsor-ro.download => main/withdraw/sponsor-ro/download/{fileType}
        //         // Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadFile'])->name('download');
        //     });

        // // withdraw bonus cashback
        // Route::prefix('cashback')
        //     ->name('cashback.')
        //     ->middleware('auth.permit:main.withdraw.cashback.index')
        //     ->group(function () {
        //         // main.withdraw.cashback.index => main/withdraw/cashback
        //         Route::get('/', [App\Http\Controllers\Main\UserWithdrawController::class, 'indexBonusCashback'])->name('index');
        //         // main.withdraw.cashback.datatable => main/withdraw/cashback/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableBonusCashback'])->name('datatable');
        //         // main.withdraw.cashback.total => main/withdraw/sponsor/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalBonusCashback'])->name('total');
        //         // main.withdraw.cashback.download => main/withdraw/cashback/download/{fileType}
        //         // Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadFile'])->name('download');
        //     });

        // // withdraw bonus point ro
        // Route::prefix('point-ro')
        //     ->name('point-ro.')
        //     ->middleware('auth.permit:main.withdraw.point-ro.index')
        //     ->group(function () {
        //         // main.withdraw.point-ro.index => main/withdraw/point-ro
        //         Route::get('/', [App\Http\Controllers\Main\UserWithdrawController::class, 'indexBonusPointRO'])->name('index');
        //         // main.withdraw.point-ro.datatable => main/withdraw/point-ro/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableBonusPointRO'])->name('datatable');
        //         // main.withdraw.point-ro.total => main/withdraw/point-ro/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalBonusPointRO'])->name('total');
        //         // main.withdraw.point-ro.download => main/withdraw/point-ro/download/{fileType}
        //         // Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadFile'])->name('download');
        //     });

        // // withdraw bonus generasi
        // Route::prefix('generasi')
        //     ->name('generasi.')
        //     ->middleware('auth.permit:main.withdraw.generasi.index')
        //     ->group(function () {
        //         // main.withdraw.generasi.index => main/withdraw/generasi
        //         Route::get('/', [App\Http\Controllers\Main\UserWithdrawController::class, 'indexBonusGenerasi'])->name('index');
        //         // main.withdraw.generasi.datatable => main/withdraw/generasi/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableBonusGenerasi'])->name('datatable');
        //         // main.withdraw.generasi.total => main/withdraw/sponsor/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalBonusGenerasi'])->name('total');
        //         // main.withdraw.generasi.download => main/withdraw/generasi/download/{fileType}
        //         // Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadFile'])->name('download');
        //     });

        // withdraw bonus prestasi
        Route::prefix('prestasi')
            ->name('prestasi.')
            ->middleware('auth.permit:main.withdraw.prestasi.index')
            ->group(function () {
                // main.withdraw.prestasi.index => main/withdraw/prestasi
                Route::get('/', [App\Http\Controllers\Main\UserWithdrawController::class, 'indexBonusPrestasi'])->name('index');
                // main.withdraw.prestasi.datatable => main/withdraw/prestasi/datatable
                Route::get('/datatable', [App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableBonusPrestasi'])->name('datatable');
                // main.withdraw.prestasi.total => main/withdraw/sponsor/total
                Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalBonusPrestasi'])->name('total');
                // main.withdraw.prestasi.download => main/withdraw/prestasi/download/{fileType}
                // Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadFile'])->name('download');
            });

        Route::prefix('histories')
            ->name('histories.')
            ->middleware('auth.permit:main.withdraw.histories.index')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Main\UserWithdrawController::class, 'indexHistories'])->name('index');
                Route::get('/data', [\App\Http\Controllers\Main\UserWithdrawController::class, 'dataTableHistories'])->name('dataTable');
                // main.withdraw.prestasi.total => main/withdraw/sponsor/total
                Route::get('/total', [App\Http\Controllers\Main\UserWithdrawController::class, 'totalHistories'])->name('total');
                // main.withdraw.prestasi.download => main/withdraw/prestasi/download/{fileType}
                Route::get('/download/{fileType}', [App\Http\Controllers\Main\UserWithdrawController::class, 'downloadHistories'])->name('download');
            });

        Route::prefix('transfer')
            ->name('transfer.')
            ->middleware('auth.permit:main.withdraw.transfer.index')
            ->group(function () {
                // main.withdraw.transfer.index => main/withdraw/transfer
                Route::get('/', [\App\Http\Controllers\Main\UserWithdrawController::class, 'transfer'])->name('index');
                // main.withdraw.transfer.submit => main/withdraw/transfer
                Route::post('/', [\App\Http\Controllers\Main\UserWithdrawController::class, 'transfer'])->name('submit');
            });
    });
