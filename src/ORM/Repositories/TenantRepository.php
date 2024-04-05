<?php

namespace SeQura\Middleware\ORM\Repositories;

/**
 * Class TenantRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class TenantRepository extends BaseRepository
{
    protected const TABLE_NAME = 'tenants';

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return static::TABLE_NAME;
    }
}
