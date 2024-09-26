<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An object for UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
class Update extends DmlQuery implements UpdateInterface
{
    use WhereTrait;

    /**
     * The table to update.
     */
    protected string $table;

    /**
     * @param UpdateBuilder $builder
     */
    public function __construct(
        protected QuoterInterface $quoter,
        protected mixed $builder,
    ) {
    }

    public function table(string $table): self
    {
        $this->table = $this->quoter->quoteName($table);
        return $this;
    }

    /**
     * Builds this query object into a string.
     */
    protected function build(): string
    {
        return 'UPDATE'
            . $this->builder->buildFlags($this->flags)
            . $this->builder->buildTable($this->table)
            . $this->builder->buildValuesForUpdate($this->col_values)
            . $this->builder->buildWhere($this->where)
            . $this->builder->buildOrderBy($this->order_by);
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
}
