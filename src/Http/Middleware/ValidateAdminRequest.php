<?php

namespace SeQura\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateAdminRequest
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->has('action') || !$request->has('storeId')) {
            new HttpException(400, 'Invalid request.');
        }

        return $next($request);
    }
}
