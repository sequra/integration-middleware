<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEXED_COLUMNS = [
        //        index_1 - status
        //        index_2 - taskType
        //        index_3 - queueName
        //        index_4 - context
        //        index_5 - queueTime
        //        index_6 - lastExecutionProgress
        //        index_7 - lastUpdateTimestamp
        //        index_8 - priority
        'status' => ['type', 'index_1'],
        'context' => ['type', 'index_4'],
        'lastUpdateTimestamp' => ['id', 'type', 'index_7'],
        'statusQueuePriority' => ['index_1', 'index_3', 'index_8'],
    ];

    public const TABLE_NAME = 'execution_queue_items';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(static::TABLE_NAME, static function (Blueprint $table) {
            $table->bigIncrements('id');
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(static::TABLE_NAME);
    }
};
