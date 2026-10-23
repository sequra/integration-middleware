<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ReRegisterWebhookRequest;

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
            $this->generateConnectionRequests($data)
        ));

        $body = $response->toArray();
        // The core reports unhandled errors with a statusCode of 0, which is not a valid HTTP status.
        $statusCode = (int)($body['statusCode'] ?? 0);

        return response()->json(
            $body,
            $response->isSuccessful() ? 200 : ($statusCode ?: 500)
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
     * Registers the store integration of a connected deployment in the seQura Merchant Portal again.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reRegisterWebhooks(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()->connection($request->get('storeId'))->reRegisterWebhooks(
            new ReRegisterWebhookRequest(
                (string)($data['environment'] ?? ''),
                (string)($data['merchantId'] ?? ''),
                (string)($data['username'] ?? ''),
                (string)($data['password'] ?? ''),
                (string)($data['deployment'] ?? '')
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
                (string)($connectionData['merchantId'] ?? ''),
                (string)($connectionData['username'] ?? ''),
                (string)($connectionData['password'] ?? ''),
                $connectionData['deployment']
            );
        }

        return $connectionRequests;
    }
}
