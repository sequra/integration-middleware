<?php

namespace SeQura\Middleware\Http\Controllers;

use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\AsyncProcessStarterService;
use SeQura\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;

/**
 * Class AsyncProcessController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class AsyncProcessController extends BaseController
{
    /**
     * @param $guid
     * @return \Illuminate\Http\Response
     */
    public function run($guid): \Illuminate\Http\Response
    {
        Logger::logDebug('Received async process request.', 'Integration', ['guid' => $guid]);

        if (!$guid) {
            abort(401, 'guid is missing');
        }

        /** @var AsyncProcessStarterService $asyncProcessService */
        $asyncProcessService = ServiceRegister::getService(AsyncProcessService::class);
        $asyncProcessService->runProcess($guid);

        return response(['success' => true], 200);
    }
}
