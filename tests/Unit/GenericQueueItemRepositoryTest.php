<?php

namespace SeQura\Middleware\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use SeQura\Core\Infrastructure\Serializer\Concrete\JsonSerializer;
use SeQura\Core\Infrastructure\Serializer\Serializer;
use SeQura\Core\Infrastructure\Utility\TimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use SeQura\Core\Tests\Infrastructure\Common\TestServiceRegister;
use SeQura\Core\Tests\Infrastructure\ORM\AbstractGenericQueueItemRepositoryTest;
use SeQura\Middleware\Tests\CreatesApplication;
use SeQura\Middleware\Tests\Unit\Repository\TestQueueItemRepository;

/**
 * Class GenericBaseRepositoryTest
 *
 * @package SeQura\Middleware\Tests\Unit
 */
class GenericQueueItemRepositoryTest extends AbstractGenericQueueItemRepositoryTest
{
    use DatabaseMigrations, CreatesApplication;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->createApplication();

        parent::setUp();

        TestQueueItemRepository::createTestEntityTable();

        new TestServiceRegister(
            [
                TimeProvider::class => function () {
                    return new TestTimeProvider();
                },
                Serializer::class => function () {
                    return new JsonSerializer();
                },
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getQueueItemEntityRepositoryClass(): string
    {
        return TestQueueItemRepository::class;
    }

    /**
     * @inheritDoc
     */
    public function cleanUpStorage(): void
    {
        $this->tearDownAfterClass();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        Schema::dropIfExists(TestQueueItemRepository::TABLE_NAME);
    }
}
