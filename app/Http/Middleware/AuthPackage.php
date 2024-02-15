<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthPackage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // // old version
        // $routeName = $request->route()->getName();
        // $skips = ['mitra.package.index', 'mitra.package.transfer'];

        // if (in_array($routeName, $skips)) {
        //     if (!$user->is_member_mitra_user || ($user->is_member_mitra_user && $user->is_top_member)) {
        //         return redirect()->route('dashboard');
        //     }

        //     if ($user->userPackage->status == MITRA_PKG_CONFIRMED) return redirect()->route('dashboard');
        // } else {
        //     if ($user->is_member_mitra_user && !$user->is_top_member && ($user->userPackage->status != MITRA_PKG_CONFIRMED)) {
        //         return redirect()->route('mitra.package.index');
        //     }
        // }

        // new version (TRADMED)
        if (!$user->is_main_user) {
            if (!$user->has_package) {
                $message = 'Anda belum memiliki paket. Silahkan melakukan pembelian.';

                return request()->ajax()
                    ? ajaxError($message, 403)
                    : pageError($message, 'dashboard');
            }
        }

        return $next($request);
    }
}
