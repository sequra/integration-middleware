<?php

namespace SeQura\Middleware\ORM\Repositories;

use JsonException;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\QueueItemRepository as QueueItemRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\Utility\IndexHelper;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Middleware\ORM\Transformers\QueueItemEntityTransformer;

/**
 * Class QueueItemRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class QueueItemRepository extends BaseRepository implements QueueItemRepositoryInterface
{
    protected const TABLE_NAME = 'execution_queue_items';

    /**
     * Finds list of earliest queued queue items per queue for given priority.
     * Following list of criteria for searching must be satisfied:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be returned
     *      - Only queue items with given priority can be retrieved.
     *
     * @param int $priority Queue item priority priority.
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned
     *
     * @return QueueItem[] Found queue item list
     * @throws JsonException
     */
    public function findOldestQueuedItems($priority, $limit = 10): array
    {
        $ids = $this->getQueueIdsForExecution($priority, $limit);
        $query = $this->getTransformer()->newQuery();
        $records = $query->whereIn('id', $ids)
            ->orderBy('id')
            ->get()
            ->toArray();

        return !empty($records) ?
            $this->getTransformer()
                ->toOrmCollection(array_map(
                    static function ($r) {
                        return (array) $r;
                    },
                    $records
                ))
            : [];
    }

    /**
     * Creates or updates given queue item. If queue item id is not set, new queue item will be created otherwise
     * update will be performed.
     *
     * @param QueueItem $queueItem Item to save
     * @param array $additionalWhere List of key/value pairs that must be satisfied upon saving queue item. Key is
     *  queue item property and value is condition value for that property. Example for MySql storage:
     *  $storage->save($queueItem, array('status' => 'queued')) should produce query
     *  UPDATE queue_storage_table SET .... WHERE .... AND status => 'queued'
     *
     * @return int Id of saved queue item
     *
     * @throws QueueItemSaveException if queue item could not be saved
     * @throws QueryFilterInvalidParamException
     * @throws JsonException
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = []): int
    {
        if ($queueItem->getId() === null) {
            return $this->save($queueItem);
        }

        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $queueItem->getId());

        foreach ($additionalWhere as $name => $value) {
            if ($value === null) {
                $filter->where($name, Operators::NULL);
            } else {
                $filter->where($name, Operators::EQUALS, $value ?? '');
            }
        }

        /** @var QueueItem $item */
        $item = $this->selectOne($filter);
        if ($item === null) {
            throw new QueueItemSaveException(
                "Can not update queue item with id {$queueItem->getId()}. Item not found."
            );
        }

        $this->update($queueItem);

        return $queueItem->getId();
    }

    /**
     * @inheritDoc
     */
    public function batchStatusUpdate(array $ids, $status): void
    {
        if (empty($ids)) {
            return;
        }

        $lastUpdateTime = IndexHelper::castFieldValue($this->getTimeProvider()->getCurrentLocalTime(), 'integer');
        $this->getTransformer()->newQuery()
            ->whereIn('id', $ids)
            ->update([
                'status' => $status,
                'last_update_time' => $lastUpdateTime,
                $this->getTransformer()->resolveColumn('status') => $status,
                $this->getTransformer()->resolveColumn('lastUpdateTimestamp') => $lastUpdateTime,
            ]);
    }

    /**
     * @param int $priority
     * @param int $limit
     *
     * @return array
     */
    protected function getQueueIdsForExecution(int $priority, int $limit): array
    {
        $runningQueueNames = $this->getRunningQueueNames();
        $castedPriorityValue = IndexHelper::castFieldValue($priority, 'integer');

        // Do NOT use order by nor limit in this query since it is most performant without them, especially do not
        // use both order and limit in this query because index usage will be canceled and query will be slow.
        // Do NOT use limit here for because it can lead to results that are not correct (not oldest queue is returned)
        // since we can't use order here.
        // Since we only return ids here, limiting result set is not crucial since we group by queue and memory is the
        // main constraining factor.
        $query = $this->getTransformer()->newQuery();
        $query->selectRaw('min(id) as id')
            ->where($this->getTransformer()->resolveColumn('priority'), '=', $castedPriorityValue)
            ->where($this->getTransformer()->resolveColumn('status'), '=', QueueItem::QUEUED)
            ->groupBy([$this->getTransformer()->resolveColumn('queueName')]);

        if (!empty($runningQueueNames)) {
            $query->whereNotIn($this->getTransformer()->resolveColumn('queueName'), $runningQueueNames);
        }

        $result = $query->get()->toArray();
        $result = array_column($result, 'id');

        sort($result);

        return array_slice($result, 0, $limit);
    }

    /**
     * @return array
     */
    protected function getRunningQueueNames(): array
    {
        $query = $this->getTransformer()->newQuery();
        $query->select($this->getTransformer()->resolveColumn('queueName'))
            ->where($this->getTransformer()->resolveColumn('status'), '=', QueueItem::IN_PROGRESS);

        $result = $query->get()->toArray();

        return array_column($result, $this->getTransformer()->resolveColumn('queueName'));
    }

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return static::TABLE_NAME;
    }

    /**
     * @return QueueItemEntityTransformer
     */
    protected function getTransformer(): QueueItemEntityTransformer
    {
        if ($this->transformer === null) {
            /** @var Entity $ormInstance */
            $ormInstance = new $this->entityClass();
            $this->transformer = new QueueItemEntityTransformer($this->getTableName(), $ormInstance);
        }

        return $this->transformer;
    }

    /**
     * Provides time provider.
     *
     * @return TimeProvider
     */
    private function getTimeProvider(): TimeProvider
    {
        return ServiceRegister::getService(TimeProvider::CLASS_NAME);
    }
}
