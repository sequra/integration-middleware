<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\PaymentMethods\Requests\GetPaymentMethodsRequest;
use SeQura\Core\Infrastructure\Http\Exceptions\HttpRequestException;

/**
 * Class PaymentMethodsController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class PaymentMethodsController extends BaseController
{
    /**
     * Returns active connection data.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws HttpRequestException
     */
    public function getPaymentMethods(Request $request): JsonResponse
    {
        $data = AdminAPI::get()
            ->paymentMethods($request->get('storeId'))
            ->getPaymentMethods(new GetPaymentMethodsRequest((string)$request->get('identifier'), true));

        return response()->json($data->toArray());
    }
}
