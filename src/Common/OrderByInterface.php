<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for ORDER BY clauses.
 *
 * @package Aura.SqlQuery
 */
interface OrderByInterface
{
    /**
     * Adds a column order to the query.
     *
     * @param string[] $spec the columns and direction to order by
     */
    public function orderBy(array $spec): self;
}
