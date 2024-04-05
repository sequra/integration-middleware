<?php

namespace SeQura\Middleware\ORM\Repositories;

/**
 * Class ConfigurationRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class ConfigurationRepository extends BaseRepository
{
    protected const TABLE_NAME = 'configurations';

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return static::TABLE_NAME;
    }
}
