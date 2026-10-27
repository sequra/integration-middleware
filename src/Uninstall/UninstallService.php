<?php

namespace SeQura\Middleware\Uninstall;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Requests\DisconnectRequest;
use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Middleware\ORM\Migrator\Migrator;
use SeQura\Middleware\Uninstall\Components\GlobalDataDeleter;
use SeQura\Middleware\Uninstall\Contracts\UninstallService as BaseService;

/**
 * Interface UninstallService
 *
 * @package SeQura\Middleware\Uninstall
 */
abstract class UninstallService implements BaseService
{
    /**
     * @inheritDoc
     */
    public function uninstall(): void
    {
        $this->disconnectDeployments();
        $this->dropTenantSpecificTables();
        $this->deleteTenantFromGlobalTable();
        $this->deleteDataFromGlobalTables();
    }

    /**
     * Disconnects every connected deployment while the connection data still exists, which removes its store
     * integration from the seQura Merchant Portal and runs the integration's disconnect hook. AdminAPI logs a
     * failure and answers with an error response instead of throwing, so a failure does not stop the uninstallation,
     * since the merchant has already removed the app.
     */
    protected function disconnectDeployments(): void
    {
        $storeId = (string)ConfigurationManager::getInstance()->getContext();
        if ($storeId === '') {
            return;
        }

        $onboardingData = AdminAPI::get()->connection($storeId)->getOnboardingData();
        if (!$onboardingData->isSuccessful()) {
            return;
        }

        // Only the last disconnect is full, so the store data is wiped and the disconnect hook runs once.
        $connections = $onboardingData->toArray()['connectionData'] ?? [];
        foreach ($connections as $index => $connection) {
            AdminAPI::get()->disconnect($storeId)->disconnect(
                new DisconnectRequest($connection['deployment'], $index === array_key_last($connections))
            );
        }
    }

    /**
     * Removes tenant specific tables
     */
    protected function dropTenantSpecificTables(): void
    {
        $this->getMigrator()->dropTenantSpecificTables();
    }

    /**
     * Removes the tenant from global tenant table
     */
    abstract protected function deleteTenantFromGlobalTable();

    /**
     * Removes the tenant from global tables with indexes
     */
    protected function deleteDataFromGlobalTables(): void
    {
        GlobalDataDeleter::delete(QueueItem::getClassName(), $this->getExcludedTaskTypes());

        $entity = ConfigEntity::getClassName();
        GlobalDataDeleter::delete($entity);
    }

    /**
     * Returns a migrator
     *
     * @return Migrator
     */
    protected function getMigrator(): Migrator
    {
        return Migrator::getInstance();
    }

    /**
     * Returns the types of tasks that should be excluded from deletion
     *
     * @return array
     */
    protected function getExcludedTaskTypes(): array
    {
        return [];
    }
}
