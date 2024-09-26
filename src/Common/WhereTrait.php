<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Common code for WHERE clauses.
 *
 * @package Aura.SqlQuery
 */
trait WhereTrait
{
    /**
     * Adds a WHERE condition to the query by AND.
     *
     * @param callable|string         $cond the WHERE condition
     * @param array<string|int,mixed> $bind Values to be bound to placeholders
     */
    public function where(callable|string $cond, array $bind = []): self
    {
        $this->addClauseCondWithBind('where', 'AND', $cond, $bind);
        return $this;
    }

    /**
     * Adds a WHERE condition to the query by OR. If the condition has
     * ?-placeholders, additional arguments to the method will be bound to
     * those placeholders sequentially.
     *
     * @param callable|string         $cond the WHERE condition
     * @param array<int|string,mixed> $bind Values to be bound to placeholders
     *
     * @see where()
     */
    public function orWhere(callable|string $cond, array $bind = []): self
    {
        $this->addClauseCondWithBind('where', 'OR', $cond, $bind);
        return $this;
    }
}
