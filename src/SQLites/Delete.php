<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\SQLite;

use Aura\SqlQuery\Common;

/**
 * An object for Sqlite DELETE queries.
 *
 * @package Aura.SqlQuery
 */
class Delete extends Common\Delete implements Common\OrderByInterface, Common\LimitOffsetInterface
{
    use Common\LimitOffsetTrait;

    protected function build(): string
    {
        return parent::build()
            . $this->builder->buildLimitOffset($this->getLimit(), $this->offset);
    }

    public function orderBy(array $spec): self
    {
        return $this->addOrderBy($spec);
    }
}
