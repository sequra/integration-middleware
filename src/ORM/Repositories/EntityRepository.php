<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Middleware\ORM\Migrations\CreateEntityTable;
use SeQura\Middleware\ORM\TableNameProvider;

/**
 * Class EntityRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class EntityRepository extends TenantSpecificRepository
{
    protected const TABLE_NAME = CreateEntityTable::TABLE;

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return TableNameProvider::getInstance()->getTableName(static::TABLE_NAME);
    }
}
