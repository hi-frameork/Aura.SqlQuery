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
 * An object for SELECT queries.
 *
 * @package Aura.SqlQuery
 */
class Select extends Query implements SelectInterface
{
    use WhereTrait;
    use LimitOffsetTrait { limit as setLimit;
        offset as setOffset; }

    /**
     * An array of union SELECT statements.
     *
     * @var string[]
     */
    protected array $union = [];

    /**
     * Is this a SELECT FOR UPDATE?
     */
    protected bool $for_update = false;

    /**
     * The columns to be selected.
     *
     * @var array<string,mixed>
     */
    protected array $cols = [];

    /**
     * Select from these tables; includes JOIN clauses.
     *
     * @var string[]
     */
    protected array $from = [];

    /**
     * The current key in the `$from` array.
     */
    protected int $from_key = -1;

    /**
     * Tracks which JOIN clauses are attached to which FROM tables.
     *
     * @var string[]
     */
    protected array $join = [];

    /**
     * GROUP BY these columns.
     *
     * @var string[]
     */
    protected array $group_by = [];

    /**
     * The list of HAVING conditions.
     *
     * @var string[]
     */
    protected array $having = [];

    /**
     * The page number to select.
     */
    protected int $page = 0;

    /**
     * The number of rows per page.
     */
    protected int $paging = 10;

    /**
     * Tracks table references to avoid duplicate identifiers.
     *
     * @var string[]
     */
    protected array $table_refs = [];

    /**
     * @param SelectBuilder $builder
     */
    public function __construct(
        protected QuoterInterface $quoter,
        protected mixed $builder,
    ) {
    }

    public function getStatement(): string
    {
        $union = '';
        if (! empty($this->union)) {
            $union = \implode(\PHP_EOL, $this->union) . \PHP_EOL;
        }
        return $union . $this->build();
    }

    public function setPaging(int $paging): self
    {
        $this->paging = (int) $paging;
        if ($this->page) {
            $this->setPagingLimitOffset();
        }
        return $this;
    }

    public function getPaging(): int
    {
        return $this->paging;
    }

    public function forUpdate(bool $enable = true): self
    {
        $this->for_update = (bool) $enable;
        return $this;
    }

    public function distinct(bool $enable = true): self
    {
        $this->setFlag('DISTINCT', $enable);
        return $this;
    }

    public function isDistinct(): bool
    {
        return $this->hasFlag('DISTINCT');
    }

    public function cols(array $cols): self
    {
        foreach ($cols as $key => $val) {
            $this->addCol($key, $val);
        }
        return $this;
    }

    /**
     * Adds a column and alias to the columns to be selected.
     *
     * @param mixed $key If an integer, ignored. Otherwise, the column to be
     *                   added.
     * @param mixed $val if $key was an integer, the column to be added;
     *                   otherwise, the column alias
     */
    protected function addCol(string|int $key, mixed $val): void
    {
        if (\is_string($key)) {
            // [col => alias]
            $this->cols[$val] = $key;
        } else {
            $this->addColWithAlias($val);
        }
    }

    /**
     * Adds a column with an alias to the columns to be selected.
     *
     * @param string $spec the column specification: "col alias",
     *                     "col AS alias", or something else entirely
     */
    protected function addColWithAlias(string $spec): void
    {
        $parts = \explode(' ', $spec);
        $count = \count($parts);
        if (2 == $count) {
            // "col alias"
            $this->cols[$parts[1]] = $parts[0];
        } elseif (3 == $count && 'AS' == \mb_strtoupper($parts[1])) {
            // "col AS alias"
            $this->cols[$parts[2]] = $parts[0];
        } else {
            // no recognized alias
            $this->cols[] = $spec;
        }
    }

    public function removeCol(string $alias): bool
    {
        if (isset($this->cols[$alias])) {
            unset($this->cols[$alias]);

            return true;
        }

        $index = \array_search($alias, $this->cols);
        if (false !== $index) {
            unset($this->cols[$index]);
            return true;
        }

        return false;
    }

    public function hasCol(string $alias): bool
    {
        return isset($this->cols[$alias]) || false !== \array_search($alias, $this->cols);
    }

    public function hasCols(): bool
    {
        return (bool) $this->cols;
    }

    public function getCols(): array
    {
        return $this->cols;
    }

    /**
     * Tracks table references.
     *
     * @param string $type FROM, JOIN, etc
     * @param string $spec the table and alias name
     *
     * @throws AuraSqlQueryException when the reference has already been used
     */
    protected function addTableRef(string $type, string $spec): void
    {
        $name = $spec;

        $pos = \mb_strripos($name, ' AS ');
        if (false !== $pos) {
            $name = \trim(\mb_substr($name, $pos + 4));
        }

        if (isset($this->table_refs[$name])) {
            $used = $this->table_refs[$name];
            throw new AuraSqlQueryException("Cannot reference '{$type} {$spec}' after '{$used}'");
        }

        $this->table_refs[$name] = "{$type} {$spec}";
    }

    public function from(string $spec): self
    {
        $this->addTableRef('FROM', $spec);
        return $this->addFrom($this->quoter->quoteName($spec));
    }

    public function fromRaw(string $spec): self
    {
        $this->addTableRef('FROM', $spec);
        return $this->addFrom($spec);
    }

    /**
     * Adds to the $from property and increments the key count.
     *
     * @param string $spec the table specification
     */
    protected function addFrom(string $spec): self
    {
        $this->from[] = [$spec];
        $this->from_key++;
        return $this;
    }

    public function fromSubSelect(string|SelectInterface $spec, string $name): self
    {
        $this->addTableRef('FROM (SELECT ...) AS', $name);
        $spec = $this->subSelect($spec, '        ');
        $name = $this->quoter->quoteName($name);
        return $this->addFrom("({$spec}    ) AS {$name}");
    }

    /**
     * Formats a sub-SELECT statement, binding values from a Select object as
     * needed.
     *
     * return the sub-SELECT string
     *
     * @param string|SelectInterface $spec   a sub-SELECT specification
     * @param string                 $indent indent each line with this string
     */
    protected function subSelect(string|SelectInterface $spec, string $indent): string
    {
        if ($spec instanceof SelectInterface) {
            $this->bindValues($spec->getBindValues());
        }

        return \PHP_EOL . $indent
            . \ltrim(\preg_replace('/^/m', $indent, (string) $spec))
            . \PHP_EOL;
    }

    public function join(string $join, string $spec, ?string $cond = null, array $bind = []): self
    {
        $join = \mb_strtoupper(\ltrim("{$join} JOIN"));
        $this->addTableRef($join, $spec);

        $spec = $this->quoter->quoteName($spec);
        $cond = $this->fixJoinCondition($cond, $bind);
        return $this->addJoin(\rtrim("{$join} {$spec} {$cond}"));
    }

    /**
     * Fixes a JOIN condition to quote names in the condition and prefix it
     * with a condition type ('ON' is the default and 'USING' is recognized).
     *
     * @param ?string             $cond join on this condition
     * @param array<string,mixed> $bind values to bind to ?-placeholders in the condition
     */
    protected function fixJoinCondition(?string $cond, array $bind): string
    {
        if (! $cond) {
            return '';
        }

        $cond = $this->quoter->quoteNamesIn($cond);
        $cond = $this->rebuildCondAndBindValues($cond, $bind);

        if ('ON ' == \mb_strtoupper(\mb_substr(\ltrim($cond), 0, 3))) {
            return $cond;
        }

        if ('USING ' == \mb_strtoupper(\mb_substr(\ltrim($cond), 0, 6))) {
            return $cond;
        }

        return 'ON ' . $cond;
    }

    public function innerJoin(string $spec, ?string $cond = null, array $bind = []): self
    {
        return $this->join('INNER', $spec, $cond, $bind);
    }

    public function leftJoin(string $spec, ?string $cond = null, array $bind = []): self
    {
        return $this->join('LEFT', $spec, $cond, $bind);
    }

    public function joinSubSelect(string $join, string|SelectInterface $spec, string $name, ?string $cond = null, array $bind = []): self
    {
        $join = \mb_strtoupper(\ltrim("{$join} JOIN"));
        $this->addTableRef("{$join} (SELECT ...) AS", $name);

        $spec = $this->subSelect($spec, '            ');
        $name = $this->quoter->quoteName($name);
        $cond = $this->fixJoinCondition($cond, $bind);

        $text = \rtrim("{$join} ({$spec}        ) AS {$name} {$cond}");
        return $this->addJoin('        ' . $text);
    }

    /**
     * Adds the JOIN to the right place, given whether or not a FROM has been
     * specified yet.
     *
     * @param string $spec the JOIN clause
     */
    protected function addJoin(string $spec): self
    {
        $from_key = (-1 == $this->from_key) ? 0 : $this->from_key;
        $this->join[$from_key][] = $spec;
        return $this;
    }

    public function groupBy(array $spec): self
    {
        foreach ($spec as $col) {
            $this->group_by[] = $this->quoter->quoteNamesIn($col);
        }
        return $this;
    }

    public function having(callable|string $cond, array $bind = []): self
    {
        $this->addClauseCondWithBind('having', 'AND', $cond, $bind);
        return $this;
    }

    public function orHaving(callable|string $cond, array $bind = []): self
    {
        $this->addClauseCondWithBind('having', 'OR', $cond, $bind);
        return $this;
    }

    public function page(int $page): self
    {
        $this->page = (int) $page;
        $this->setPagingLimitOffset();
        return $this;
    }

    /**
     * Updates the limit and offset values when changing pagination.
     */
    protected function setPagingLimitOffset(): void
    {
        $this->setLimit(0);
        $this->setOffset(0);
        if ($this->page) {
            $this->setLimit($this->paging);
            $this->setOffset($this->paging * ($this->page - 1));
        }
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function union(): self
    {
        $this->union[] = $this->build() . \PHP_EOL . 'UNION';
        $this->reset();
        return $this;
    }

    public function unionAll(): self
    {
        $this->union[] = $this->build() . \PHP_EOL . 'UNION ALL';
        $this->reset();
        return $this;
    }

    public function reset(): void
    {
        $this->resetFlags();
        $this->resetCols();
        $this->resetTables();
        $this->resetWhere();
        $this->resetGroupBy();
        $this->resetHaving();
        $this->resetOrderBy();
        $this->limit(0);
        $this->offset(0);
        $this->page(0);
        $this->forUpdate(false);
    }

    public function resetCols(): self
    {
        $this->cols = [];
        return $this;
    }

    public function resetTables(): self
    {
        $this->from = [];
        $this->from_key = -1;
        $this->join = [];
        $this->table_refs = [];
        return $this;
    }

    public function resetWhere(): self
    {
        $this->where = [];
        return $this;
    }

    public function resetGroupBy(): self
    {
        $this->group_by = [];
        return $this;
    }

    public function resetHaving(): self
    {
        $this->having = [];
        return $this;
    }

    public function resetOrderBy(): self
    {
        $this->order_by = [];
        return $this;
    }

    public function resetUnions(): self
    {
        $this->union = [];
        return $this;
    }

    protected function build(): string
    {
        $cols = [];
        foreach ($this->cols as $key => $val) {
            if (\is_int($key)) {
                $cols[] = $this->quoter->quoteNamesIn($val);
            } else {
                $cols[] = $this->quoter->quoteNamesIn("{$val} AS {$key}");
            }
        }

        return 'SELECT'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildCols($cols)
            . $this->builder->buildFrom($this->from, $this->join)
            . $this->builder->buildWhere($this->where)
            . $this->builder->buildGroupBy($this->group_by)
            . $this->builder->buildHaving($this->having)
            . $this->builder->buildOrderBy($this->order_by)
            . $this->builder->buildLimitOffset($this->limit, $this->offset)
            . $this->builder->buildForUpdate($this->for_update);
    }

    /**
     * Sets a limit count on the query.
     *
     * @param int $limit the number of rows to select
     */
    public function limit(int $limit): self
    {
        $this->setLimit($limit);
        if ($this->page) {
            $this->page = 0;
            $this->setOffset(0);
        }
        return $this;
    }

    /**
     * Sets a limit offset on the query.
     *
     * @param int $offset start returning after this many rows
     */
    public function offset(int $offset): self
    {
        $this->setOffset($offset);
        if ($this->page) {
            $this->page = 0;
            $this->setLimit(0);
        }
        return $this;
    }

    public function orderBy(array $spec): self
    {
        return $this->addOrderBy($spec);
    }
}
