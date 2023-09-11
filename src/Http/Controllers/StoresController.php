<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\Domain\Stores\Exceptions\FailedToRetrieveStoresException;

/**
 * Class StoresController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class StoresController extends BaseController
{
    /**
     * Returns all stores.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getStores(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->store($request->get('storeId'))->getStores();

        return response()->json($data->toArray());
    }

    /**
     * Returns the current active store.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveStoresException
     */
    public function getCurrentStore(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->store($request->get('storeId'))->getCurrentStore();

        return response()->json($data->toArray());
    }
}
