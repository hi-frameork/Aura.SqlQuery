<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Postgres;

/**
 * An interface for RETURNING clauses.
 *
 * @package Aura.SqlQuery
 */
interface ReturningInterface
{
    /**
     * Adds returning columns to the query.
     *
     * Multiple calls to returning() will append to the list of columns, not
     * overwrite the previous columns.
     *
     * @param string[] $cols the column(s) to add to the query
     */
    public function returning(array $cols): self;
}
