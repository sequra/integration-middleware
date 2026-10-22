<?php

namespace SeQura\Middleware\Uninstall;

use SeQura\Core\BusinessLogic\AdminAPI\AdminAPI;
use SeQura\Core\BusinessLogic\AdminAPI\Disconnect\Requests\DisconnectRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\Logger\LogContextData;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Middleware\ORM\Migrator\Migrator;
use SeQura\Middleware\Uninstall\Components\GlobalDataDeleter;
use SeQura\Middleware\Uninstall\Contracts\UninstallService as BaseService;
use Throwable;

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
     * Fully disconnects every connected deployment while the connection data still exists, which removes its store
     * integration from the seQura Merchant Portal and runs the integration's disconnect hook. A failure is logged
     * and does not stop the uninstallation, since the merchant has already removed the app.
     */
    protected function disconnectDeployments(): void
    {
        $storeId = ConfigurationManager::getInstance()->getContext();

        try {
            /** @var ConnectionData[] $connections */
            $connections = StoreContext::doWithStore($storeId, static function () {
                /** @var ConnectionService $connectionService */
                $connectionService = ServiceRegister::getService(ConnectionService::class);

                return $connectionService->getAllConnectionData();
            });
        } catch (Throwable $e) {
            Logger::logWarning(
                'Reading the connections failed during uninstall; continuing with local cleanup.',
                'Middleware',
                [new LogContextData('message', $e->getMessage())]
            );

            return;
        }

        foreach ($connections as $connection) {
            // AdminAPI logs a failure and answers with an error response instead of throwing.
            AdminAPI::get()->disconnect($storeId)->disconnect(new DisconnectRequest($connection->getDeployment(), true));
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
