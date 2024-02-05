<?php

namespace SeQura\Middleware\Tests\Unit\Repository;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SeQura\Middleware\ORM\Repositories\QueueItemRepository;

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

    private const INDEXED_COLUMNS = [
        'status' => ['type', 'index_1'],
        'context' => ['type', 'index_4'],
        'lastUpdateTimestamp' => ['id', 'type', 'index_7'],
        'statusQueuePriority' => ['index_1', 'index_3', 'index_8'],
    ];

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
                    for ($i = 1; $i <= 9; $i++) {
                        $table->string("index_$i")->nullable();
                    }
                    $table->bigInteger('parent_id')->nullable();
                    $table->string('status', 32);
                    $table->string('context')->nullable();
                    $table->longText('serialized_task')->nullable();
                    $table->string('queue_name');
                    $table->integer('last_execution_progress')->nullable();
                    $table->integer('progress_base_points')->nullable();
                    $table->integer('retries')->nullable();
                    $table->text('failure_description')->nullable();
                    $table->integer('create_time')->nullable();
                    $table->integer('start_time')->nullable();
                    $table->integer('earliest_start_time')->nullable();
                    $table->integer('queue_time')->nullable();
                    $table->integer('last_update_time')->nullable();
                    $table->integer('finish_time')->nullable();
                    $table->integer('priority')->nullable();

                    foreach (static::INDEXED_COLUMNS as $name => $column) {
                        $table->index($column, $name);
                    }
                }
            );
        }
    }
}
