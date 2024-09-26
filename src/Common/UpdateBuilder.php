<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Common UPDATE builder.
 *
 * @package Aura.SqlQuery
 */
class UpdateBuilder extends Builder
{
    /**
     * Builds the table portion of the UPDATE.
     *
     * @param string $table the table name
     */
    public function buildTable(string $table): string
    {
        return " {$table}";
    }

    /**
     * Builds the columns and values for the statement.
     *
     * @param array<string,string> $col_values the columns and values
     */
    public function buildValuesForUpdate(array $col_values): string
    {
        $values = [];
        foreach ($col_values as $col => $value) {
            $values[] = "{$col} = {$value}";
        }
        return \PHP_EOL . 'SET' . $this->indentCsv($values);
    }
}
