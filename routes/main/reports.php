<?php

use Illuminate\Support\Facades\Route;

Route::prefix('reports')
    ->name('reports.')
    ->group(function () {
        Route::prefix('global')
            ->name('global.')
            ->middleware('auth.permit:main.reports.global.index')
            ->group(function () {
                // main.reports.global.index => /main/reports/global/product
                Route::get('/', [\App\Http\Controllers\Main\ReportsController::class, 'indexGlobal'])->name('index');
                // global product
                Route::prefix('product')
                    ->name('product.')
                    ->middleware('auth.permit:main.reports.global.product.index')
                    ->group(function () {
                        // main.reports.global.product.index => /main/reports/global/product
                        Route::get('/', [\App\Http\Controllers\Main\ReportsController::class, 'indexReportGlobalProduct'])->name('index');
                        // main.reports.global.product.datatable => /main/reports/global/product/datatable
                        Route::get('/datatable', [\App\Http\Controllers\Main\ReportsController::class, 'dataTableReportGlobalProduct'])->name('datatable');
                        // download
                        // main.reports.global.product.excel => /main/reports/global/product/excel
                        Route::get('/excel', [\App\Http\Controllers\Main\ReportsController::class, 'excelReportGlobalProduct'])->name('excel');
                    });
                // global manager
                Route::prefix('manager')
                    ->name('manager.')
                    ->middleware('auth.permit:main.reports.global.manager.index')
                    ->group(function () {
                        // main.reports.global.manager.index => /main/reports/global/manager
                        Route::get('/', [\App\Http\Controllers\Main\ReportsController::class, 'indexReportGlobalManager'])->name('index');
                        // main.reports.global.manager.datatable => /main/reports/global/manager/datatable
                        Route::get('/datatable', [\App\Http\Controllers\Main\ReportsController::class, 'dataTableReportGlobalManager'])->name('datatable');
                        // download
                        // main.reports.global.manager.download => /main/reports/global/manager/{exportFormat}
                        Route::get('/{exportFormat}', [\App\Http\Controllers\Main\ReportsController::class, 'downloadReportGlobalManager'])->name('download');
                    });

                // global manager
                Route::prefix('detail-manager')
                    ->name('detailManager.')
                    ->middleware('auth.permit:main.reports.global.detailManager.index')
                    ->group(function () {
                        // main.reports.global.detailManager.index => /main/reports/global/detail-manager
                        Route::get('/', [
                            \App\Http\Controllers\Main\ReportsController::class, 'indexReportGlobalDetailManager'
                        ])->name('index');
                        // main.reports.global.detailManager.datatable => /main/reports/global/detail-manager/datatable
                        Route::get('/datatable', [\App\Http\Controllers\Main\ReportsController::class, 'dataTableReportGlobalDetailManager'])->name('datatable');
                        // download
                        // main.reports.global.detailManager.download => /main/reports/global/detail-manager/{exportFormat}
                        Route::get('/{exportFormat}', [\App\Http\Controllers\Main\ReportsController::class, 'downloadReportGlobalDetailManager'])->name('download');
                    });
            });

        Route::prefix('bonus')
            ->name('bonus.')
            ->middleware('auth.permit:main.reports.bonus.index')
            ->group(function () {
                // main.reports.bonus.index => /main/reports/global/product
                Route::get('/', [\App\Http\Controllers\Main\ReportsController::class, 'indexBonus'])->name('index');
                // global manager
                Route::prefix('distributor')
                    ->name('distributor.')
                    ->middleware('auth.permit:main.reports.bonus.distributor.index')
                    ->group(function () {
                        // main.reports.bonus.distributor.index => /main/reports/bonus/distributor
                        Route::get('/', [\App\Http\Controllers\Main\ReportsController::class, 'indexReportBonusDistributor'])->name('index');
                        // main.reports.bonus.distributor.datatable => /main/reports/bonus/distributor/datatable
                        Route::get('/datatable', [\App\Http\Controllers\Main\ReportsController::class, 'dataTableReportBonusDistributor'])->name('datatable');
                        // download
                        // main.reports.bonus.distributor.download => /main/reports/bonus/distributor/{exportFormat}
                        Route::get('/{exportFormat}', [\App\Http\Controllers\Main\ReportsController::class, 'downloadReportBonusDistributor'])->name('download');
                    });
            });
    });
