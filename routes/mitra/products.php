<?php

use Illuminate\Support\Facades\Route;

Route::prefix('my-products')
    ->name('myProducts.')
    ->group(function () {
        // mitra.myProduct.index => /my-products/
        Route::get('/', [App\Http\Controllers\Mitra\ProductController::class, 'index'])
            ->middleware('auth.permit:mitra.myProducts.index')
            ->name('index');
    });
