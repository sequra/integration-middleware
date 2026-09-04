<?php

namespace SeQura\Middleware\Http\Controllers;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\CountryConfiguration\Requests\CountryConfigurationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\EmptyCountryConfigurationParameterException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\FailedToRetrieveSellingCountriesException;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\Exceptions\InvalidCountryCodeForConfigurationException;

/**
 * Class CountrySettingsController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class CountrySettingsController extends BaseController
{
    /**
     * Returns all available selling countries.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getSellingCountries(Request $request):JsonResponse
    {
        $data = AdminAPI::get()->countryConfiguration($request->get('storeId'))->getSellingCountries();

        return response()->json($data->toArray());
    }

    /**
     * Returns existing country configuration.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FailedToRetrieveSellingCountriesException
     */
    public function getCountrySettings(Request $request): JsonResponse
    {
        $data = AdminAPI::get()->countryConfiguration($request->get('storeId'))->getCountryConfigurations();
        $sellingCountries = AdminAPI::get()->countryConfiguration($request->get('storeId'))->getSellingCountries()->toArray();

        $countries = $data->toArray();
        $sellingCountries = array_map(function($country) {
            return $country['code'];
        }, $sellingCountries);

        $response = [];
        foreach ($countries as $country) {
            if (in_array($country['countryCode'], $sellingCountries)) {
                $response[] = $country;
            }
        }

        return response()->json($response);
    }

    /**
     * Sets new country configuration.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws EmptyCountryConfigurationParameterException
     * @throws InvalidCountryCodeForConfigurationException
     */
    public function setCountrySettings(Request $request): JsonResponse
    {
        $response = AdminAPI::get()->countryConfiguration($request->get('storeId'))->saveCountryConfigurations(
            new CountryConfigurationRequest($request->post())
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
