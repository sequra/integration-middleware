<?php

namespace SeQura\Middleware\Service\BusinessLogic;

use SeQura\Core\Infrastructure\Configuration\Configuration;

/**
 * Class ConfigurationService
 *
 * @package SeQura\Middleware\Service\BusinessLogic
 */
abstract class ConfigurationService extends Configuration
{
    /**
     * Returns async process starter url, always in http.
     *
     * @param string $guid Process identifier.
     *
     * @return string Formatted URL of async process starter endpoint.
     */
    public function getAsyncProcessUrl($guid): string
    {
        return config('app.url') . route('async', ['guid' => $guid], false);
    }
}
