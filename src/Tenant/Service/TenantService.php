<?php

namespace SeQura\Middleware\Tenant\Service;

use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Middleware\Tenant\Entity\Tenant;

/**
 * Class TenantService
 *
 * @package SeQura\Middleware\Tenant\Service
 */
class TenantService
{
    protected RepositoryInterface $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns a Tenant for the given context, or null if tenant does not exist
     *
     * @param string $context
     *
     * @return Tenant|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getTenantByContext(string $context): ?Tenant
    {
        $query = new QueryFilter();
        $query->where('context', Operators::EQUALS, $context);

        /** @var Tenant|null $entity */
        $entity = $this->repository->selectOne($query);

        return $entity;
    }

    /**
     * Creates a new instance of Tenant and saves it to the repository
     *
     * @param string $context
     *
     * @return void
     */
    public function createTenant(string $context): void
    {
        $tenant = new Tenant();
        $tenant->context = $context;

        $this->repository->save($tenant);
    }
}
