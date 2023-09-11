<?php

namespace SeQura\Middleware;

use SeQura\Core\BusinessLogic\BootstrapComponent as BaseBootstrapComponent;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetSettings;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Order\Models\SeQuraOrder;
use SeQura\Core\BusinessLogic\Providers\QueueNameProvider\Contract\QueueNameProviderInterface;
use SeQura\Core\BusinessLogic\Utility\EncryptorInterface;
use SeQura\Core\Infrastructure\Configuration\ConfigEntity;
use SeQura\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Process;
use SeQura\Core\Infrastructure\TaskExecution\QueueItem;
use SeQura\Middleware\ORM\Repositories\ConfigurationRepository;
use SeQura\Middleware\ORM\Repositories\EntityRepository;
use SeQura\Middleware\ORM\Repositories\PDOQueueItemRepository;
use SeQura\Middleware\ORM\Repositories\ProcessRepository;
use SeQura\Middleware\ORM\Repositories\TenantRepository;
use SeQura\Middleware\Service\BusinessLogic\DisconnectService;
use SeQura\Middleware\Service\BusinessLogic\VersionService;
use SeQura\Middleware\Service\Infrastructure\LoggerService;
use SeQura\Middleware\Tenant\Entity\Tenant;
use SeQura\Middleware\Tenant\Service\TenantService;
use SeQura\Middleware\Utility\Encryptor;
use SeQura\Middleware\Utility\QueueNameProvider;

/**
 * Class BootstrapComponent
 *
 * @package SeQura\Middleware
 */
class BootstrapComponent extends BaseBootstrapComponent
{
    /**
     * @inheritDoc
     */
    protected static function initServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(
            ShopLoggerAdapter::class,
            static function () {
                return LoggerService::getInstance();
            }
        );

        ServiceRegister::registerService(
            Serializer::class,
            static function () {
                return new JsonSerializer();
            }
        );

        ServiceRegister::registerService(
            EncryptorInterface::class,
            static function () {
                return new Encryptor();
            }
        );

        ServiceRegister::registerService(
            QueueNameProviderInterface::class,
            static function () {
                return new QueueNameProvider();
            }
        );

        ServiceRegister::registerService(
            TenantService::class,
            static function () {
                return new TenantService(
                    RepositoryRegistry::getRepository(Tenant::getClassName())
                );
            }
        );

        ServiceRegister::registerService(
            VersionServiceInterface::class,
            static function () {
                return new VersionService();
            }
        );

        ServiceRegister::registerService(
            DisconnectServiceInterface::class,
            static function () {
                return new DisconnectService();
            }
        );
    }

    /**
     * @inheritDoc
     *
     * @throws RepositoryClassException
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        RepositoryRegistry::registerRepository(ConfigEntity::class, ConfigurationRepository::class);
        RepositoryRegistry::registerRepository(QueueItem::class, PDOQueueItemRepository::class);
        RepositoryRegistry::registerRepository(Process::class, ProcessRepository::class);
        RepositoryRegistry::registerRepository(Tenant::class, TenantRepository::class);
        RepositoryRegistry::registerRepository(SeQuraOrder::class, EntityRepository::class);
        RepositoryRegistry::registerRepository(ConnectionData::class, EntityRepository::class);
        RepositoryRegistry::registerRepository(CountryConfiguration::class, EntityRepository::class);
        RepositoryRegistry::registerRepository(StatisticalData::class, EntityRepository::class);
        RepositoryRegistry::registerRepository(WidgetSettings::class, EntityRepository::class);
        RepositoryRegistry::registerRepository(GeneralSettings::class, EntityRepository::class);
    }
}
