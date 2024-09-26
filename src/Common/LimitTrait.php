<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * A trait for LIMIT clauses.
 *
 * @package Aura.SqlQuery
 */
trait LimitTrait
{
    /**
     * The LIMIT value.
     */
    protected int $limit = 0;

    /**
     * Sets a limit count on the query.
     *
     * @param int $limit the number of rows to select
     */
    public function limit(int $limit): self
    {
        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * Returns the LIMIT value.
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
