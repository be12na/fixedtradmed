<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class BladeDirectiveServiceProvider extends ServiceProvider
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
        // if
        Blade::if('hasPermission', function ($routeName, User $user = null) {
            return hasPermission($routeName, $user);
        });

        Blade::if('isLive', function () {
            return isLive();
        });

        Blade::if('canStockOpname', function () {
            return canStockOpname();
        });

        Blade::if('canSale', function () {
            return canSale();
        });

        Blade::if('authIsMitraPremium', function () {
            return authIsMitraPremium();
        });

        Blade::if('authIsManager', function () {
            return authIsManager();
        });

        Blade::if('authIsManagerDistributor', function () {
            return authIsManagerDistributor();
        });

        // directive string
        Blade::directive('menuActive', function ($requireNames) {
            return "<?php echo matchMenu($requireNames) ? 'active' : ''; ?>";
        });

        Blade::directive('sidebarActiveOrCollapsed', function ($requireNames) {
            return "<?php echo matchMenu($requireNames) ? 'active' : 'collapsed'; ?>";
        });

        Blade::directive('sidebarShow', function ($requireNames) {
            return "<?php echo matchMenu($requireNames) ? 'show' : ''; ?>";
        });

        Blade::directive('formatNumber', function ($value, $decimals = 0) {
            return "<?php echo formatNumber($value, $decimals); ?>";
        });

        Blade::directive('formatCurrency', function ($value, $decimalLength = 2, $startSymbol = true, $endSymbol = false) {
            return "<?php echo formatCurrency($value, $decimalLength, $startSymbol, $endSymbol); ?>";
        });

        Blade::directive('formatAutoNumber', function ($value, $asCurrency = true, $maxDecimalLength = 2, $currencyStartSymbol = true, $currencyEndSymbol = false) {
            return "<?php echo formatAutoNumber($value, $asCurrency, $maxDecimalLength, $currencyStartSymbol, $currencyEndSymbol); ?>";
        });

        Blade::directive('formatDatetime', function ($value, string $format = null) {
            return "<?php echo formatDatetime($value, $format); ?>";
        });

        Blade::directive('formatMediumDatetime', function ($value) {
            return "<?php echo formatMediumDatetime($value); ?>";
        });

        Blade::directive('formatFullDatetime', function ($value) {
            return "<?php echo formatFullDatetime($value); ?>";
        });

        Blade::directive('formatShortDate', function ($value) {
            return "<?php echo formatShortDate($value); ?>";
        });

        Blade::directive('formatMediumDate', function ($value) {
            return "<?php echo formatMediumDate($value); ?>";
        });

        Blade::directive('formatFullDate', function ($value) {
            return "<?php echo formatFullDate($value); ?>";
        });

        Blade::directive('dayName', function ($value) {
            return "<?php echo dayName($value); ?>";
        });

        Blade::directive('contentCheck', function ($value) {
            return "<?php echo contentCheck((bool) $value); ?>";
        });

        Blade::directive('optionSelected', function ($data_value, $true_value = '0') {
            return "<?php echo optionSelected($data_value, $true_value); ?>";
        });

        Blade::directive('referralLinkHtml', function () {
            return "<?php echo view('partials.referral-link', ['refLinkMode' => 'html'])->render(); ?>";
        });

        Blade::directive('referralLinkScript', function () {
            return "<?php echo view('partials.referral-link', ['refLinkMode' => 'script'])->render(); ?>";
        });
    }
}
