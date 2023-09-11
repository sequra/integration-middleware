<?php

namespace SeQura\Middleware\ORM\Migrator;

use Exception;
use Illuminate\Database\Migrations\Migration;
use SeQura\Core\Infrastructure\Logger\Logger;
use SeQura\Core\Infrastructure\Singleton;
use SeQura\Middleware\ORM\Migrations\CreateEntityTable;

/**
 * Class Migrator
 *
 * @package SeQura\Middleware\ORM\Migrator
 */
class Migrator extends Singleton
{
    protected static $instance;
    private const TENANT_SPECIFIC_TABLE_MIGRATIONS = [
        CreateEntityTable::class,
    ];

    /**
     * Executes migrations that create tenant specific tables.
     */
    public function createTenantSpecificTables(): void
    {
        foreach ($this->getTenantSpecificTableMigrations() as $migration) {
            try {
                /** @var Migration $migrationScript */
                $migrationScript = new $migration;
                $migrationScript->up();
            } catch (Exception $e) {
                Logger::logError("Failed to create tenant specific table [$migration] because: " . $e->getMessage());
            }
        }
    }

    /**
     * Executes migrations that drop tenant specific tables.
     */
    public function dropTenantSpecificTables(): void
    {
        foreach ($this->getTenantSpecificTableMigrations() as $migration) {
            try {
                /** @var Migration $migrationScript */
                $migrationScript = new $migration;
                $migrationScript->down();
            } catch (Exception $e) {
                Logger::logError("Failed to drop tenant specific table [$migration] because: " . $e->getMessage());
            }
        }
    }

    /**
     * Retrieves the list of tenant specific table migrations.
     *
     * @return array
     */
    protected function getTenantSpecificTableMigrations(): array
    {
        return self::TENANT_SPECIFIC_TABLE_MIGRATIONS;
    }
}
