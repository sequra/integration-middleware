<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Middleware\ORM\Eloquent\Configuration;

/**
 * Class ConfigurationRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class ConfigurationRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getEloquentModelClassName(): string
    {
        return Configuration::class;
    }
}
