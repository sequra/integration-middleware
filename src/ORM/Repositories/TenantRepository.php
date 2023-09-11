<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Middleware\ORM\Eloquent\Tenant;

/**
 * Class TenantRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class TenantRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getEloquentModelClassName(): string
    {
        return Tenant::class;
    }
}
