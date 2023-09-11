<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;

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
        $data = AdminAPI::get()->disconnect($request->get('storeId'))->disconnect();

        return response()->json($data->toArray());
    }
}
