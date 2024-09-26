<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AuraSqlQueryException;
use Aura\SqlQuery\Exception;

/**
 * An object for INSERT queries.
 *
 * @package Aura.SqlQuery
 */
class Insert extends DmlQuery implements InsertInterface
{
    /**
     * The table to insert into (quoted).
     */
    protected string $into;

    /**
     * The table to insert into (raw, for last-insert-id use).
     */
    protected string $into_raw;

    /**
     * A map of fully-qualified `table.column` names to last-insert-id names.
     * This is used to look up the right last-insert-id name for a given table
     * and column. Generally useful only for extended tables in Postgres.
     *
     * @var array<string,string>
     */
    protected array $last_insert_id_names;

    /**
     * The current row-number we are adding column values for. This comes into
     * play only with bulk inserts.
     */
    protected int $row = 0;

    /**
     * A collection of `$col_values` for previous rows in bulk inserts.
     *
     * @var array<int,array<string,mixed>>
     */
    protected array $col_values_bulk = [];

    /**
     * A collection of `$bind_values` for previous rows in bulk inserts.
     *
     * @var array<string,mixed>
     */
    protected array $bind_values_bulk = [];

    /**
     * The order in which columns will be bulk-inserted; this is taken from the
     * very first inserted row.
     *
     * @var string[]
     */
    protected array $col_order = [];

    /**
     * @param InsertBuilder $builder
     */
    public function __construct(
        protected QuoterInterface $quoter,
        protected mixed $builder,
    ) {
    }

    public function into($into): self
    {
        $this->into_raw = $into;
        $this->into = $this->quoter->quoteName($into);
        return $this;
    }

    protected function build(): string
    {
        $stm = 'INSERT'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildInto($this->into);

        if ($this->row) {
            $this->finishRow();
            $stm .= $this->builder->buildValuesForBulkInsert($this->col_order, $this->col_values_bulk);
        } else {
            $stm .= $this->builder->buildValuesForInsert($this->col_values);
        }

        return $stm;
    }

    public function setLastInsertIdNames(array $last_insert_id_names): void
    {
        $this->last_insert_id_names = $last_insert_id_names;
    }

    public function getLastInsertIdName(string $col): ?string
    {
        $key = $this->into_raw . '.' . $col;
        if (isset($this->last_insert_id_names[$key])) {
            return $this->last_insert_id_names[$key];
        }

        return null;
    }

    public function col(string $col, ...$value): self
    {
        return $this->addCol($col, ...$value);
    }

    public function cols(array $cols): self
    {
        return $this->addCols($cols);
    }

    public function set(string $col, ?string $value): self
    {
        return $this->setCol($col, $value);
    }

    /**
     * Gets the values to bind to placeholders.
     */
    public function getBindValues(): array
    {
        return \array_merge(parent::getBindValues(), $this->bind_values_bulk);
    }

    public function addRows(array $rows): self
    {
        foreach ($rows as $cols) {
            $this->addRow($cols);
        }
        if ($this->row > 1) {
            $this->finishRow();
        }
        return $this;
    }

    public function addRow(array $cols = []): self
    {
        if (empty($this->col_values)) {
            return $this->cols($cols);
        }

        if (empty($this->col_order)) {
            $this->col_order = \array_keys($this->col_values);
        }

        $this->finishRow();
        $this->row++;
        $this->cols($cols);
        return $this;
    }

    /**
     * Finishes off the current row in a bulk insert, collecting the bulk
     * values and resetting for the next row.
     */
    protected function finishRow(): void
    {
        if (empty($this->col_values)) {
            return;
        }

        foreach ($this->col_order as $col) {
            $this->finishCol($col);
        }

        $this->col_values = [];
        $this->bind_values = [];
    }

    /**
     * Finishes off a single column of the current row in a bulk insert.
     *
     * @param string $col the column to finish off
     *
     * @throws Exception on named column missing from row
     */
    protected function finishCol($col): void
    {
        if (! \array_key_exists($col, $this->col_values)) {
            throw new AuraSqlQueryException("Column {$col} missing from row {$this->row}.");
        }

        // get the current col_value
        $value = $this->col_values[$col];

        // is it *not* a placeholder?
        if (':' != \mb_substr($value, 0, 1)) {
            // copy the value as-is
            $this->col_values_bulk[$this->row][$col] = $value;
            return;
        }

        // retain col_values in bulk with the row number appended
        $this->col_values_bulk[$this->row][$col] = "{$value}_{$this->row}";

        // the existing placeholder name without : or row number
        $name = \mb_substr($value, 1);

        // retain bind_value in bulk with new placeholder
        if (\array_key_exists($name, $this->bind_values)) {
            $this->bind_values_bulk["{$name}_{$this->row}"] = $this->bind_values[$name];
        }
    }
}
