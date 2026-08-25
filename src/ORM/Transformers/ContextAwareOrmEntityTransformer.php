<?php

namespace SeQura\Middleware\ORM\Transformers;

use Illuminate\Database\Query\Builder;
use JsonException;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Middleware\Http\Exceptions\EmptyContextException;

/**
 * Class ContextAwareOrmEntityTransformer
 *
 * Transformer of entities whose table is shared by every tenant, where a `context` column tells the rows of one
 * tenant from another. It carries that column through both directions: written on insert and update, and
 * required in the where clause of every read, so one tenant can never resolve another one's row.
 *
 * @package SeQura\Middleware\ORM\Transformers
 */
class ContextAwareOrmEntityTransformer extends OrmEntityTransformer
{
    /**
     * @var ConfigurationManager
     */
    protected ConfigurationManager $configService;

    /**
     * @inheritDoc
     *
     * @throws EmptyContextException
     */
    public function getEntityTypeQuery(): Builder
    {
        return parent::getEntityTypeQuery()->where('context', $this->getContext());
    }

    /**
     * @inheritDoc
     *
     * @throws EmptyContextException
     * @throws JsonException
     */
    public function prepareDataForInsertOrUpdate(Entity $entity): array
    {
        $preparedEntity = parent::prepareDataForInsertOrUpdate($entity);

        $preparedEntity['context'] = $this->getContext();

        return $preparedEntity;
    }

    /**
     * Returns the context of the current tenant.
     *
     * @return string
     *
     * @throws EmptyContextException
     */
    protected function getContext(): string
    {
        $context = (string)$this->getConfigService()->getContext();

        if ($context === '') {
            throw new EmptyContextException(__('Empty context for a context-specific entity!'));
        }

        return $context;
    }

    /**
     * Returns an instance of configuration service.
     *
     * @return ConfigurationManager
     */
    protected function getConfigService(): ConfigurationManager
    {
        if (!isset($this->configService)) {
            $this->configService = ServiceRegister::getService(ConfigurationManager::CLASS_NAME);
        }

        return $this->configService;
    }
}
