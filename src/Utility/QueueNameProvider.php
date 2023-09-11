<?php

namespace SeQura\Middleware\Utility;

use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class QueueNameProvider
 *
 * @package SeQura\Middleware\Utility
 */
class QueueNameProvider implements QueueNameProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getQueueName(Task $task): string
    {
        return sprintf('%s_%s', $task::getClassName(), $this->getTenantId());
    }

    /**
     * Returns a hashed tenant id.
     *
     * @return string
     */
    private function getTenantId(): string
    {
        $context = ConfigurationManager::getInstance()->getContext();

        if ($context === '') {
            return $context;
        }

        return md5($context);
    }
}
