<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

class AuthPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$routesName)
    {
        if (!app('appPermission')->hasPermission($routesName ?: [$request->route()->getName()], $request->user())) {
            if ($request->ajax()) {
                return ajaxError('Anda tidak memiliki akses.', 403);
            } else {
                return pageError('Anda tidak memiliki akses untuk membuka halaman tesebut.');
            }
        }

        return $next($request);
    }
}
