<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('throttle:byIP20')
    ->group(function () {
        Route::middleware('guest')
            ->group(function () {
                Route::get('login', [\App\Http\Controllers\LoginController::class, 'showLoginForm'])->name('login');
                Route::post('login', [\App\Http\Controllers\LoginController::class, 'login']);

                //         Route::prefix('ref-mitra')
                //             ->name('referral-mitra.')
                //             ->group(function () {
                //                 // referral-mitra.link => /ref-mitra/{mitraReferral}
                //                 Route::get('/{mitraReferral}', [App\Http\Controllers\Member\ReferralLinkController::class, 'index'])->name('link');
                //                 // referral-mitra.register => /ref-mitra/{mitraReferral}/register
                //                 Route::post('/{mitraReferral}/register', [App\Http\Controllers\Member\ReferralLinkController::class, 'register'])->name('register');
                //             });

                //         // Route::get('activate/{username_activation}', [App\Http\Controllers\ActivationController::class, 'activate'])->name('activate');
            });

        include 'public.php';
    });

Route::middleware('throttle:byIP100')
    ->group(function () {
        Route::get('/', function () {
            if (Auth::check()) return redirect()->route('home');
            // return view('welcome');
            return redirect()->route('login');
        })->name('welcome');

        // Route::get('reload-captcha', function() {
        //     return captcha_src();
        // });

        Route::get('logout', [\App\Http\Controllers\LoginController::class, 'logout'])->name('logout');
        Route::post('logout', [\App\Http\Controllers\LoginController::class, 'logout']);

        Route::get('/home', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

        Route::get('/select-region', [App\Http\Controllers\RegionController::class, 'region'])->name('selectRegion');
        Route::get('/select-province', [App\Http\Controllers\RegionController::class, 'province'])->name('selectProvince');
        Route::get('/select-city', [App\Http\Controllers\RegionController::class, 'city'])->name('selectCity');
        Route::get('/select-district', [App\Http\Controllers\RegionController::class, 'district'])->name('selectDistrict');
        Route::get('/select-village', [App\Http\Controllers\RegionController::class, 'village'])->name('selectVillage');

        Route::prefix('{userDomain}')
            ->middleware('auth')
            ->group(function () {
                Route::get('/', function () {
                    return redirect()->route('dashboard');
                })->middleware('auth.profile');
                // dashboard => /dashboard
                Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
                    ->middleware('auth.profile')
                    ->name('dashboard');
                // password => /password
                Route::prefix('password')
                    ->name('password.')
                    ->group(function () {
                        Route::get('/', [App\Http\Controllers\PasswordController::class, 'passwordForm'])->name('index');
                        Route::post('/update', [App\Http\Controllers\PasswordController::class, 'updatePassword'])->name('update');
                    });

                // profile => /profile
                Route::prefix('profile')
                    ->name('profile.')
                    ->group(function () {
                        Route::get('/', [App\Http\Controllers\ProfileController::class, 'index'])->name('index');
                        // profile.edit => /profile/edit
                        Route::get('/edit', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
                        // profile.update => /profile/update
                        Route::post('/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('update');
                    });

                include 'main.php';

                Route::middleware('auth.profile')
                    ->group(function () {
                        include 'member.php';
                        include 'mitra.php';
                    });
            });
    });
