<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Middleware\ORM\Eloquent\Entity;

/**
 * Class EntityRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class EntityRepository extends TenantSpecificRepository
{
    /**
     * @inheritDoc
     */
    protected function getEloquentModelClassName(): string
    {
        return Entity::class;
    }
}
