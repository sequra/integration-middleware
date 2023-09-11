<?php

namespace SeQura\Middleware\ORM\Repositories;

use Illuminate\Database\Eloquent\Model;
use JsonException;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Middleware\ORM\Transformers\EloquentTransformer;

/**
 * Class BaseRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
abstract class BaseRepository implements RepositoryInterface, ConditionallyDeletes
{
    protected ?EloquentTransformer $transformer = null;
    protected string $entityClass;

    /**
     * Returns table name for the repository
     *
     * @return string
     */
    abstract protected function getEloquentModelClassName(): string;

    /**
     * Returns full class name.
     *
     * @return string Full class name.
     */
    public static function getClassName(): string
    {
        return static::class;
    }

    /**
     * Sets repository entity.
     *
     * @param string $entityClass Repository entity class.
     */
    public function setEntityClass($entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    /**
     * Executes select query.
     *
     * @param QueryFilter|null $filter Filter for query.
     *
     * @return Entity[] A list of found entities ot empty array.
     *
     * @throws JsonException
     * @throws QueryFilterInvalidParamException
     */
    public function select(QueryFilter $filter = null): array
    {
        $queryBuilder = $this->getTransformer()->transformFilter($filter);
        $baseEntities = $queryBuilder->get()->toArray();

        return $this->getTransformer()->toOrmCollection($baseEntities);
    }

    /**
     * Executes select query and returns first result.
     *
     * @param QueryFilter|null $filter Filter for query.
     *
     * @return Entity|null First found entity or NULL.
     *
     * @throws JsonException
     * @throws QueryFilterInvalidParamException
     */
    public function selectOne(QueryFilter $filter = null): ?Entity
    {
        if ($filter === null) {
            $filter = new QueryFilter();
        }

        $filter->setLimit(1);
        $results = $this->select($filter);

        return empty($results) ? null : $results[0];
    }

    /**
     * Executes insert query and returns ID of created entity. Entity will be updated with new ID.
     *
     * @param Entity $entity Entity to be saved.
     *
     * @return int Identifier of saved entity.
     *
     * @throws QueryFilterInvalidParamException|JsonException
     */
    public function save(Entity $entity): int
    {
        $data = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
        $data['type'] = $entity->getConfig()->getType();
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
     * @throws QueryFilterInvalidParamException|JsonException
     */
    public function update(Entity $entity): bool
    {
        $data = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
        $data['type'] = $entity->getConfig()->getType();
        $filter = (new QueryFilter())->where('id', Operators::EQUALS, $entity->getId());
        $rowsAffected = $this->getTransformer()
            ->transformFilter($filter)
            ->update($data);

        return $rowsAffected === 1;
    }

    /**
     * Executes delete query and returns success flag.
     *
     * @param Entity $entity Entity to be deleted.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function delete(Entity $entity): bool
    {
        $filter = (new QueryFilter())->where('id', Operators::EQUALS, $entity->getId());
        $rowsAffected = $this->deleteWhere($filter);

        return $rowsAffected === 1;
    }

    /**
     * Counts records that match filter criteria.
     *
     * @param QueryFilter|null $filter Filter for query.
     *
     * @return int Number of records that match filter criteria.
     *
     * @throws QueryFilterInvalidParamException
     */
    public function count(QueryFilter $filter = null): int
    {
        return $this->getTransformer()->transformFilter($filter)->count();
    }

    /**
     * Deletes records identified by the query.
     *
     * @param QueryFilter|null $queryFilter
     *
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWhere(QueryFilter $queryFilter = null): int
    {
        return $this->getTransformer()->transformFilter($queryFilter)->delete();
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
            $this->transformer = new EloquentTransformer($this->getEloquentModelClassName(), $ormInstance);
        }

        return $this->transformer;
    }
}
