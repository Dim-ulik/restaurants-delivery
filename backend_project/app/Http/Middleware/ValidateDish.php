<?php

namespace App\Http\Middleware;

use App\Models\Dish;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class ValidateDish
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
        $dishId = $request->route('dishId');

        if (!is_numeric($dishId)) {
            return response(['message' => 'Wrong dish id format: `' . $dishId . '`, id must be numeric'], 400);
        }

        return $next($request);
    }
}
