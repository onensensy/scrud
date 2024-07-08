<?php

namespace Sensy\Scrud\app\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImpersonatorMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->can("impersonate")) {
            return $next($request);
        }
        return abort(401, 'Unauthorised');
    }
}
