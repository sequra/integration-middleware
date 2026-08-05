<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;

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
     */
    public function setConnectionData(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()->connection($request->get('storeId'))->connect(new OnboardingRequest(
            $this->generateConnectionRequests($data),
            $data['sendStatisticalData']
        ));

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $response->toArray()['statusCode']
        );
    }

    /**
     * Validates connection data.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function validateConnectionData(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()->connection($request->get('storeId'))->isConnectionDataValid(new ConnectionRequest(
            $data['environment'],
            $data['merchantId'],
            $data['username'],
            $data['password'],
            $data['deployment']
        ));

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : 400
        );
    }

    /**
     * Creates a connection request for every deployment sent in the onboarding data.
     *
     * @param mixed[] $data
     *
     * @return ConnectionRequest[]
     */
    private function generateConnectionRequests(array $data): array
    {
        $connectionRequests = [];

        foreach ($data['connectionData'] as $connectionData) {
            $connectionRequests[] = new ConnectionRequest(
                $data['environment'],
                $connectionData['merchantId'],
                $connectionData['username'],
                $connectionData['password'],
                $connectionData['deployment']
            );
        }

        return $connectionRequests;
    }
}
