<?php

namespace SeQura\Middleware\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;

/**
 * Class WidgetSettingsController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class WidgetSettingsController extends BaseController
{
    /**
     * Retrieves widget settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function getWidgetSettings(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->widgetConfiguration($request->get('storeId'))->getWidgetSettings();

        return response()->json($data->toArray());
    }

    /**
     * Saves widget settings.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function setWidgetSettings(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()->widgetConfiguration($request->get('storeId'))->setWidgetSettings(
            new WidgetSettingsRequest(
                $data['displayWidgetOnProductPage'] ?? false,
                $data['showInstallmentAmountInProductListing'] ?? false,
                $data['showInstallmentAmountInCartPage'] ?? false,
                $data['widgetStyles'] ?? '',
                $data['productPriceSelector'] ?? '',
                $data['defaultProductLocationSelector'] ?? '',
                $data['cartPriceSelector'] ?? '',
                $data['cartPriceSelector'] ?? '',
                $data['widgetOnCartPage'] ?? '',
                $data['widgetOnListingPage'] ?? '',
                $data['listingPriceSelector'] ?? '',
                $data['listingLocationSelector'] ?? '',
                $data['altProductPriceSelector'] ?? '',
                $data['altProductPriceTriggerSelector'] ?? '',
                $data['customLocations'] ?? ''
            )
        );

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $response->toArray()['errorCode']
        );
    }
}
