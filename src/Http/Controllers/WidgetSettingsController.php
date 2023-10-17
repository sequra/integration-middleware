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
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(
                        [
                            'statusCode' => 400,
                            'errorCode' => 400,
                            'errorMessage' => __('messages.widget-configuration.invalid-json'),
                            'errorParameters' => [],
                        ],
                        400
                    );
                }
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
                $data['widgetConfiguration'],
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
