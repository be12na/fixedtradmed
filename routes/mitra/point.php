<?php

use Illuminate\Support\Facades\Route;

Route::prefix('point')
    ->name('point.')
    ->group(function () {
        // point aktifasi member
        Route::prefix('point-aktifasi')
            ->name('activate-member.')
            ->middleware('auth.permit:mitra.point.activate-member.index')
            ->group(function () {
                // mitra.point.activate-member.index => {mitra}/point/point-aktifasi
                Route::get('/', [App\Http\Controllers\Mitra\BonusPointController::class, 'index'])->name('index');
                // mitra.point.activate-member.datatable => {mitra}/point/point-aktifasi/datatable
                Route::get('/datatable', [App\Http\Controllers\Mitra\BonusPointController::class, 'dataTable'])->name('datatable');
                // mitra.point.activate-member.total => {mitra}/point/point-aktifasi/total
                Route::get('/total', [App\Http\Controllers\Mitra\BonusPointController::class, 'totalBonus'])->name('total');
            });

        // point belanja pribadi
        Route::prefix('belanja-pribadi')
            ->name('my-shopping.')
            ->middleware('auth.permit:mitra.point.my-shopping.index')
            ->group(function () {
                // mitra.point.my-shopping.index => {mitra}/point/point/belanja-pribadi
                Route::get('/', [App\Http\Controllers\Mitra\BonusPointController::class, 'index'])->name('index');
                // mitra.point.my-shopping.datatable => {mitra}/point/point/belanja-pribadi/datatable
                Route::get('/datatable', [App\Http\Controllers\Mitra\BonusPointController::class, 'dataTable'])->name('datatable');
                // mitra.point.my-shopping.total => {mitra}/point/point/belanja-pribadi/total
                Route::get('/total', [App\Http\Controllers\Mitra\BonusPointController::class, 'totalBonus'])->name('total');
            });

        // point belanja pribadi
        Route::prefix('reward')
            ->name('reward.')
            ->middleware('auth.permit:mitra.point.reward.index')
            ->group(function () {
                // mitra.point.reward.index => {mitra}/point/point/reward
                Route::get('/', [App\Http\Controllers\Mitra\RewardController::class, 'index'])->name('index');

                Route::prefix('claim/{point}')
                    ->name('claim.')
                    ->group(function () {
                        // mitra.point.reward.claim.show => {mitra}/point/point/reward/claim/{point}
                        Route::get('/', [App\Http\Controllers\Mitra\RewardController::class, 'claim'])->name('show');
                        // mitra.point.reward.claim.save => {mitra}/point/point/reward/claim/{point}
                        Route::post('/', [App\Http\Controllers\Mitra\RewardController::class, 'claim'])->name('save');
                    });
            });
    });
