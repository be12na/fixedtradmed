<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->name('mitra.')
    ->group(function () {
        // Route::middleware('auth.package')
        // ->group(function () {
        include 'mitra/products.php';
        include 'mitra/member.php';
        // include 'mitra/point.php';
        include 'mitra/bonus.php';
        include 'mitra/bank.php';
        // });

        include 'mitra/purchase.php';
        // include 'mitra/package.php';
    });
