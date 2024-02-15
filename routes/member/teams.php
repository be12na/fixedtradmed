<?php

use Illuminate\Support\Facades\Route;

Route::prefix('team')
    ->name('team.')
    ->group(function () {
        Route::middleware('auth.permit:member.team.index')
            ->group(function () {
                // member.team.index => /member/team/
                Route::get('/', [\App\Http\Controllers\Member\TeamController::class, 'index'])->name('index');
            });
    });

Route::prefix('direct-mitra')
    ->name('directMitra.')
    ->middleware('auth.permit:member.directMitra.index')
    ->group(function () {
        // member.directMitra.index => /member/direct-mitra/
        Route::get('/', [\App\Http\Controllers\Member\TeamController::class, 'mitraIndex'])->name('index');
        // member.directMitra.datatable => /member/direct-mitra/datatable
        Route::get('/datatable', [\App\Http\Controllers\Member\TeamController::class, 'mitraDatatable'])->name('datatable');
        // member.directMitra.select2 => /member/direct-mitra/select2
        Route::get('/select2', [\App\Http\Controllers\Member\TeamController::class, 'mitraSelect2'])->name('select2');

        Route::prefix('histories')
            ->name('histories.')
            ->group(function () {
                // member.directMitra.index => /member/direct-mitra/
                Route::get('/', [\App\Http\Controllers\Member\TeamController::class, 'mitraIndex'])->name('index');
                // member.directMitra.datatable => /member/direct-mitra/datatable
                Route::get('/datatable', [\App\Http\Controllers\Member\TeamController::class, 'mitraDatatable'])->name('datatable');
            });
    });
