<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Common INSERT builder.
 *
 * @package Aura.SqlQuery
 */
class InsertBuilder extends Builder
{
    /**
     * Builds the INTO clause.
     *
     * @param string $into the INTO element
     */
    public function buildInto(string $into): string
    {
        return " INTO {$into}";
    }

    /**
     * Builds the inserted columns and values of the statement.
     *
     * @param array<string,string> $col_values the column names and values
     */
    public function buildValuesForInsert(array $col_values): string
    {
        return ' ('
            . $this->indentCsv(\array_keys($col_values))
            . \PHP_EOL . ') VALUES ('
            . $this->indentCsv(\array_values($col_values))
            . \PHP_EOL . ')';
    }

    /**
     * Builds the bulk-inserted columns and values of the statement.
     *
     * @param string[]            $col_order       the column names to insert, in order
     * @param array<int,string[]> $col_values_bulk the bulk-insert values, in the same order
     *                                             the column names
     */
    public function buildValuesForBulkInsert(array $col_order, array $col_values_bulk): string
    {
        $cols = '    (' . \implode(', ', $col_order) . ')';
        $vals = [];
        foreach ($col_values_bulk as $row_values) {
            $vals[] = '    (' . \implode(', ', $row_values) . ')';
        }
        return \PHP_EOL . $cols . \PHP_EOL
            . 'VALUES' . \PHP_EOL
            . \implode(',' . \PHP_EOL, $vals);
    }
}
