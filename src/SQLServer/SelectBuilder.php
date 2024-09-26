<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\SQLServer;

use Aura\SqlQuery\Common;

/**
 * An object for Sqlsrv SELECT queries.
 *
 * @package Aura.SqlQuery
 */
class SelectBuilder extends Common\SelectBuilder
{
    /**
     * Override so that LIMIT equivalent will be applied by applyLimit().
     *
     * @param int $limit  ignored
     * @param int $offset ignored
     *
     * @see build()
     * @see applyLimit()
     */
    public function buildLimitOffset(int $limit, int $offset): string
    {
        return '';
    }

    /**
     * Modify the statement applying limit/offset equivalent portions to it.
     *
     * @param string $stm    the SQL statement
     * @param int    $limit  the LIMIT value
     * @param int    $offset the OFFSET value
     */
    public function applyLimit(string $stm, int $limit, int $offset): string
    {
        if (! $limit && ! $offset) {
            return $stm; // no limit or offset
        }

        // limit but no offset?
        if ($limit && ! $offset) {
            // use TOP in place
            return \preg_replace(
                '/^(SELECT( DISTINCT)?)/',
                "$1 TOP {$limit}",
                $stm,
            );
        }

        // both limit and offset. must have an ORDER clause to work; OFFSET is
        // a sub-clause of the ORDER clause. cannot use FETCH without OFFSET.
        return $stm . \PHP_EOL . "OFFSET {$offset} ROWS "
                    . "FETCH NEXT {$limit} ROWS ONLY";
    }
}
