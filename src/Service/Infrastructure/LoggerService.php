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
                $contextData[$item->getName()] = $this->formatContextValue($item->getValue());
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

    /**
     * Formats a context value for safe logging without unbounded memory allocation.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function formatContextValue(mixed $value): string
    {
        if ($value instanceof \Throwable) {
            return get_class($value) . ': ' . $value->getMessage()
                . ' in ' . $value->getFile() . ':' . $value->getLine();
        }

        if (is_object($value)) {
            $encoded = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR, 5);

            return $encoded !== false ? $encoded : get_class($value) . ' (not serializable)';
        }

        return print_r($value, true);
    }
}
