<?php

namespace SeQura\Middleware\ORM\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ExecutionQueueItem
 *
 * @package SeQura\Middleware\ORM\Eloquent
 */
class ExecutionQueueItem extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['type', 'index_1', 'index_2', 'index_3', 'index_4', 'index_5', 'index_6', 'index_7', 'index_8', 'index_9', 'data'];
}
