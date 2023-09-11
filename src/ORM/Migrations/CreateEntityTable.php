<?php

namespace SeQura\Middleware\ORM\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SeQura\Middleware\ORM\TableNameProvider;

class CreateEntityTable extends Migration
{
    public const TABLE = 'tenant_{id}_entities';
    protected const INDEX_COUNT = 10;

    protected static array $indexes = [
        'type' => ['type'],
        'index_1' => ['type', 'index_1'],
        'index_1_2' => ['type', 'index_1', 'index_2'],
    ];

    /**
     * Creates table with indexes.
     */
    public function up(): void
    {
        Schema::create(
            $this->getTableName(),
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type')->nullable(false);
                for ($i = 1; $i <= static::INDEX_COUNT; $i++) {
                    $table->string("index_{$i}")->nullable(true);
                }

                $table->mediumText('data');

                foreach (static::$indexes as $key => $index) {
                    $table->index($index, $key);
                }
            }
        );

    }

    /**
     * Drops table
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->getTableName(), static function (Blueprint $table) {
            foreach (static::$indexes as $key => $index) {
                $table->dropIndex($key);
            }
        });

        Schema::dropIfExists($this->getTableName());
    }

    /**
     * Retrieves table name
     *
     * @return string
     */
    protected function getTableName(): string
    {
        return TableNameProvider::getInstance()->getTableName(static::TABLE);
    }
}
