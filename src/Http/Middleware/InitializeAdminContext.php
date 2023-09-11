<?php

namespace SeQura\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\ServiceRegister;

class InitializeAdminContext
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
        $configService = ServiceRegister::getService(ConfigurationManager::class);
        $configService->setContext($request->get('storeId'));

        return $next($request);
    }
}
