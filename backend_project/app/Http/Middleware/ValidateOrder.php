<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class ValidateOrder
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
        $orderId = $request->route('orderId');

        if (!is_numeric($orderId)) {
            return response(['message' => 'Wrong id format: `' . $orderId . '`, id must be numeric'], 400);
        }

        return $next($request);
    }
}
