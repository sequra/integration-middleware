<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Requests\DisconnectRequest;

/**
 * Class DisconnectController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class DisconnectController extends BaseController
{
    /**
     * Disconnects integration from the shop.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function disconnect(Request $request): JsonResponse
    {
        $response = AdminAPI::get()->disconnect($request->get('storeId'))->disconnect(
            new DisconnectRequest(
                (string)$request->get('deploymentId'),
                (bool)$request->get('isFullDisconnect')
            )
        );

        $body = $response->toArray();
        // The core reports unhandled errors with a statusCode of 0, which is not a valid HTTP status.
        $statusCode = (int)($body['statusCode'] ?? 0);

        return response()->json(
            $body,
            $response->isSuccessful() ? 200 : ($statusCode ?: 500)
        );
    }
}
