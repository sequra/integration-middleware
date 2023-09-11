<?php

namespace SeQura\Middleware\Http\Controllers;

use Illuminate\Routing\Controller as LaravelController;
use SeQura\Core\Infrastructure\Configuration\Configuration;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Middleware\Service\BusinessLogic\ConfigurationService;

/**
 * Class BaseController
 *
 * @package SeQura\Middleware\Http\Controllers
 */
class BaseController extends LaravelController
{
    /**
     * @var ConfigurationService
     */
    protected ConfigurationService $configService;

    /**
     * Returns an instance of configuration service.
     *
     * @return ConfigurationService
     */
    protected function getConfigService(): ConfigurationService
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::class);
        }

        return $this->configService;
    }
}
