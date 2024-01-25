<?php

namespace SeQura\Middleware\ORM\Repositories;

/**
 * Class ProcessRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class ProcessRepository extends BaseRepository
{
    protected const TABLE_NAME = 'processes';

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return static::TABLE_NAME;
    }
}
