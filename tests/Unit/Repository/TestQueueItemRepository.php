<?php

namespace SeQura\Middleware\Tests\Unit\Repository;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SeQura\Middleware\Model\Repository\QueueItemRepository;

/**
 * Class TestQueueItemRepository
 *
 * @package SeQura\Middleware\Tests\Unit\Repository
 */
class TestQueueItemRepository extends QueueItemRepository
{
    /**
     * Name of the base entity table in database.
     */
    public const TABLE_NAME = 'execution_queue_test';

    /**
     * Creates test entity table.
     */
    public static function createTestEntityTable(): void
    {
        if (!Schema::hasTable(static::TABLE_NAME)) {
            Schema::create(
                static::TABLE_NAME,
                static function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('type');
                    $table->string('index_1')->nullable();
                    $table->string('index_2')->nullable();
                    $table->string('index_3')->nullable();
                    $table->string('index_4')->nullable();
                    $table->string('index_5')->nullable();
                    $table->string('index_6')->nullable();
                    $table->string('index_7')->nullable();
                    $table->string('index_8')->nullable();
                    $table->string('index_9')->nullable();
                    $table->text('data');
                }
            );
        }
    }
}
