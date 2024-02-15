<?php

namespace App\Providers;

use App\Models\Bank;
use App\Models\Branch;
use App\Models\BranchPayment;
use App\Models\BranchSale;
use App\Models\BranchTransfer;
use App\Models\MitraPurchase;
use App\Models\MitraReward;
use App\Models\MitraRewardClaim;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDiscount;
use App\Models\ProductReward;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\Zone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AplikasiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->app->singleton('appPermission', function () {
            return new \App\Helpers\AppPermission;
        });

        $this->app->singleton('neo', function () {
            return new \App\Helpers\Neo;
        });

        $this->app->singleton('appStructure', function () {
            return new \App\Helpers\AppStructure;
        });

        view()->share('vFile', '__t=' . mt_rand(100000001, 999999999));

        $this->routeBindingModel();
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('byIP100', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip())->response(function () {
                return response('Too many request in your ip address', 429);
            });
        });

        RateLimiter::for('byIP20', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip())->response(function () {
                return response('Too many request in your ip address', 429);
            });
        });

        RateLimiter::for('bySession', function (Request $request) {
            return Limit::perMinute(30)->by($request->session_id)->response(function () {
                return response('Too many request', 429);
            });
        });
    }

    protected function routeBindingModel()
    {
        Route::bind('mainAdmin', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byId($value)
                    ->where('user_group', '=', USER_GROUP_MAIN)
                    ->whereIn('user_type', [USER_TYPE_MASTER, USER_TYPE_ADMIN])
                    ->first()
            );
        });

        Route::bind('productCategory', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                ProductCategory::byId($value)->first()
            );
        });

        Route::bind('product', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                Product::byId($value)->first()
            );
        });

        Route::bind('branch', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                Branch::byId($value)->first()
            );
        });

        Route::bind('mainBank', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                Bank::byId($value)->byType(OWNER_BANK_MAIN)->first()
            );
        });

        Route::bind('memberBank', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                Bank::byId($value)->byType(OWNER_BANK_MEMBER)->first()
            );
        });

        // Route::bind('internalStructure', function ($value, RoutingRoute $route) {
        //     return app('appStructure')->getDataById(true, intval($value)) ?? abort(404);
        // });

        Route::bind('userMember', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byId($value)->byMemberGroup()->first()
            );
        });

        Route::bind('branchSale', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchSale::byId($value)->first()
            );
        });

        Route::bind('branchSaleModifiable', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchSale::byId($value)
                    ->whereDoesntHave('transfer')
                    ->first()
            );
        });

        Route::bind('branchPaymentModifiable', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchPayment::byId($value)
                    ->byModifiable()
                    ->first()
            );
        });

        Route::bind('branchPaymentTransferable', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchPayment::byId($value)
                    ->byTransferable()
                    ->first()
            );
        });

        Route::bind('branchPaymentApproving', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchPayment::byId($value)
                    ->byApproving()
                    ->first()
            );
        });

        Route::bind('branchPayment', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchPayment::byId($value)->first()
            );
        });

        Route::bind('branchTransfer', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                BranchTransfer::byId($value)->with(['manager', 'branch', 'transferDetails' => function ($td) {
                    return $td->with(['branchSale' => function ($bs) {
                        return $bs->with(['products' => function ($bsd) {
                            return $bsd->withTrashed();
                        }, 'salesman'])->withTrashed();
                    }])->withTrashed();
                }])->first(),
                'Data transfer tidak ditemukan.',
                str_replace('.detail', '.index', $route->getName())
            );
        });

        Route::bind('mitraReferral', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byMitraReferral($value)->first()
            );
        });

        Route::bind('mitraPremium', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byUsername($value)
                    ->byMitraPremium()
                    ->byStatus(USER_STATUS_ACTIVE)
                    ->byActivated(true)
                    ->first(),
                'Referral tidak ditemukan atau sudah tidak aktif',
                function ($message) {
                    if (strtolower(request()->method()) == 'post') {
                        return redirect()->back()->withInput()
                            ->with('message', $message)
                            ->with('messageClass', 'danger');
                    } else {
                        return redirect()->route('login')
                            ->with('message', $message)
                            ->with('messageClass', 'danger');
                    }
                }
            );
        });

        Route::bind('mitraReferral', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byUsername($value)
                    ->byStatus(USER_STATUS_ACTIVE)
                    ->byActivated(true)
                    ->first(),
                'Referral tidak ditemukan atau sudah tidak aktif',
                function ($message) {
                    if (request()->isMethod('POST')) {
                        return redirect()->back()->withInput()
                            ->with('message', $message)
                            ->with('messageClass', 'danger');
                    } else {
                        return redirect()->route('login')
                            ->with('message', $message)
                            ->with('messageClass', 'danger');
                    }
                }
            );
        });

        Route::bind('userMitra', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byId($value)
                    ->byMitraGroup()
                    ->byActivated()
                    ->with(['referral', 'branch'])
                    ->first()
            );
        });

        Route::bind('registerMitra', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byId($value)
                    ->byMitraGroup()
                    ->byStatus(USER_STATUS_INACTIVE)
                    ->byActivated(false)
                    ->with(['referral' => function ($ref) {
                        return $ref->with(['branches']);
                    }])
                    ->first()
            );
        });

        Route::bind('mitraPurchase', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                MitraPurchase::byId($value)
                    ->byActive()
                    ->with(['products', 'branch', 'manager'])
                    ->first()
            );
        });

        Route::bind('mitraStore', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                User::byUsername($value)
                    ->byMitraGroup()
                    ->byStatus(USER_STATUS_ACTIVE)
                    ->byActivated()
                    // ->with(['referral', 'branch'])
                    ->first(),
                'Toko tidak terdaftar',
                'login'
            );
        });

        Route::bind('mitraDiscount', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                ProductDiscount::byId($value)->first()
            );
        });

        Route::bind('mitraReward', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                ProductReward::byId($value)->first()
            );
        });

        Route::bind('zone', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                Zone::byId($value)->first()
            );
        });

        Route::bind('settingBonusMitraShoppingTarget', function ($value, RoutingRoute $route) {
            $value = strtolower($value);

            $result = null;
            foreach (BONUS_TYPE_MITRA_SHOPPINGS as $key => $arr) {
                if ($value == $arr['routeKey']) {
                    $result = $key;
                    break;
                }
            }

            return fallbackRouteBinding(
                $result,
                'Jenis bonus tidak tersedia.',
                'main.settings.bank.index'
            );
        });

        Route::bind('mainReward', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                MitraReward::byId($value)->first()
            );
        });

        Route::bind('mitraRewardClaim', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                MitraRewardClaim::byId($value)->first()
            );
        });

        Route::bind('confirmMitraRewardClaim', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                MitraRewardClaim::byId($value)->onPending()->first()
            );
        });

        Route::bind('userPackageRO', function ($value, RoutingRoute $route) {
            return fallbackRouteBinding(
                UserPackage::byId($value)->byRepeatOrderType()->first()
            );
        });
    }
}
