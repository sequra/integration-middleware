<?php

namespace SeQura\Middleware\Service\Infrastructure;

use Illuminate\Support\Facades\Log;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\Logger\LogData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\Singleton;

/**
 * Class LoggerService
 *
 * @package SeQura\Middleware\Service\Infrastructure
 */
class LoggerService extends Singleton implements ShopLoggerAdapter
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * @inheritDoc
     */
    public function logMessage(LogData $data): void
    {
        /** @var  $configService */
        $configService = ServiceRegister::getService(Configuration::class);
        $minLogLevel = $configService->getMinLogLevel();
        $logLevel = $data->getLogLevel();

        if ($logLevel > $minLogLevel && !$configService->isDebugModeEnabled()) {
            return;
        }

        $logMessage = $data->getMessage();

        $context = $data->getContext();

        if (!empty($context)) {
            $contextData = array();
            foreach ($context as $item) {
                $contextData[$item->getName()] = print_r($item->getValue(), true);
            }

            $logMessage .= PHP_EOL . 'Context data: ' . print_r($contextData, true);
        }

        switch ($logLevel) {
            case Logger::ERROR:
                Log::error($logMessage);
                break;
            case Logger::WARNING:
                Log::warning($logMessage);
                break;
            case Logger::DEBUG:
                Log::debug($logMessage);
                break;
            default:
                Log::info($logMessage);
        }
    }
}
