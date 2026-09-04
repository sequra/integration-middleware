<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveShopPaymentMethodsException;

/**
 * Class DisconnectController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class GeneralSettingsController extends BaseController
{
    /**
     * Returns existing general settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getGeneralSettings(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->generalSettings($request->get('storeId'))->getGeneralSettings();

        return response()->json($data->toArray());
    }

    /**
     * Sets new general settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function setGeneralSettings(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()
            ->generalSettings($request->get('storeId'))
            ->saveGeneralSettings(
                new GeneralSettingsRequest(
                    $data['sendOrderReportsPeriodicallyToSeQura'],
                    $data['showSeQuraCheckoutAsHostedPage'],
                    $data['allowedIPAddresses'],
                    $data['excludedProducts'],
                    $data['excludedCategories'],
                    $data['replacementPaymentMethod']
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
     * Returns all shop categories.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveCategoriesException
     */
    public function getShopCategories(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->generalSettings($request->get('storeId'))->getShopCategories();

        return response()->json($data->toArray());
    }

    /**
     * Returns all shop payment methods.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveShopPaymentMethodsException
     */
    public function getShopPaymentMethods(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->generalSettings($request->get('storeId'))->getShopPaymentMethods();

        return response()->json($data->toArray());
    }
}
