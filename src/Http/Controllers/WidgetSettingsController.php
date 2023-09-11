<?php

namespace SeQura\Middleware\Http\Controllers;

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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function setWidgetSettings(Request $request): JsonResponse
    {
        $data = $request->post();
        $config = [];
        if (!empty($data['widgetConfiguration'])) {
            if (is_array($data['widgetConfiguration'])) {
                $config = $data['widgetConfiguration'];
            } else {
                $config = json_decode($data['widgetConfiguration'], true);
            }
        }

        $labels = $data['widgetLabels'] ?? [];
        $response = AdminAPI::get()->widgetConfiguration($request->get('storeId'))->setWidgetSettings(
            new WidgetSettingsRequest(
                $data['useWidgets'],
                $data['assetsKey'] ?: '',
                $data['displayWidgetOnProductPage'],
                $data['showInstallmentAmountInProductListing'],
                $data['showInstallmentAmountInCartPage'],
                $data['miniWidgetSelector'],
                $config ? $config['type'] : '',
                $config ? $config['size'] : '',
                $config ? $config['font-color'] : '',
                $config ? $config['background-color'] : '',
                $config ? $config['alignment'] : '',
                $config ? $config['branding'] : '',
                $config ? $config['starting-text'] : '',
                $config ? $config['amount-font-size'] : '',
                $config ? $config['amount-font-color'] : '',
                $config ? $config['amount-font-bold'] : '',
                $config ? $config['link-font-color'] : '',
                $config ? $config['link-underline'] : '',
                $config ? $config['border-color'] : '',
                $config ? $config['border-radius'] : '',
                $config ? $config['no-costs-claim'] : '',
                $labels ? $labels['messages'] : [],
                $labels ? $labels['messagesBelowLimit'] : []
            )
        );

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $response->toArray()['errorCode']
        );
    }
}
