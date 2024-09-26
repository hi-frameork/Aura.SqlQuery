<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for WHERE clauses.
 *
 * @package Aura.SqlQuery
 */
interface WhereInterface
{
    /**
     * Adds a WHERE condition to the query by AND. If the condition has
     * ?-placeholders, additional arguments to the method will be bound to
     * those placeholders sequentially.
     *
     * @param callable|string $cond the WHERE condition
     * @param array           $bind values to be bound to placeholders
     *
     * @return $this
     */
    public function where(callable|string $cond, array $bind = []): self;

    /**
     * Adds a WHERE condition to the query by OR. If the condition has
     * ?-placeholders, additional arguments to the method will be bound to
     * those placeholders sequentially.
     *
     * @param callable|string $cond the WHERE condition
     * @param array           $bind values to be bound to placeholders
     *
     * @return $this
     *
     * @see where()
     */
    public function orWhere(callable|string $cond, array $bind = []): self;
}
