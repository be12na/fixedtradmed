<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->name('member.')
    ->middleware('auth.grouppermit:member')
    ->group(function () {
        include 'member/teams.php';
        include 'member/products.php';
        include 'member/payment.php';
        include 'member/sale.php';
        include 'member/transfer.php';
    });
