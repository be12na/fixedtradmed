<?php

use Illuminate\Support\Facades\Route;

Route::prefix('my-member')
    ->name('myMember.')
    ->middleware('auth.permit:mitra.myMember.index')
    ->group(function () {
        // mitra.myMember.index => /mitra/my-member/
        Route::get('/', [\App\Http\Controllers\Mitra\MemberController::class, 'index'])->name('index');
        // mitra.myMember.datatable => /mitra/my-member/datatable
        Route::get('/datatable', [\App\Http\Controllers\Mitra\MemberController::class, 'datatable'])->name('datatable');
        // mitra.myMember.select2 => /mitra/my-member/select2
        Route::get('/select2', [\App\Http\Controllers\Mitra\MemberController::class, 'select2'])->name('select2');

        Route::prefix('histories')
            ->name('histories.')
            ->group(function () {
                // mitra.myMember.histories.index => /mitra/my-member/histories
                Route::get('/', [\App\Http\Controllers\Mitra\MemberController::class, 'index'])->name('index');
                // mitra.myMember.histories.datatable => /mitra/my-member/histories/datatable
                Route::get('/datatable', [\App\Http\Controllers\Mitra\MemberController::class, 'datatable'])->name('datatable');
            });
    });
