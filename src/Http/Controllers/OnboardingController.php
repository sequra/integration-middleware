<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidEnvironmentException;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class OnboardingController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class OnboardingController extends BaseController
{
    /**
     * Returns active connection data.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getConnectionData(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->connection($request->get('storeId'))->getOnboardingData();

        return response()->json($data->toArray());
    }

    /**
     * Sets new connection data.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidEnvironmentException
     */
    public function setConnectionData(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()->connection($request->get('storeId'))->saveOnboardingData(new OnboardingRequest(
            $data['environment'],
            $data['username'],
            $data['password'],
            $data['sendStatisticalData']
        ));

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $response->toArray()['errorCode']
        );
    }

    /**
     * Validates connection data.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidEnvironmentException
     * @throws HttpRequestException
     */
    public function validateConnectionData(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()->connection($request->get('storeId'))->isConnectionDataValid(new ConnectionRequest(
            $data['environment'],
            $data['merchantId'],
            $data['username'],
            $data['password']
        ));

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : 400
        );
    }
}
