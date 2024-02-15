<?php

use Illuminate\Support\Facades\Route;

Route::prefix('member')
    ->name('mitra.')
    ->middleware('auth.permit:main.mitra.index')
    ->group(function () {
        // main.mitra.index => /main/mitra
        Route::get('/', [App\Http\Controllers\Main\MitraController::class, 'index'])->name('index');
        // main.mitra.datatable => /main/mitra/datatable
        Route::get('/datatable', [App\Http\Controllers\Main\MitraController::class, 'datatable'])->name('datatable');
        // download
        Route::prefix('download')
            ->name('download.')
            ->group(function () {
                // main.mitra.download.excel => /main/mitra/download/excel
                Route::get('/excel', [\App\Http\Controllers\Main\MitraController::class, 'downloadExcel'])->name('excel');
            });
        // main.mitra.detail => /main/mitra/detail/{userMitra}
        Route::get('/detail/{userMitra}', [App\Http\Controllers\Main\MitraController::class, 'detail'])->name('detail');

        Route::middleware('auth.permit:main.mitra.edit')
            ->group(function () {
                // main.mitra.edit => /main/mitra/edit/{userMitra}
                Route::post('/edit/{userMitra}', [App\Http\Controllers\Main\MitraController::class, 'edit'])->name('edit');
                // main.mitra.update => /main/mitra/update/{userMitra}
                Route::post('/update/{userMitra}', [App\Http\Controllers\Main\MitraController::class, 'update'])->name('update');
            });

        // // aktifasi registered mitra
        // Route::prefix('register')
        //     ->name('register.')
        //     ->middleware('auth.permit:main.mitra.register.index')
        //     ->group(function () {
        //         // main.mitra.register.index => /main/mitra/register
        //         Route::get('/', [App\Http\Controllers\Main\MitraController::class, 'indexRegisterMitra'])->name('index');
        //         // main.mitra.register.datatable => /main/mitra/register/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\MitraController::class, 'registerDatatableMitra'])->name('datatable');
        //         // main.mitra.register.detail.index => /main/mitra/register/detail/{registerMitra}
        //         Route::get('/detail/{registerMitra}', [App\Http\Controllers\Main\MitraController::class, 'detailRegisterMitra'])->name('detail');
        //         // main.mitra.register.action => /main/mitra/register/action/{registerMitra}
        //         Route::post('/action/{registerMitra}', [App\Http\Controllers\Main\MitraController::class, 'actionRegisterMitra'])
        //             ->middleware('auth.permit:main.mitra.register.action')
        //             ->name('action');
        //     });
    });
