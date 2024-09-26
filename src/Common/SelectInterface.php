<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AuraSqlQueryException;

/**
 * An interface for SELECT queries.
 *
 * @package Aura.SqlQuery
 */
interface SelectInterface extends QueryInterface, WhereInterface, OrderByInterface, LimitOffsetInterface
{
    /**
     * Sets the number of rows per page.
     *
     * @param int $paging the number of rows to page at
     */
    public function setPaging(int $paging): self;

    /**
     * Gets the number of rows per page.
     */
    public function getPaging(): int;

    /**
     * Makes the select FOR UPDATE (or not).
     *
     * @param bool $enable whether or not the SELECT is FOR UPDATE (default
     *                     true)
     */
    public function forUpdate(bool $enable = true): self;

    /**
     * Makes the select DISTINCT (or not).
     *
     * @param bool $enable whether or not the SELECT is DISTINCT (default
     *                     true)
     */
    public function distinct(bool $enable = true): self;

    /**
     * Is the select DISTINCT?
     */
    public function isDistinct(): bool;

    /**
     * Adds columns to the query.
     *
     * Multiple calls to cols() will append to the list of columns, not
     * overwrite the previous columns.
     *
     * @param string[] $cols the column(s) to add to the query
     */
    public function cols(array $cols): self;

    /**
     * Remove a column via its alias.
     *
     * @param string $alias The column to remove
     */
    public function removeCol(string $alias): bool;

    /**
     * Has the column or alias been added to the query?
     *
     * @param string $alias The column or alias to look for
     */
    public function hasCol(string $alias): bool;

    /**
     * Does the query have any columns in it?
     */
    public function hasCols(): bool;

    /**
     * Returns a list of columns.
     *
     * @return array<string,mixed>
     */
    public function getCols(): array;

    /**
     * Adds a FROM element to the query; quotes the table name automatically.
     *
     * @param string $spec the table specification; "foo" or "foo AS bar"
     */
    public function from(string $spec): self;

    /**
     * Adds a raw unquoted FROM element to the query; useful for adding FROM
     * elements that are functions.
     *
     * @param string $spec The table specification, e.g. "function_name()".
     */
    public function fromRaw(string $spec): self;

    /**
     * Adds an aliased sub-select to the query.
     *
     * @param string|SelectInterface $spec if a Select object, use as the sub-select;
     *                                     if a string, the sub-select string
     * @param string                 $name the alias name for the sub-select
     */
    public function fromSubSelect(string|self $spec, string $name): self;

    /**
     * Adds a JOIN table and columns to the query.
     *
     * @param string              $join the join type: inner, left, natural, etc
     * @param string              $spec the table specification; "foo" or "foo AS bar"
     * @param ?string             $cond join on this condition
     * @param array<string,mixed> $bind values to bind to ?-placeholders in the condition
     *
     * @throws AuraSqlQueryException
     */
    public function join(string $join, string $spec, ?string $cond = null, array $bind = []): self;

    /**
     * Adds a INNER JOIN table and columns to the query.
     *
     * @param string              $spec the table specification; "foo" or "foo AS bar"
     * @param string              $cond join on this condition
     * @param array<string,mixed> $bind values to bind to ?-placeholders in the condition
     *
     * @throws AuraSqlQueryException
     */
    public function innerJoin(string $spec, ?string $cond = null, array $bind = []): self;

    /**
     * Adds a LEFT JOIN table and columns to the query.
     *
     * @param string              $spec the table specification; "foo" or "foo AS bar"
     * @param ?string             $cond join on this condition
     * @param array<string,mixed> $bind values to bind to ?-placeholders in the condition
     *
     * @throws AuraSqlQueryException
     */
    public function leftJoin(string $spec, ?string $cond = null, array $bind = []): self;

    /**
     * Adds a JOIN to an aliased subselect and columns to the query.
     *
     * @param string                 $join the join type: inner, left, natural, etc
     * @param string|SelectInterface $spec if a Select
     *                                     object, use as the sub-select; if a string, the sub-select
     *                                     command string
     * @param string                 $name the alias name for the sub-select
     * @param ?string                $cond join on this condition
     * @param array<string,mixed>    $bind values to bind to ?-placeholders in the condition
     *
     * @throws AuraSqlQueryException
     */
    public function joinSubSelect(string $join, string|self $spec, string $name, ?string $cond = null, array $bind = []): self;

    /**
     * Adds grouping to the query.
     *
     * @param string[] $spec the column(s) to group by
     */
    public function groupBy(array $spec): self;

    /**
     * Adds a HAVING condition to the query by AND.
     *
     * @param callable|string     $cond the HAVING condition
     * @param array<string,mixed> $bind values to be bound to placeholders
     */
    public function having(callable|string $cond, array $bind = []): self;

    /**
     * Adds a HAVING condition to the query by OR.
     *
     * @param callable|string     $cond the HAVING condition
     * @param array<string,mixed> $bind values to be bound to placeholders
     *
     * @see having()
     */
    public function orHaving(callable|string $cond, array $bind = []): self;

    /**
     * Sets the limit and count by page number.
     *
     * @param int $page limit results to this page number
     */
    public function page(int $page): self;

    /**
     * Returns the page number being selected.
     */
    public function getPage(): int;

    /**
     * Takes the current select properties and retains them, then sets
     * UNION for the next set of properties.
     */
    public function union(): self;

    /**
     * Takes the current select properties and retains them, then sets
     * UNION ALL for the next set of properties.
     */
    public function unionAll(): self;

    /**
     * Clears the current select properties, usually called after a union.
     * You may need to call resetUnions() if you have used one
     */
    public function reset(): void;

    /**
     * Resets the columns on the SELECT.
     */
    public function resetCols(): self;

    /**
     * Resets the FROM and JOIN clauses on the SELECT.
     */
    public function resetTables(): self;

    /**
     * Resets the WHERE clause on the SELECT.
     */
    public function resetWhere(): self;

    /**
     * Resets the GROUP BY clause on the SELECT.
     */
    public function resetGroupBy(): self;

    /**
     * Resets the HAVING clause on the SELECT.
     */
    public function resetHaving(): self;

    /**
     * Resets the ORDER BY clause on the SELECT.
     */
    public function resetOrderBy(): self;

    /**
     * Resets the UNION and UNION ALL clauses on the SELECT.
     */
    public function resetUnions(): self;
}
