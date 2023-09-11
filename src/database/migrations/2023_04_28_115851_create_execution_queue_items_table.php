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
        //        index_8 - parentId
        'status' => ['type', 'index_1'],
        'context' => ['type', 'index_4'],
        'lastUpdateTimestamp' => ['id', 'type', 'index_7'],
        'statusQueuePriority' => ['index_1', 'index_3', 'index_8'],
    ];

    public const TABLE_NAME = 'execution_queue_items';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(static::TABLE_NAME, static function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->longText('data');
            for ($i = 1; $i <= 9; $i++) {
                $table->string("index_$i")->nullable();
            }

            foreach (static::INDEXED_COLUMNS as $name => $column) {
                $table->index($column, $name);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(static::TABLE_NAME);
    }
};
