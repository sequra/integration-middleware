<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as LaravelController;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Middleware\Service\BusinessLogic\ConfigurationService;

/**
 * Class BaseController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class BaseController extends LaravelController
{
    /**
     * @var ConfigurationService
     */
    protected ConfigurationService $configService;

    /**
     * Returns an instance of configuration service.
     *
     * @return ConfigurationService
     */
    protected function getConfigService(): ConfigurationService
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::class);
        }

        return $this->configService;
    }

    /**
     * Returns the core response as JSON, with the HTTP status taken from it when it is not successful.
     *
     * @param Response $response
     *
     * @return JsonResponse
     */
    protected function jsonResponse(Response $response): JsonResponse
    {
        $body = $response->toArray();
        $statusCode = (int)($body['statusCode'] ?? 0);

        return response()->json(
            $body,
            $response->isSuccessful() ? 200 : ($statusCode ?: 500)
        );
    }
}
