<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for setting column values.
 *
 * @package Aura.SqlQuery
 */
interface ValuesInterface
{
    /**
     * Sets one column value placeholder; if an optional second parameter is
     * passed, that value is bound to the placeholder.
     *
     * @param string  $col   the column name
     * @param mixed[] $value optional: a value to bind to the placeholder
     */
    public function col(string $col, ...$value): self;

    /**
     * Sets multiple column value placeholders. If an element is a key-value
     * pair, the key is treated as the column name and the value is bound to
     * that column.
     *
     * @param array $cols a list of column names, optionally as key-value
     *                    pairs where the key is a column name and the value is a bind value for
     *                    that column
     */
    public function cols(array $cols): self;

    /**
     * Sets a column value directly; the value will not be escaped, although
     * fully-qualified identifiers in the value will be quoted.
     *
     * @param string  $col   the column name
     * @param ?string $value the column value expression
     */
    public function set(string $col, ?string $value): self;
}
