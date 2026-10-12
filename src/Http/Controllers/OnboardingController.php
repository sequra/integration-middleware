<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ConnectionRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\OnboardingRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Connection\Requests\ReRegisterWebhookRequest;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Services\StatisticalDataService;
use SeQura\Core\Infrastructure\ServiceRegister;

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
        $storeId = (string)$request->get('storeId');
        $response = AdminAPI::get()->connection($storeId)->connect(new OnboardingRequest(
            $this->generateConnectionRequests($data),
            $this->getSendStatisticalData($data, $storeId)
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
     * The onboarding frontend does not always send the statistical data choice, and the core saves it on every
     * connect. Without one the stored choice is kept, so a reconnect does not switch the order reports off; a new
     * connection sends them.
     *
     * @param mixed[] $data
     * @param string $storeId
     *
     * @return bool
     */
    private function getSendStatisticalData(array $data, string $storeId): bool
    {
        if (array_key_exists('sendStatisticalData', $data)) {
            return (bool)$data['sendStatisticalData'];
        }

        /** @var StatisticalDataService $statisticalDataService */
        $statisticalDataService = ServiceRegister::getService(StatisticalDataService::class);
        $statisticalData = StoreContext::doWithStore($storeId, [$statisticalDataService, 'getStatisticalData']);

        return $statisticalData === null || $statisticalData->isSendStatisticalData();
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
