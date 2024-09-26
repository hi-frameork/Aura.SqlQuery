<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Postgres;

/**
 * Common code for RETURNING clauses.
 *
 * @package Aura.SqlQuery
 */
trait BuildReturningTrait
{
    /**
     * Builds the `RETURNING` clause of the statement.
     *
     * @param string[] $returning return these columns
     */
    public function buildReturning(array $returning): string
    {
        if (empty($returning)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'RETURNING' . $this->indentCsv($returning);
    }
}
