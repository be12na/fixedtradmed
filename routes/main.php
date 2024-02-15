<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->name('main.')
    ->middleware('auth.grouppermit:main')
    ->group(function () {
        Route::prefix('select-2')
            ->name('select2.')
            ->group(function () {
                // managers
                Route::get('/managers', [\App\Http\Controllers\Main\Select2Controller::class, 'managers'])->name('managers');
                // branch teams
                Route::get('/branch-teams', [\App\Http\Controllers\Main\Select2Controller::class, 'branchTeams'])->name('branchTeams');
            });

        include 'main/masters.php';
        // include 'main/branches.php';
        // include 'main/members.php';
        include 'main/mitra.php';
        // include 'main/sales.php';
        include 'main/transfer.php';
        include 'main/bonus.php';
        include 'main/settings.php';
        include 'main/point.php';
        include 'main/withdraw.php';
        // include 'main/reports.php';
        // include 'main/payment.php';
    });
