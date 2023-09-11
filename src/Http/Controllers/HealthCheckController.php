<?php

namespace SeQura\Middleware\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Class HealthCheckController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class HealthCheckController extends BaseController
{
    /**
     * Check for a database connection.
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $response = response()->json();
        } catch (Exception) {
            $response = response()->json("No database connection", 500);
        }

        return $response;
    }
}
