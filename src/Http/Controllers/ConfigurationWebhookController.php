<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\ConfigurationWebhookAPI;

/**
 * Class ConfigurationWebhookController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class ConfigurationWebhookController extends BaseController
{
    private const STATUS_CONFLICT = 409;
    private const STATUS_GONE = 410;

    /**
     * Handles a configuration request sent by the seQura Merchant Portal for the store given in the URL.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $response = ConfigurationWebhookAPI::configurationHandler((string)$request->query('storeId', ''))
            ->handleRequest((string)$request->query('signature', ''), $request->json()->all());

        if ($response->isSuccessful()) {
            return response()->json($response->toArray());
        }

        // A conflict seQura may retry unchanged; any other failure is a request it must not repeat.
        $data = $response->toArray();
        $status = (int)($data['errorCode'] ?? 0) === self::STATUS_CONFLICT ? self::STATUS_CONFLICT : self::STATUS_GONE;

        return response()->json($data, $status);
    }
}
