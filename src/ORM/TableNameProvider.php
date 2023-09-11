<?php

namespace SeQura\Middleware\ORM;

use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Singleton;

/**
 * Class TableNameProvider
 *
 * @package SeQura\Middleware\ORM
 */
class TableNameProvider extends Singleton
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * Provides ID that uniquely identifies table for a current user.
     *
     * @param string $nameFormat
     *
     * @return string
     */
    public function getTableName(string $nameFormat): string
    {
        return str_replace('{id}', $this->getTenantId(), $nameFormat);
    }

    /**
     * Returns a hashed tenant id.
     *
     * @return string
     */
    protected function getTenantId(): string
    {
        $context = ConfigurationManager::getInstance()->getContext();

        if ($context === '') {
            return $context;
        }

        return md5($context);
    }
}
