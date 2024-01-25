<?php

namespace SeQura\Middleware\ORM\Transformers;

use JsonException;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemEntityTransformer
 *
 * @package SeQura\Middleware\ORM\Transformers
 */
class QueueItemEntityTransformer extends OrmEntityTransformer
{
    /**
     * @param array $record
     *
     * @return Entity
     */
    public function toOrmEntity(array $record): Entity
    {
        $item = new QueueItem();
        $item->setId((int)$record['id']);
        $item->setParentId(!empty($record['parent_id']) ? (int)$record['parent_id'] : null);
        $item->setStatus($record['status']);
        $item->setContext($record['context']);
        $item->setSerializedTask($record['serialized_task']);
        $item->setQueueName($record['queue_name']);
        $item->setLastExecutionProgressBasePoints(
            !empty($record['last_execution_progress']) ? (int)$record['last_execution_progress'] : 0
        );
        $item->setProgressBasePoints(
            !empty($record['progress_base_points']) ? (int)$record['progress_base_points'] : 0
        );
        $item->setRetries(!empty($record['retries']) ? (int)$record['retries'] : 0);
        $item->setFailureDescription($record['failure_description']);
        $item->setCreateTimestamp($record['create_time']);
        $item->setStartTimestamp(($record['start_time']));
        $item->setEarliestStartTimestamp($record['earliest_start_time']);
        $item->setQueueTimestamp($record['queue_time']);
        $item->setLastUpdateTimestamp($record['last_update_time']);
        $item->setFinishTimestamp($record['finish_time']);
        $item->setPriority((int)$record['priority']);

        return $item;
    }

    /**
     * @param QueueItem $entity
     *
     * @return array
     *
     * @throws JsonException
     */
    public function prepareDataForInsertOrUpdate(Entity $entity): array
    {
        $preparedEntity = parent::prepareDataForInsertOrUpdate($entity);
        unset($preparedEntity['data']);

        $preparedEntity['parent_id'] = $entity->getParentId();
        $preparedEntity['status'] = $entity->getStatus();
        $preparedEntity['context'] = $entity->getContext();
        $preparedEntity['serialized_task'] = $entity->getSerializedTask();
        $preparedEntity['queue_name'] = $entity->getQueueName();
        $preparedEntity['last_execution_progress'] = $entity->getLastExecutionProgressBasePoints();
        $preparedEntity['progress_base_points'] = $entity->getProgressBasePoints();
        $preparedEntity['retries'] = $entity->getRetries();
        // Limit failure description to the value somewhat smaller than max DB column size
        $preparedEntity['failure_description'] = substr($entity->getFailureDescription(), 0, 64000);
        $preparedEntity['create_time'] = $entity->getCreateTimestamp();
        $preparedEntity['start_time'] = $entity->getStartTimestamp();
        $preparedEntity['earliest_start_time'] = $entity->getEarliestStartTimestamp();
        $preparedEntity['queue_time'] = $entity->getQueueTimestamp();
        $preparedEntity['last_update_time'] = $entity->getLastUpdateTimestamp();
        $preparedEntity['finish_time'] = $entity->getFinishTimestamp();
        $preparedEntity['priority'] = $entity->getPriority();

        return $preparedEntity;
    }
}
