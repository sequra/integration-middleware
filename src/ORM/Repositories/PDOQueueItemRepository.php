<?php

namespace SeQura\Middleware\ORM\Repositories;

use Illuminate\Support\Facades\DB;
use JsonException;
use PDO;
use PDOStatement;
use RuntimeException;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class PDOQueueItemRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class PDOQueueItemRepository extends QueueItemRepository
{
    protected const CONNECTION_NAME = 'mysql';

    /**
     * Saves or updates queue item with additional condition.
     *
     * @param QueueItem $queueItem
     * @param array $additionalWhere
     *
     * @return int
     *
     * @throws QueryFilterInvalidParamException
     * @throws QueueItemSaveException
     * @throws JsonException
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = []): int
    {
        if ($queueItem->getId() === null) {
            return $this->save($queueItem);
        }

        if ($this->count($this->buildFilter($queueItem, $additionalWhere)) !== 1) {
            throw new QueueItemSaveException("Can not update queue item with id {$queueItem->getId()}. Additional where conditions not met");
        }

        $this->update($queueItem);

        return $queueItem->getId();
    }

    /**
     * Saves queue item entity.
     *
     * @param Entity $entity
     *
     * @return int
     *
     * @throws JsonException
     */
    public function save(Entity $entity): int
    {
        $data = $this->prepareRawData($entity);
        $insertStatement = $this->getBaseInsertStatement();
        $pdo = $this->getPdo();
        $statement = $pdo->prepare($insertStatement);
        $this->bindValues($statement, $data);
        $this->executeStatement($statement);
        $id = (int)$pdo->lastInsertId();
        $entity->setId($id);

        return $id;
    }

    /**
     * Executes queue item update.
     *
     * @param Entity $entity
     * @return bool
     *
     * @throws JsonException
     */
    public function update(Entity $entity): bool
    {
        $data = $this->prepareRawData($entity);
        $updateStatement = $this->getBaseUpdateStatement($entity);
        $pdo = $this->getPdo();
        $statement = $pdo->prepare($updateStatement);
        $this->bindValues($statement, $data);

        return $this->executeStatement($statement);
    }

    /**
     * Builds an additional where filter.
     *
     * @param QueueItem $queueItem
     * @param array $additionalWhere
     *
     * @return QueryFilter
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function buildFilter(QueueItem $queueItem, array &$additionalWhere): QueryFilter
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $queueItem->getId());

        foreach ($additionalWhere as $name => $value) {
            if ($value === null) {
                $filter->where($name, Operators::NULL);
            } else {
                $filter->where($name, Operators::EQUALS, $value);
            }
        }

        return $filter;
    }

    /**
     * Prepares raw data for insert or update.
     *
     * @param Entity $entity
     *
     * @return array
     *
     * @throws JsonException
     */
    protected function prepareRawData(Entity $entity): array
    {
        $data = $this->getTransformer()->prepareDataForInsertOrUpdate($entity);
        $data['type'] = $entity->getConfig()->getType();
        if (isset($data['id'])) {
            unset($data['id']);
        }

        return $data;
    }

    /**
     * Provides base insert statement.
     *
     * @return string
     */
    protected function getBaseInsertStatement(): string
    {
        return 'INSERT INTO ' . static::QUEUE_TABLE_NAME .
            ' (type, data, index_1, index_2, index_3, index_4, index_5, index_6, index_7, index_8) ' .
            ' VALUES (:type, :data, :index_1, :index_2, :index_3, :index_4, :index_5, :index_6, :index_7, :index_8)';
    }

    /**
     * Provides PDO.
     *
     * @return PDO
     */
    protected function getPdo(): PDO
    {
        $pdo = DB::connection(self::CONNECTION_NAME)->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    /**
     * Binds record values to PDO statement.
     *
     * @param bool | PDOStatement $statement
     * @param array $data
     */
    protected function bindValues(bool|PDOStatement $statement, array &$data): void
    {
        if ($statement === false) {
            throw new RuntimeException('Invalid PDO statement.');
        }

        $statement->bindValue('type', $data['type']);
        $statement->bindValue('data', $data['data']);
        $statement->bindValue('index_1', $data['index_1']);
        $statement->bindValue('index_2', $data['index_2']);
        $statement->bindValue('index_3', $data['index_3']);
        $statement->bindValue('index_4', $data['index_4']);
        $statement->bindValue('index_5', $data['index_5']);
        $statement->bindValue('index_6', $data['index_6']);
        $statement->bindValue('index_7', $data['index_7']);
        $statement->bindValue('index_8', $data['index_8']);
    }

    /**
     * Executes statement.
     *
     * @param PDOStatement $statement
     *
     * @return bool
     */
    protected function executeStatement(PDOStatement $statement): bool
    {
        $result = $statement->execute();
        if ($result === false) {
            throw new RuntimeException('SQL statement has failed.');
        }

        return true;
    }

    /**
     * Provides base update statement.
     *
     * @param Entity $entity
     *
     * @return string
     */
    protected function getBaseUpdateStatement(Entity $entity): string
    {
        $updateStatement = 'UPDATE ' . static::QUEUE_TABLE_NAME . ' SET ';
        $updateStatement .= 'type=:type,';
        $updateStatement .= 'data=:data,';
        $updateStatement .= 'index_1=:index_1,';
        $updateStatement .= 'index_2=:index_2,';
        $updateStatement .= 'index_3=:index_3,';
        $updateStatement .= 'index_4=:index_4,';
        $updateStatement .= 'index_5=:index_5,';
        $updateStatement .= 'index_6=:index_6,';
        $updateStatement .= 'index_7=:index_7,';
        $updateStatement .= 'index_8=:index_8';
        $updateStatement .= ' WHERE id = ' . $entity->getId();

        return $updateStatement;
    }
}
