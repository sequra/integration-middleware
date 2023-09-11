<?php

namespace SeQura\Middleware\Service\BusinessLogic;

use SeQura\Core\BusinessLogic\Domain\Integration\Version\VersionServiceInterface;
use SeQura\Core\BusinessLogic\Domain\Version\Models\Version;

/**
 * Class VersionInfoService
 *
 * @package SeQura\Middleware\Service\BusinessLogic
 */
class VersionService implements VersionServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getVersion(): Version
    {
        return new Version(
            '1.0.0',
            '1.0.0',
            ''
        );
    }
}
