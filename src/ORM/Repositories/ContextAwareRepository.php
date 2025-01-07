<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Middleware\ORM\Transformers\OrmEntityTransformer;

/**
 * Class ContextAwareRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
abstract class ContextAwareRepository extends BaseRepository
{
    /**
     * Executes insert query and returns ID of created entity. Entity will be updated with new ID.
     *
     * @param Entity $entity Entity to be saved.
     *
     * @return int Identifier of saved entity.
     *
     * @throws QueryFilterInvalidParamException
     * @throws \JsonException
     */
    public function save(Entity $entity): int
    {
        $data = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
        $id = $this->getTransformer()->newQuery()->insertGetId($data);
        $entity->setId($id);
        $this->update($entity);

        return $id;
    }

    /**
     * Executes update query and returns success flag.
     *
     * @param Entity $entity Entity to be updated.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     *
     * @throws \JsonException
     * @throws QueryFilterInvalidParamException
     */
    public function update(Entity $entity): bool
    {
        $data = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
        $filter = (new QueryFilter())->where('id', Operators::EQUALS, $entity->getId());
        $rowsAffected = $this->getTransformer()
            ->transformFilter($filter)
            ->update($data);

        return $rowsAffected === 1;
    }

    /**
     * Gets EloquentTransformer instance.
     *
     * @return OrmEntityTransformer
     */
    protected function getTransformer(): OrmEntityTransformer
    {
        if ($this->transformer === null) {
            $ormInstance = new $this->entityClass();
            $this->transformer = new OrmEntityTransformer(
                $this->getTableName(),
                $ormInstance
            );
        }

        return $this->transformer;
    }

    /**
     * Returns the table name.
     *
     * @return string
     */
    abstract protected function getTableName(): string;
}
