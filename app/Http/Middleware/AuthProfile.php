<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthProfile
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

        if ($user && !$user->is_main_user && !$user->is_profile) {
            return redirect()->route('profile.index')
                ->with('message', 'Silahkan lengkapi profile anda.')
                ->with('messageClass', 'warning');
        }

        return $next($request);
    }
}
