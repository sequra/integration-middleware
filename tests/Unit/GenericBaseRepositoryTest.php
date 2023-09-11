<?php

namespace SeQura\Middleware\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use SeQura\Core\Tests\Infrastructure\ORM\AbstractGenericStudentRepositoryTest;
use SeQura\Middleware\Tests\CreatesApplication;
use SeQura\Middleware\Tests\Unit\Repository\TestRepository;

/**
 * Class GenericBaseRepositoryTest
 *
 * @package SeQura\Middleware\Tests\Unit
 */
class GenericBaseRepositoryTest extends AbstractGenericStudentRepositoryTest
{
    use DatabaseMigrations, CreatesApplication;

    public function getStudentEntityRepositoryClass(): string
    {
        return TestRepository::class;
    }

    /**
     * @inheritDoc
     */
    public function cleanUpStorage(): void
    {
        TestRepository::dropTestEntityTable();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->createApplication();

        parent::setUp();

        TestRepository::createTestEntityTable();
    }
}
