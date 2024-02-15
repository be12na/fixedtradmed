<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bonus-member')
    ->name('memberBonus.')
    ->group(function () {
        // // bonus royalty
        // Route::prefix('royalty')
        //     ->name('royalty.')
        //     ->middleware('auth.permit:main.bonus.royalty.index')
        //     ->group(function () {
        //         // main.bonus.royalty.index => main/bonus/royalty
        //         Route::get('/', [App\Http\Controllers\Main\BonusController::class, 'index'])->name('index');
        //         // main.bonus.royalty.datatable => main/bonus/royalty/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\BonusController::class, 'dataTable'])->name('datatable');
        //         // main.bonus.royalty.total => main/bonus/royalty/total
        //         Route::get('/total', [App\Http\Controllers\Main\BonusController::class, 'totalBonus'])->name('total');
        //         // download
        //         Route::prefix('download')
        //             ->name('download.')
        //             ->group(function () {
        //                 // main.bonus.royalty.download.excel => main/bonus/royalty/download/excel
        //                 Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //             });
        //     });

        // // bonus override
        // Route::prefix('override')
        //     ->name('override.')
        //     ->middleware('auth.permit:main.bonus.override.index')
        //     ->group(function () {
        //         // main.bonus.override.index => main/bonus/override
        //         Route::get('/', [App\Http\Controllers\Main\BonusController::class, 'index'])->name('index');
        //         // main.bonus.override.datatable => main/bonus/override/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\BonusController::class, 'dataTable'])->name('datatable');
        //         // main.bonus.override.total => main/bonus/override/total
        //         Route::get('/total', [App\Http\Controllers\Main\BonusController::class, 'totalBonus'])->name('total');
        //         // download
        //         Route::prefix('download')
        //             ->name('download.')
        //             ->group(function () {
        //                 // main.bonus.override.download.excel => main/bonus/override/download/excel
        //                 Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //             });
        //     });

        // // bonus team
        // Route::prefix('team')
        //     ->name('team.')
        //     ->middleware('auth.permit:main.bonus.team.index')
        //     ->group(function () {
        //         // main.bonus.team.index => main/bonus/team
        //         Route::get('/', [App\Http\Controllers\Main\BonusController::class, 'index'])->name('index');
        //         // main.bonus.team.datatable => main/bonus/team/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\BonusController::class, 'dataTable'])->name('datatable');
        //         // main.bonus.team.total => main/bonus/team/total
        //         Route::get('/total', [App\Http\Controllers\Main\BonusController::class, 'totalBonus'])->name('total');
        //         // download
        //         Route::prefix('download')
        //             ->name('download.')
        //             ->group(function () {
        //                 // main.bonus.team.download.excel => main/bonus/team/download/excel
        //                 Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //             });
        //     });

        // // bonus sale
        // Route::prefix('sale')
        //     ->name('sale.')
        //     ->middleware('auth.permit:main.bonus.sale.index')
        //     ->group(function () {
        //         // main.bonus.sale.index => main/bonus/sale
        //         Route::get('/', [App\Http\Controllers\Main\BonusController::class, 'index'])->name('index');
        //         // main.bonus.sale.datatable => main/bonus/sale/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\BonusController::class, 'dataTable'])->name('datatable');
        //         // main.bonus.sale.total => main/bonus/sale/total
        //         Route::get('/total', [App\Http\Controllers\Main\BonusController::class, 'totalBonus'])->name('total');
        //         // download
        //         Route::prefix('download')
        //             ->name('download.')
        //             ->group(function () {
        //                 // main.bonus.sale.download.excel => main/bonus/sale/download/excel
        //                 Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //             });
        //     });

        // // bonus summary
        // Route::prefix('summary')
        //     ->name('summary.')
        //     ->middleware('auth.permit:main.bonus.summary.index')
        //     ->group(function () {
        //         // main.bonus.summary.index => main/bonus/summary
        //         Route::get('/', function () {
        //             return redirect()->route('main.bonus.summary.indexSummary', ['summaryName' => 'total']);
        //         })->name('index');

        //         // main.bonus.summary.indexSummary => main/bonus/summary/{summaryName}
        //         Route::get('/{summaryName}', [App\Http\Controllers\Main\BonusController::class, 'indexSummary'])->name('indexSummary');
        //         // main.bonus.summary.datatable => main/bonus/summary/{summaryName}/datatable
        //         Route::get('/{summaryName}/datatable', [App\Http\Controllers\Main\BonusController::class, 'datatableSummary'])->name('datatableSummary');
        //         // main.bonus.summary.download => main/bonus/summary/{summaryName}/download/{exportFormat}/download/{exportFormat}
        //         Route::get('/{summaryName}/download/{exportFormat}', [App\Http\Controllers\Main\BonusController::class, 'downloadSummary'])->name('downloadSummary');
        //     });

        // // bonus mitra sponsor
        // Route::prefix('sponsor')
        //     ->name('sponsor.')
        //     ->middleware('auth.permit:main.memberBonus.sponsor.index')
        //     ->group(function () {
        //         // main.memberBonus.sponsor.index => main/bonus-member/sponsor
        //         Route::get('/', [App\Http\Controllers\Main\UserBonusController::class, 'indexBonusSponsor'])->name('index');
        //         // main.memberBonus.sponsor.datatable => main/bonus-member/sponsor/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserBonusController::class, 'dataTableBonusSponsor'])->name('datatable');
        //         // main.memberBonus.sponsor.total => main/bonus-member/sponsor/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserBonusController::class, 'totalBonusSponsor'])->name('total');
        //         // download
        //         // Route::prefix('download')
        //         //     ->name('download.')
        //         //     ->group(function () {
        //         //         // main.bonus.sponsor.download.excel => main/bonus/sponsor/download/excel
        //         //         Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //         //     });
        //     });

        // // bonus mitra sponsor RO
        // Route::prefix('sponsor-ro')
        //     ->name('sponsor-ro.')
        //     ->middleware('auth.permit:main.memberBonus.sponsor-ro.index')
        //     ->group(function () {
        //         // main.memberBonus.sponsor-ro.index => main/bonus-member/sponsor-ro
        //         Route::get('/', [App\Http\Controllers\Main\UserBonusController::class, 'indexBonusSponsorRO'])->name('index');
        //         // main.memberBonus.sponsor-ro.datatable => main/bonus-member/sponsor-ro/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserBonusController::class, 'dataTableBonusSponsorRO'])->name('datatable');
        //         // main.memberBonus.sponsor-ro.total => main/bonus-member/sponsor-ro/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserBonusController::class, 'totalBonusSponsorRO'])->name('total');
        //         // download
        //         // Route::prefix('download')
        //         //     ->name('download.')
        //         //     ->group(function () {
        //         //         // main.bonus.sponsor-ro.download.excel => main/bonus/sponsor-ro/download/excel
        //         //         Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //         //     });
        //     });

        // // bonus mitra cashback
        // Route::prefix('cashback')
        //     ->name('cashback.')
        //     ->middleware('auth.permit:main.memberBonus.cashback.index')
        //     ->group(function () {
        //         // main.memberBonus.cashback.index => main/bonus-member/cashback
        //         Route::get('/', [App\Http\Controllers\Main\UserBonusController::class, 'indexBonusCashback'])->name('index');
        //         // main.memberBonus.cashback.datatable => main/bonus-member/cashback/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserBonusController::class, 'dataTableBonusCashback'])->name('datatable');
        //         // main.memberBonus.cashback.total => main/bonus-member/cashback/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserBonusController::class, 'totalBonusCashback'])->name('total');
        //         // download
        //         // Route::prefix('download')
        //         //     ->name('download.')
        //         //     ->group(function () {
        //         //         // main.bonus.cashback.download.excel => main/bonus/cashback/download/excel
        //         //         Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //         //     });
        //     });

        // // bonus mitra point ro
        // Route::prefix('point-ro')
        //     ->name('point-ro.')
        //     ->middleware('auth.permit:main.memberBonus.point-ro.index')
        //     ->group(function () {
        //         // main.memberBonus.point-ro.index => main/bonus-member/point-ro
        //         Route::get('/', [App\Http\Controllers\Main\UserBonusController::class, 'indexBonusPointRO'])->name('index');
        //         // main.memberBonus.point-ro.datatable => main/bonus-member/point-ro/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserBonusController::class, 'datatableBonusPointRO'])->name('datatable');
        //         // main.memberBonus.point-ro.total => main/bonus-member/point-ro/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserBonusController::class, 'totalBonusPointRO'])->name('total');
        //         // download
        //         // Route::prefix('download')
        //         //     ->name('download.')
        //         //     ->group(function () {
        //         //         // main.bonus.point-ro.download.excel => main/bonus/point-ro/download/excel
        //         //         Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //         //     });
        //     });

        // bonus mitra generasi
        // Route::prefix('generasi')
        //     ->name('generasi.')
        //     ->middleware('auth.permit:main.memberBonus.generasi.index')
        //     ->group(function () {
        //         // main.memberBonus.generasi.index => main/bonus-member/generasi
        //         Route::get('/', [App\Http\Controllers\Main\UserBonusController::class, 'indexBonusGenerasi'])->name('index');
        //         // main.memberBonus.generasi.datatable => main/bonus-member/generasi/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\UserBonusController::class, 'dataTableBonusGenerasi'])->name('datatable');
        //         // main.memberBonus.generasi.total => main/bonus-member/generasi/total
        //         Route::get('/total', [App\Http\Controllers\Main\UserBonusController::class, 'totalBonusGenerasi'])->name('total');
        //         // download
        //         // Route::prefix('download')
        //         //     ->name('download.')
        //         //     ->group(function () {
        //         //         // main.bonus.generasi.download.excel => main/bonus/generasi/download/excel
        //         //         Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
        //         //     });
        //     });

        // bonus mitra prestasi
        Route::prefix('prestasi')
            ->name('prestasi.')
            ->middleware('auth.permit:main.memberBonus.prestasi.index')
            ->group(function () {
                // main.memberBonus.prestasi.index => main/bonus-member/prestasi
                Route::get('/', [App\Http\Controllers\Main\UserBonusController::class, 'indexBonusPrestasi'])->name('index');
                // main.memberBonus.prestasi.datatable => main/bonus-member/prestasi/datatable
                Route::get('/datatable', [App\Http\Controllers\Main\UserBonusController::class, 'dataTableBonusPrestasi'])->name('datatable');
                // main.memberBonus.prestasi.total => main/bonus-member/prestasi/total
                Route::get('/total', [App\Http\Controllers\Main\UserBonusController::class, 'totalBonusPrestasi'])->name('total');
                // download
                // Route::prefix('download')
                //     ->name('download.')
                //     ->group(function () {
                //         // main.bonus.prestasi.download.excel => main/bonus/prestasi/download/excel
                //         Route::get('/excel', [App\Http\Controllers\Main\BonusController::class, 'downloadExcel'])->name('excel');
                //     });
            });
    });
