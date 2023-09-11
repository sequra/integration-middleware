<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\Domain\Version\Exceptions\FailedToRetrieveVersionException;

/**
 * Class IntegrationController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class IntegrationController extends BaseController
{
    /**
     * Returns integration version information.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveVersionException
     */
    public function getVersion(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->integration($request->get('storeId'))->getVersion();

        return response()->json($data->toArray());
    }

    /**
     * Returns the integration UI state.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getState(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->integration($request->get('storeId'))->getUIState();

        return response()->json($data->toArray());
    }


    /**
     * Returns the integration shop name.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getShopName(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->integration($request->get('storeId'))->getShopName();

        return response()->json($data->toArray());
    }
}
