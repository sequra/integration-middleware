<?php

namespace SeQura\Middleware\Uninstall\Components;

use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Interface GlobalDataDeleter
 *
 * @package SeQura\Middleware\Uninstall\Components
 */
class GlobalDataDeleter
{
    /**
     * Deletes data from the global context.
     *
     * @param string $entity
     * @param array $excluded
     *
     * @return void
     */
    public static function delete(string $entity, array $excluded = []): void
    {
        try {
            $queryFilter = static::getUserSelector();

            // process excluded queue items
            foreach ($excluded as $column => $value) {
                $queryFilter->where($column, Operators::NOT_EQUALS, $value);
            }

            static::getRepository($entity)->deleteWhere($queryFilter);
        } catch (\Exception $e) {
            Logger::logError(
                "Failed to delete global data because: {$e->getMessage()}",
                'Integration',
                ['ExceptionTrace' => $e->getTraceAsString()]
            );
        }
    }

    /**
     * @param $entity
     *
     * @return RepositoryInterface | ConditionallyDeletes
     *
     * @throws RepositoryNotRegisteredException
     */
    protected static function getRepository($entity): RepositoryInterface|ConditionallyDeletes
    {
        return RepositoryRegistry::getRepository($entity);
    }

    /**
     * @return QueryFilter
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected static function getUserSelector(): QueryFilter
    {
        /** @var ConfigurationManager $configService */
        $configService = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
        $context = $configService->getContext();
        $filter = new QueryFilter();
        $filter->where('context', Operators::EQUALS, $context);

        return $filter;
    }
}
