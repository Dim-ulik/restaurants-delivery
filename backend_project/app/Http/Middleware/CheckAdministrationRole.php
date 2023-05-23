<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdministrationRole
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
        if (!(in_array('Manager', $request->input('userInfo')['roles']) ||
            in_array('Cook', $request->input('userInfo')['roles']))) {
            return response(['message' => 'You do not have permission to this restaurant'], 403);
        }
        return $next($request);
    }
}
