<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const TABLE_NAME = 'configurations';
    private const INDEXED_COLUMNS = [
        'type',
        'index_1',
        'index_2',
        ['type', 'index_1'],
        ['type', 'index_2'],
        ['type', 'index_1', 'index_2'],
    ];

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
            $table->string('index_1');
            $table->string('index_2')->nullable();
            $table->mediumText('data');

            foreach (static::INDEXED_COLUMNS as $column) {
                $table->index($column);
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
