<?php

namespace SeQura\Middleware\ORM\Transformers;

use Illuminate\Database\Eloquent\Builder;
use SeQura\Core\Infrastructure\Configuration\ConfigurationManager;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Middleware\Http\Exceptions\EmptyContextException;

/**
 * Class ContextAwareTransformer
 *
 * @package SeQura\Middleware\ORM\Transformers
 */
class ContextAwareTransformer extends EloquentTransformer
{
    /**
     * @var ConfigurationManager
     */
    protected ConfigurationManager $configService;
    /**
     * @var string
     */
    protected string $tableName;

    /**
     * EloquentUtility constructor.
     *
     * @param string $eloquentClassName
     * @param Entity $ormInstance
     * @param string $tableName
     */
    public function __construct(string $eloquentClassName, Entity $ormInstance, string $tableName)
    {
        parent::__construct($eloquentClassName, $ormInstance);
        $this->tableName = $tableName;
    }

    /**
     * Returns query builder with where condition for orm entity type.
     *
     * @return Builder
     */
    public function getEntityTypeQuery(): Builder
    {
        return $this->newQuery()->from($this->tableName);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataForInsertOrUpdate(Entity $entity): array
    {
        if (empty($this->getConfigService()->getContext())) {
            throw new EmptyContextException(__('Empty context for a context-specific entity!'));
        }

        $preparedEntity = parent::prepareDataForInsertOrUpdate($entity);

        $preparedEntity['context'] = $this->getConfigService()->getContext();

        return $preparedEntity;
    }

    /**
     * @inheritDoc
     */
    protected function getWhereBuilder(QueryFilter|null $filter): Builder
    {
        if (empty($this->getConfigService()->getContext())) {
            throw new EmptyContextException(__('Empty context for a context-specific entity!'));
        }

        $preparedBuilder = parent::getWhereBuilder($filter);

        $preparedBuilder->where('context', $this->getConfigService()->getContext());

        return $preparedBuilder;
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
