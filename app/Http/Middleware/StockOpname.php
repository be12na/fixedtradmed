<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StockOpname
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
        if (!canStockOpname()) {
            $message = app('neo')->stockOpnameMessage();
            if ($request->ajax()) {
                return ajaxError($message, 403);
            } else {
                return pageError($message);
            }
        }

        return $next($request);
    }
}
