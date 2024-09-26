<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for INSERT queries.
 *
 * @package Aura.SqlQuery
 */
interface InsertInterface extends QueryInterface, ValuesInterface
{
    /**
     * Sets the table to insert into.
     *
     * @param string $into the table to insert into
     */
    public function into(string $into): self;

    /**
     * Sets the map of fully-qualified `table.column` names to last-insert-id
     * names. Generally useful only for extended tables in Postgres.
     *
     * @param string[] $last_insert_id_names the list of ID names
     */
    public function setLastInsertIdNames(array $last_insert_id_names): void;

    /**
     * Returns the proper name for passing to `PDO::lastInsertId()`.
     *
     * @param string $col the last insert ID column
     *
     * @return mixed normally null, since most drivers do not need a name;
     *               alternatively, a string from `$last_insert_id_names`
     */
    public function getLastInsertIdName(string $col): ?string;

    /**
     * Adds multiple rows for bulk insert.
     *
     * @param array<int,array<string,mixed>> $rows An array of rows, where each element is an array of
     *                                             column key-value pairs. The values are bound to placeholders.
     */
    public function addRows(array $rows): self;

    /**
     * Add one row for bulk insert; increments the row counter and optionally
     * adds columns to the new row.
     *
     * When adding the first row, the counter is not incremented.
     *
     * After calling `addRow()`, you can further call `col()`, `cols()`, and
     * `set()` to work with the newly-added row. Calling `addRow()` again will
     * finish off the current row and start a new one.
     *
     * @param array<string,mixed> $cols an array of column key-value pairs; the values are
     *                                  bound to placeholders
     */
    public function addRow(array $cols = []): self;
}
