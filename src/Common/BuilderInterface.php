<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

interface BuilderInterface
{
    /**
     * Builds the flags as a space-separated string.
     *
     * @param string[] $flags the flags to build
     */
    public function buildFlags(array $flags): string;

    /**
     * Builds the `WHERE` clause of the statement.
     *
     * @param string[] $where the WHERE elements
     */
    public function buildWhere(array $where): string;

    /**
     * Builds the `ORDER BY ...` clause of the statement.
     *
     * @param string[] $order_by the ORDER BY elements
     */
    public function buildOrderBy(array $order_by): string;

    /**
     * Builds the `LIMIT` clause of the statement.
     *
     * @param int $limit the LIMIT element
     */
    public function buildLimit(int $limit): string;

    /**
     * Builds the `LIMIT ... OFFSET` clause of the statement.
     *
     * @param int $limit  the LIMIT element
     * @param int $offset the OFFSET element
     */
    public function buildLimitOffset(int $limit, int $offset): string;

    /**
     * Returns an array as an indented comma-separated values string.
     *
     * @param string[] $list the values to convert
     */
    public function indentCsv(array $list): string;

    /**
     * Returns an array as an indented string.
     *
     * @param string[] $list the values to convert
     */
    public function indent(array $list): string;
}
