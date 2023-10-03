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
                !empty($config['type']) ? $config['type'] : '',
                !empty($config['size']) ? $config['size'] : '',
                !empty($config['font-color']) ? $config['font-color'] : '',
                !empty($config['background-color']) ? $config['background-color'] : '',
                !empty($config['alignment']) ? $config['alignment'] : '',
                !empty($config['branding']) ? $config['branding'] : '',
                !empty($config['starting-text']) ? $config['starting-text'] : '',
                !empty($config['amount-font-size']) ? $config['amount-font-size'] : '',
                !empty($config['amount-font-color']) ? $config['amount-font-color'] : '',
                !empty($config['amount-font-bold']) ? $config['amount-font-bold'] : '',
                !empty($config['link-font-color']) ? $config['link-font-color'] : '',
                !empty($config['link-underline']) ? $config['link-underline'] : '',
                !empty($config['border-color']) ? $config['border-color'] : '',
                !empty($config['border-radius']) ? $config['border-radius'] : '',
                !empty($config['no-costs-claim']) ? $config['no-costs-claim'] : '',
                !empty($labels['messages']) ? $labels['messages'] : [],
                !empty($labels['messagesBelowLimit']) ? $labels['messagesBelowLimit'] : []
            )
        );

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $response->toArray()['errorCode']
        );
    }
}
