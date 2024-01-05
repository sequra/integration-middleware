<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\EmptyCategoryParameterException;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Exceptions\FailedToRetrieveCategoriesException;

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
     *
     * @throws EmptyCategoryParameterException
     */
    public function setGeneralSettings(Request $request): JsonResponse
    {
        $data = $request->post();
        $response = AdminAPI::get()
            ->generalSettings($request->get('storeId'))
            ->saveGeneralSettings(
                new GeneralSettingsRequest(
                    $data['showSeQuraCheckoutAsHostedPage'],
                    $data['sendOrderReportsPeriodicallyToSeQura'],
                    $data['allowedIPAddresses'],
                    $data['excludedProducts'],
                    $data['excludedCategories']
                )
            );

        return response()->json(
            $response->toArray(),
            $response->isSuccessful() ? 200 : $response->toArray()['errorCode']
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
}
