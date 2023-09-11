<?php

namespace SeQura\Middleware\ORM\Repositories;

use SeQura\Middleware\ORM\Eloquent\Process;

/**
 * Class ProcessRepository
 *
 * @package SeQura\Middleware\ORM\Repositories
 */
class ProcessRepository extends BaseRepository
{
    /**
     * @inheritDoc
     */
    protected function getEloquentModelClassName(): string
    {
        return Process::class;
    }
}
