<?php

namespace SeQura\Middleware\ORM\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Configuration
 *
 * @package SeQura\Middleware\ORM\Eloquent
 */
class Configuration extends Model
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
    public $fillable = ['type', 'index_1', 'index_2', 'data'];
}
