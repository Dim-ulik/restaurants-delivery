<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class ValidateMenu
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
        $menuId = $request->route('menuId');

        if (!is_numeric($menuId)) {
            return response(['message' => 'Wrong id format: `' . $menuId . '`, id must be numeric'], 400);
        }

        return $next($request);
    }
}
