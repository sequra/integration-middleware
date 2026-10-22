<?php

namespace SeQura\Middleware\Service\BusinessLogic;

use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;

/**
 * Class DisconnectService
 *
 * Default integration side of a full disconnect. The core's full disconnect already deletes the connection and all
 * of the store's seQura data, so there is nothing left to remove here. A host that keeps its own platform data
 * (scripts, webhooks, users) registers its own DisconnectServiceInterface to clean it up.
 *
 * @package SeQura\Middleware\Service\BusinessLogic
 */
class DisconnectService implements DisconnectServiceInterface
{
    /**
     * @inheritDoc
     */
    public function disconnect(): void
    {
    }
}
