<?php

namespace SeQura\Middleware\Uninstall;

use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
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
        $this->dropTenantSpecificTables();
        $this->deleteTenantFromGlobalTable();
        $this->deleteDataFromGlobalTables();
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
