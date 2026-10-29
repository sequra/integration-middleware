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

        return $this->jsonResponse($response);
    }
}
