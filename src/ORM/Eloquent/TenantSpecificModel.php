<?php

namespace SeQura\Middleware\ORM\Eloquent;

use Illuminate\Database\Eloquent\Model;
use SeQura\Middleware\ORM\TableNameProvider;

/**
 * Class TenantSpecificModel
 *
 * @package SeQura\Middleware\ORM\Eloquent
 */
abstract class TenantSpecificModel extends Model
{
    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return TableNameProvider::getInstance()->getTableName($this->table);
    }
}
