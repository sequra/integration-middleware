<?php

namespace SeQura\Middleware\ORM\Transformers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryCondition;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\Utility\IndexHelper;

/**
 * Class EloquentTransformer
 *
 * @package SeQura\Middleware\ORM\Transformers
 */
class EloquentTransformer
{
    protected string $eloquentClassName = '';
    protected Entity $ormInstance;
    protected array $indexMap;

    /**
     * EloquentUtility constructor.
     *
     * @param string $eloquentClassName
     * @param Entity $ormInstance
     */
    public function __construct(string $eloquentClassName, Entity $ormInstance)
    {
        $this->eloquentClassName = $eloquentClassName;
        $this->ormInstance = $ormInstance;
        $this->indexMap = IndexHelper::mapFieldsToIndexes($ormInstance);
    }

    /**
     * Returns table name.
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->getEloquentInstance()->getTable();
    }

    /**
     * Return Eloquent instance.
     *
     * @return Model
     */
    protected function getEloquentInstance(): Model
    {
        /** @var Model $model */
        $model = new $this->eloquentClassName;

        return $model;
    }

    /**
     * Transforms query to Eloquent query builder.
     *
     * @param QueryFilter|null $filter
     *
     * @return Builder
     *
     * @throws QueryFilterInvalidParamException
     */
    public function transformFilter(?QueryFilter $filter): Builder
    {
        $queryBuilder = $this->getWhereBuilder($filter);
        if ($filter && $filter->getOrderByColumn()) {
            $this->addOrderBy($queryBuilder, $filter);
        }

        return $queryBuilder;
    }

    /**
     * Add where conditions from query to the Eloquent query builder.
     *
     * @param QueryFilter|null $filter
     *
     * @return Builder
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getWhereBuilder(?QueryFilter $filter): Builder
    {
        $queryBuilder = $this->getEntityTypeQuery();
        if ($filter === null) {
            return $queryBuilder;
        }

        foreach ($filter->getConditions() as $condition) {
            if ($condition->getColumn() === 'id') {
                $queryBuilder->where('id', $condition->getValue());
                continue;
            }

            if (!array_key_exists($condition->getColumn(), $this->indexMap)) {
                throw new QueryFilterInvalidParamException(
                    __('Field %s is not indexed!', [$condition->getColumn()])
                );
            }

            $queryBuilder = $this->addCondition($queryBuilder, $condition);
        }

        if ($filter->getLimit()) {
            $queryBuilder->offset($filter->getOffset());
            $queryBuilder->take($filter->getLimit());
        }

        return $queryBuilder;
    }

    /**
     * Returns query builder with where condition for orm entity type.
     *
     * @return Builder
     */
    public function getEntityTypeQuery(): Builder
    {
        return $this->newQuery()->where('type', $this->ormInstance->getConfig()->getType());
    }

    /**
     * Returns query builder.
     *
     * @return Builder
     */
    public function newQuery(): Builder
    {
        return $this->getEloquentInstance()->newQuery();
    }

    /**
     * Adds a single AND condition to SELECT query.
     *
     * @param Builder $queryBuilder Eloquent query builder.
     * @param QueryCondition $condition SeQura query condition.
     *
     * @return Builder Updated eloquent query builder.
     */
    private function addCondition(Builder $queryBuilder, QueryCondition $condition): Builder
    {
        $isChainOrOperator = $condition->getChainOperator() === 'OR';
        $columnName = $this->resolveColumn($condition->getColumn());
        $conditionValue = IndexHelper::castFieldValue($condition->getValue(), $condition->getValueType());

        return match ($condition->getOperator()) {
            Operators::NULL => $isChainOrOperator ? $queryBuilder->orWhereNull($columnName)
                : $queryBuilder->whereNull($columnName),
            Operators::NOT_NULL => $isChainOrOperator ? $queryBuilder->orWhereNotNull($columnName)
                : $queryBuilder->whereNotNull($columnName),
            Operators::IN => $isChainOrOperator ? $queryBuilder->orWhereIn($columnName, $conditionValue)
                : $queryBuilder->whereIn($columnName, $conditionValue),
            Operators::NOT_IN => $isChainOrOperator ? $queryBuilder->orWhereNotIn($columnName, $conditionValue)
                : $queryBuilder->whereNotIn($columnName, $conditionValue),
            default => $isChainOrOperator ? $queryBuilder->orWhere($columnName, $condition->getOperator(),
                $conditionValue)
                : $queryBuilder->where($columnName, $condition->getOperator(), $conditionValue),
        };
    }

    /**
     * Returns index mapped to given property.
     *
     * @param string $property Property name.
     *
     * @return string|null Index column in SeQura entity table, or null if it doesn't exist.
     */
    public function resolveColumn(string $property): ?string
    {
        if (array_key_exists($property, $this->indexMap)) {
            return 'index_' . $this->indexMap[$property];
        }

        return null;
    }

    /**
     * Adds order by to the query builder.
     *
     * @param Builder $builder
     * @param QueryFilter $filter
     *
     * @return Builder
     *
     * @throws QueryFilterInvalidParamException
     */
    private function addOrderBy(Builder $builder, QueryFilter $filter): Builder
    {
        $orderByColumn = $filter->getOrderByColumn();
        $indexedColumn = $orderByColumn === 'id' ? 'id' : $this->resolveColumn($orderByColumn);
        if ($indexedColumn === null) {
            throw new QueryFilterInvalidParamException(
                __('Unknown or not indexed OrderBy column %s', [$orderByColumn])
            );
        }

        $builder->orderBy($indexedColumn, $filter->getOrderDirection());


        return $builder;
    }

    /**
     * Prepares data for inserting a new record or updating an existing one.
     *
     * @param Entity $entity SeQura entity object.
     *
     * @return array Prepared entity array.
     *
     * @throws JsonException
     */
    public function prepareDataForInsertOrUpdate(Entity $entity): array
    {
        $preparedEntity = [];
        $preparedEntity['data'] = $this->serializeData($entity);
        $indexes = IndexHelper::transformFieldsToIndexes($entity);

        foreach ($indexes as $index => $value) {
            $indexField = 'index_' . $index;
            $preparedEntity[$indexField] = $value;
        }

        return $preparedEntity;
    }

    /**
     * Serializes SeQura entity to string.
     *
     * @param Entity $entity SeQura entity object to be serialized
     *
     * @return string Serialized entity
     *
     * @throws JsonException
     */
    public function serializeData(Entity $entity): string
    {
        return json_encode($entity->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Transforms an array of records to an array of SeQura entities.
     *
     * @param array $records Array of records.
     *
     * @return Entity[] Array of SeQura entities.
     *
     * @throws JsonException
     */
    public function toOrmCollection(array $records): array
    {
        $entities = [];
        foreach ($records as $record) {
            $entities[] = $this->toOrmEntity($record);
        }

        return $entities;
    }

    /**
     * Transforms record to SeQura entity.
     *
     * @param array $record Database record.
     *
     * @return Entity SeQura entity.
     *
     * @throws JsonException
     */
    public function toOrmEntity(array $record): Entity
    {
        $jsonEntity = json_decode($record['data'], true, 512, JSON_THROW_ON_ERROR);
        if (array_key_exists('class_name', $jsonEntity)) {
            $entity = new $jsonEntity['class_name'];
        } else {
            $entity = clone($this->ormInstance);
        }

        /** @var Entity $entity */
        $entity->inflate($jsonEntity);
        if (!empty($record['id'])) {
            $entity->setId($record['id']);
        }

        return $entity;
    }
}
