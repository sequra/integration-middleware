<?php

namespace SeQura\Middleware\Service\BusinessLogic;

use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData;
use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration;
use SeQura\Core\BusinessLogic\DataAccess\PromotionalWidgets\Entities\WidgetSettings;
use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\Integration\Disconnect\DisconnectServiceInterface;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;

class DisconnectService implements DisconnectServiceInterface
{
    /**
     * @inheritDoc
     *
     * @return void
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function disconnect(): void
    {
        $this->deleteConnectionData();
        $this->deleteStatisticalData();
        $this->deleteCountryConfigurationData();
        $this->deleteWidgetSettings();
    }

    /**
     * Deletes connection data for the current context.
     *
     * @return bool
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function deleteConnectionData(): bool
    {
        $connectionRepository = RepositoryRegistry::getRepository(ConnectionData::class);

        $connectionData = $connectionRepository->selectOne();
        if ($connectionData !== null) {
            return $connectionRepository->delete($connectionData);
        }

        return false;
    }

    /**
     * Deletes statistical data for the current context.
     *
     * @return bool
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function deleteStatisticalData(): bool
    {
        $statisticsRepository = RepositoryRegistry::getRepository(StatisticalData::class);

        $statisticalData = $statisticsRepository->selectOne();
        if ($statisticalData !== null) {
            return $statisticsRepository->delete($statisticalData);
        }

        return false;
    }

    /**
     * Deletes country configuration data for the current context.
     *
     * @return bool
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function deleteCountryConfigurationData(): bool
    {
        $countryConfigRepository = RepositoryRegistry::getRepository(CountryConfiguration::class);

        $countryConfiguration = $countryConfigRepository->selectOne();
        if ($countryConfiguration !== null) {
            return $countryConfigRepository->delete($countryConfiguration);
        }

        return false;
    }

    /**
     * Deletes widget settings data for the current context.
     *
     * @return bool
     *
     * @throws \SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function deleteWidgetSettings(): bool
    {
        $widgetSettingsRepository = RepositoryRegistry::getRepository(WidgetSettings::class);

        $widgetSettings = $widgetSettingsRepository->selectOne();
        if ($widgetSettings !== null) {
            return $widgetSettingsRepository->delete($widgetSettings);
        }

        return false;
    }
}
