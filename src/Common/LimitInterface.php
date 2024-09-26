<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for LIMIT clauses.
 *
 * @package Aura.SqlQuery
 */
interface LimitInterface
{
    /**
     * Sets a limit count on the query.
     *
     * @param int $limit the number of rows to select
     */
    public function limit(int $limit): self;

    /**
     * Returns the LIMIT value.
     */
    public function getLimit(): int;
}
