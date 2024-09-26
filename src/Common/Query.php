<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Abstract query object.
 *
 * @package Aura.SqlQuery
 */
abstract class Query implements QueryInterface
{
    /**
     * Data to be bound to the query.
     *
     * @var array<string,mixed>
     */
    protected array $bind_values = [];

    /**
     * The list of WHERE conditions.
     *
     * @var string[]
     */
    protected array $where = [];

    /**
     * ORDER BY these columns.
     *
     * @var string[]
     */
    protected array $order_by = [];

    /**
     * The list of flags.
     *
     * @var string[]
     */
    protected array $flags = [];

    public function __construct(protected QuoterInterface $quoter)
    {
    }

    /**
     * Returns this query object as an SQL statement string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getStatement();
    }

    public function getStatement(): string
    {
        return $this->build();
    }

    /**
     * Builds this query object into a string.
     */
    abstract protected function build(): string;

    public function getQuoteNamePrefix(): string
    {
        return $this->quoter->getQuoteNamePrefix();
    }

    public function getQuoteNameSuffix(): string
    {
        return $this->quoter->getQuoteNameSuffix();
    }

    public function bindValues(array $bind_values): self
    {
        // array_merge() renumbers integer keys, which is bad for
        // question-mark placeholders
        foreach ($bind_values as $key => $val) {
            $this->bindValue($key, $val);
        }
        return $this;
    }

    /**
     * Binds a single value to the query.
     *
     * @param string $name  the placeholder name or number
     * @param mixed  $value the value to bind to the placeholder
     */
    public function bindValue(string $name, mixed $value): self
    {
        $this->bind_values[$name] = $value;
        return $this;
    }

    public function getBindValues(): array
    {
        return $this->bind_values;
    }

    /**
     * Reset all values bound to named placeholders.
     */
    public function resetBindValues(): self
    {
        $this->bind_values = [];
        return $this;
    }

    /**
     * Sets or unsets specified flag.
     *
     * @param string $flag   Flag to set or unset
     * @param bool   $enable Flag status - enabled or not (default true)
     */
    protected function setFlag(string $flag, bool $enable = true): void
    {
        if ($enable) {
            $this->flags[$flag] = true;
        } else {
            unset($this->flags[$flag]);
        }
    }

    /**
     * Returns true if the specified flag was enabled by setFlag().
     *
     * @param string $flag Flag to check
     */
    protected function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]);
    }

    /**
     * Reset all query flags.
     */
    public function resetFlags(): self
    {
        $this->flags = [];
        return $this;
    }

    /**
     * Adds conditions and binds values to a clause.
     *
     * @param string              $clause the clause to work with, typically 'where' or
     *                                    'having'
     * @param string              $andor  add the condition using this operator, typically
     *                                    'AND' or 'OR'
     * @param callable|string     $cond   the WHERE condition
     * @param array<string,mixed> $bind   arguments to bind to placeholders
     */
    protected function addClauseCondWithBind(string $clause, string $andor, callable|string $cond, array $bind): void
    {
        if ($cond instanceof \Closure) {
            $this->addClauseCondClosure($clause, $andor, $cond);
            $this->bindValues($bind);
            return;
        }

        $cond = $this->quoter->quoteNamesIn($cond);
        $cond = $this->rebuildCondAndBindValues($cond, $bind);

        $clause = &$this->{$clause};
        if ($clause) {
            $clause[] = "{$andor} {$cond}";
        } else {
            $clause[] = $cond;
        }
    }

    /**
     * Adds to a clause through a closure, enclosing within parentheses.
     *
     * @param string   $clause  the clause to work with, typically 'where' or
     *                          'having'
     * @param string   $andor   add the condition using this operator, typically
     *                          'AND' or 'OR'
     * @param callable $closure the closure that adds to the clause
     */
    protected function addClauseCondClosure(string $clause, string $andor, callable $closure): void
    {
        // retain the prior set of conditions, and temporarily reset the clause
        // for the closure to work with (otherwise there will be an extraneous
        // opening AND/OR keyword)
        $set = $this->{$clause};
        $this->{$clause} = [];

        // invoke the closure, which will re-populate the $this->$clause
        $closure($this);

        // are there new clause elements?
        if (! $this->{$clause}) {
            // no: restore the old ones, and done
            $this->{$clause} = $set;
            return;
        }

        // append an opening parenthesis to the prior set of conditions,
        // with AND/OR as needed ...
        if ($set) {
            $set[] = "{$andor} (";
        } else {
            $set[] = '(';
        }

        // append the new conditions to the set, with indenting
        foreach ($this->{$clause} as $cond) {
            $set[] = "    {$cond}";
        }
        $set[] = ')';

        // ... then put the full set of conditions back into $this->$clause
        $this->{$clause} = $set;
    }

    /**
     * Rebuilds a condition string, replacing sequential placeholders with
     * named placeholders, and binding the sequential values to the named
     * placeholders.
     *
     * @param string              $cond        the condition with sequential placeholders
     * @param array<string,mixed> $bind_values the values to bind to the sequential
     *                                         placeholders under their named versions
     *
     * @return string the rebuilt condition string
     */
    protected function rebuildCondAndBindValues(string $cond, array $bind_values): string
    {
        $selects = [];

        foreach ($bind_values as $key => $val) {
            if ($val instanceof SelectInterface) {
                $selects[":{$key}"] = $val;
            } else {
                $this->bindValue($key, $val);
            }
        }

        foreach ($selects as $key => $select) {
            $selects[$key] = $select->getStatement();
            $this->bind_values = \array_merge(
                $this->bind_values,
                $select->getBindValues(),
            );
        }

        return \strtr($cond, $selects);
    }

    /**
     * Adds a column order to the query.
     *
     * @param string[] $spec the columns and direction to order by
     */
    protected function addOrderBy(array $spec): self
    {
        foreach ($spec as $col) {
            $this->order_by[] = $this->quoter->quoteNamesIn($col);
        }
        return $this;
    }
}
