<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Interface for query objects.
 *
 * @package Aura.SqlQuery
 */
interface QueryInterface extends \Stringable
{
    /**
     * Returns this query object as an SQL statement string.
     */
    public function getStatement(): string;

    /**
     * Returns the prefix to use when quoting identifier names.
     */
    public function getQuoteNamePrefix(): string;

    /**
     * Returns the suffix to use when quoting identifier names.
     */
    public function getQuoteNameSuffix(): string;

    /**
     * Adds values to bind into the query; merges with existing values.
     *
     * @param array<string,mixed> $bind_values values to bind to the query
     */
    public function bindValues(array $bind_values): self;

    /**
     * Binds a single value to the query.
     *
     * @param string $name  the placeholder name or number
     * @param mixed  $value the value to bind to the placeholder
     */
    public function bindValue(string $name, mixed $value): self;

    /**
     * Gets the values to bind into the query.
     *
     * @return array<string,mixed>
     */
    public function getBindValues(): array;

    /**
     * Reset all query flags.
     */
    public function resetFlags(): self;
}
