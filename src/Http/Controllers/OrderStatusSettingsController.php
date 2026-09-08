<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests\OrderStatusSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\FailedToRetrieveShopOrderStatusesException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;

/**
 * Class OrderStatusSettingsController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class OrderStatusSettingsController
{
    /**
     * Returns all order statuses from the shop system.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveShopOrderStatusesException
     */
    public function getShopOrderStatuses(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->orderStatusSettings($request->get('storeId'))->getShopOrderStatuses();

        return response()->json($data->toArray());
    }

    /**
     * Returns existing order status settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getOrderStatusSettings(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->orderStatusSettings($request->get('storeId'))->getOrderStatusSettings();

        return response()->json($data->toArray());
    }

    /**
     * Sets new order status settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws EmptyOrderStatusMappingParameterException
     * @throws InvalidSeQuraOrderStatusException
     */
    public function setOrderStatusSettings(Request $request): JsonResponse
    {
        $response = AdminAPI::get()->orderStatusSettings($request->get('storeId'))->saveOrderStatusSettings(
            new OrderStatusSettingsRequest($request->post())
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
