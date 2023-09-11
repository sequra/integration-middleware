<?php

namespace SeQura\Middleware\ORM\Repositories;

use Illuminate\Database\Eloquent\Model;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Middleware\ORM\Transformers\ContextAwareTransformer;
use SeQura\Middleware\ORM\Transformers\EloquentTransformer;

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
        /** @var Model $eloquentModel */
        $eloquentModel = $this->getTransformer()->newQuery()->create($data);
        $entity->setId($eloquentModel->id);
        $this->update($entity);

        return $eloquentModel->id;
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
     * @return EloquentTransformer
     */
    protected function getTransformer(): EloquentTransformer
    {
        if ($this->transformer === null) {
            $ormInstance = new $this->entityClass();
            $this->transformer = new ContextAwareTransformer(
                $this->getEloquentModelClassName(),
                $ormInstance,
                $this->getTableName()
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
