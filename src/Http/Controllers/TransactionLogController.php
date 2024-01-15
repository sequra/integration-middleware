<?php

namespace SeQura\Middleware\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;

/**
 * Class TransactionLogController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class TransactionLogController extends BaseController
{
    /**
     * Returns existing transaction logs.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function getTransactionLogs(Request $request): JsonResponse
    {
        $data = AdminAPI::get()
            ->transactionLogs($request->get('storeId'))
            ->getTransactionLogs($request->get('page'), $request->get('limit'));

        return response()->json($data->toArray());
    }
}
