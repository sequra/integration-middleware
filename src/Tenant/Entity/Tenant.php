<?php

namespace SeQura\Middleware\Tenant\Entity;

use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class Tenant
 *
 * @package SeQura\Middleware\Tenant\Entity
 */
class Tenant extends Entity
{
    public const CLASS_NAME = __CLASS__;

    protected $fields = [
        'id',
        'context'
    ];

    public string $context = '';

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('context');

        return new EntityConfiguration($indexMap, 'Tenant');
    }
}
