<?php

namespace SeQura\Middleware\ORM\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tenant
 *
 * @package SeQura\Middleware\ORM\Eloquent
 */
class Tenant extends Model
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
    protected $fillable = ['id', 'type', 'index_1', 'data'];
}
