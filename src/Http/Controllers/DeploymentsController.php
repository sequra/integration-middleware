<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;

/**
 * Class DeploymentsController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class DeploymentsController extends BaseController
{
    /**
     * Returns all deployments the store can connect to.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getDeployments(Request $request): JsonResponse
    {
        $response = AdminAPI::get()->deployments($request->get('storeId'))->getAllDeployments();

        return $this->jsonResponse($response);
    }

    /**
     * Returns the deployments the store is not connected to yet.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getNotConnectedDeployments(Request $request): JsonResponse
    {
        $response = AdminAPI::get()->deployments($request->get('storeId'))->getNotConnectedDeployments();

        return $this->jsonResponse($response);
    }
}
