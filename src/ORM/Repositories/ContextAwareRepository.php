<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Middleware\ORM\Transformers\ContextAwareOrmEntityTransformer;
use SeQura\Middleware\ORM\Transformers\OrmEntityTransformer;

/**
 * Class ContextAwareRepository
 *
 * Repository of entities kept in a table shared by every tenant. It differs from the base repository only in the
 * transformer it builds, which scopes every read and write to the context of the current tenant; the base
 * insert, update and delete need no change.
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
abstract class ContextAwareRepository extends BaseRepository
{
    /**
     * Gets the context aware transformer instance.
     *
     * @return OrmEntityTransformer
     */
    protected function getTransformer(): OrmEntityTransformer
    {
        if ($this->transformer === null) {
            $ormInstance = new $this->entityClass();
            $this->transformer = new ContextAwareOrmEntityTransformer(
                $this->getTableName(),
                $ormInstance
            );
        }

        return $this->transformer;
    }
}
