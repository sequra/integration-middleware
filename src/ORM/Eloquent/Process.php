<?php

namespace SeQura\Middleware\ORM\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Process
 *
 * @package SeQura\Middleware\ORM\Eloquent
 */
class Process extends Model
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
    protected $fillable = ['type', 'index_1', 'data'];
}
