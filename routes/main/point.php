<?php

use Illuminate\Support\Facades\Route;

// Route::prefix('point')
//     ->name('point.')
//     ->group(function () {
//         Route::prefix('belanja')
//             ->name('shopping.')
//             ->middleware('auth.permit:main.point.shopping.index')
//             ->group(function () {
//                 // main.point.shopping.index => main/point/belanja
//                 Route::get('/', [App\Http\Controllers\Main\BonusPointController::class, 'index'])->name('index');
//                 // main.point.shopping.datatable => main/point/belanja/datatable
//                 Route::get('/datatable', [App\Http\Controllers\Main\BonusPointController::class, 'dataTable'])->name('datatable');
//                 // main.point.shopping.total => main/point/belanja/total
//                 Route::get('/total', [App\Http\Controllers\Main\BonusPointController::class, 'totalBonus'])->name('total');
//                 // main.point.shopping.download => main/point/belanja/download/{fileType}
//                 Route::get('/download/{fileType}', [App\Http\Controllers\Main\BonusPointController::class, 'downloadFile'])->name('download');
//             });

//         Route::prefix('aktifasi')
//             ->name('activate.')
//             ->middleware('auth.permit:main.point.activate.index')
//             ->group(function () {
//                 // main.point.activate.index => main/point/aktifasi
//                 Route::get('/', [App\Http\Controllers\Main\BonusPointController::class, 'index'])->name('index');
//                 // main.point.activate.datatable => main/point/aktifasi/datatable
//                 Route::get('/datatable', [App\Http\Controllers\Main\BonusPointController::class, 'dataTable'])->name('datatable');
//                 // main.point.activate.total => main/point/aktifasi/total
//                 Route::get('/total', [App\Http\Controllers\Main\BonusPointController::class, 'totalBonus'])->name('total');
//                 // main.point.activate.download => main/point/aktifasi/download/{fileType}
//                 Route::get('/download/{fileType}', [App\Http\Controllers\Main\BonusPointController::class, 'downloadFile'])->name('download');
//             });

//         Route::prefix('claim-reward')
//             ->name('claim.')
//             ->middleware('auth.permit:main.point.claim.index')
//             ->group(function () {
//                 // main.point.claim.index => main/point/claim-reward
//                 Route::get('/', [App\Http\Controllers\Main\RewardClaimController::class, 'index'])->name('index');
//                 // main.point.claim.datatable => main/point/claim-reward/datatable
//                 Route::get('/datatable', [App\Http\Controllers\Main\RewardClaimController::class, 'dataTable'])->name('datatable');
//                 // main.point.claim.detail => main/point/claim-reward/detail/{mitraRewardClaim}
//                 Route::get('/detail/{mitraRewardClaim}', [App\Http\Controllers\Main\RewardClaimController::class, 'detail'])->name('detail');
//                 // main.point.claim.confirm => main/point/claim-reward/confirm/{confirmMitraRewardClaim}
//                 Route::post('/confirm/{confirmMitraRewardClaim}', [App\Http\Controllers\Main\RewardClaimController::class, 'confirm'])
//                     ->middleware('auth.permit:main.point.claim.confirm')
//                     ->name('confirm');
//             });
//     });
