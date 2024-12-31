<?php

namespace SeQura\Middleware\ORM\Repositories;

use JsonException;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\ConditionallyDeletes;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Middleware\ORM\Transformers\OrmEntityTransformer;

/**
 * Class BaseRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
abstract class BaseRepository implements RepositoryInterface, ConditionallyDeletes
{
    protected ?OrmEntityTransformer $transformer = null;
    /**
     * @var string
     */
    protected string $entityClass;

    /**
     * Returns table name for the repository
     *
     * @return string
     */
    abstract protected function getTableName(): string;

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
     * @throws QueryFilterInvalidParamException
     * @throws JsonException
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
     * @param QueryFilter $filter Filter for query.
     *
     * @return Entity|null First found entity or NULL.
     * @throws QueryFilterInvalidParamException|JsonException
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
     * @throws QueryFilterInvalidParamException
     */
    public function save(Entity $entity): int
    {
        $data = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
        $data['type'] = $entity->getConfig()->getType();
        $id = $this->getTransformer()->newQuery()->insertGetId($data);
        $entity->setId($id);
        $this->update($entity);

        return $id;
    }

    /**
     * Executes mass insert query for all provided entities
     *
     * @param Entity[] $entities
     *
     * @throws JsonException
     */
    public function massInsert(array $entities): void
    {
        $data = [];
        foreach ($entities as $entity) {
            $entityData = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
            $entityData['type'] = $entity->getConfig()->getType();
            $data[] = $entityData;
        }

        $this->getTransformer()->newQuery()->insert($data);
    }

    /**
     * Executes update query and returns success flag.
     *
     * @param Entity $entity Entity to be updated.
     *
     * @return bool TRUE if operation succeeded; otherwise, FALSE.
     * @throws QueryFilterInvalidParamException
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
     * @param QueryFilter $filter Filter for query.
     *
     * @return int Number of records that match filter criteria.
     * @throws QueryFilterInvalidParamException
     */
    public function count(QueryFilter $filter = null): int
    {
        $queryBuilder = $this->getTransformer()->transformFilter($filter);

        return $queryBuilder->count();
    }

    /**
     * Deletes records identified by the query.
     *
     * @param QueryFilter|null $filter
     *
     * @return void
     * @throws QueryFilterInvalidParamException
     */
    public function deleteWhere(QueryFilter $filter = null): void
    {
        $this->getTransformer()->transformFilter($filter)->delete();
    }

    /**
     * @return OrmEntityTransformer
     */
    protected function getTransformer(): OrmEntityTransformer
    {
        if ($this->transformer === null) {
            /** @var Entity $ormInstance */
            $ormInstance = new $this->entityClass();
            $this->transformer = new OrmEntityTransformer($this->getTableName(), $ormInstance);
        }

        return $this->transformer;
    }
}
