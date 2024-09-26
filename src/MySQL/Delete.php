<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\MySQL;

use Aura\SqlQuery\Common;

/**
 * An object for MySQL UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
class Delete extends Common\Delete implements Common\OrderByInterface, Common\LimitInterface
{
    use Common\LimitTrait;

    /**
     * Builds the statement.
     */
    protected function build(): string
    {
        return parent::build()
            . $this->builder->buildLimit($this->getLimit());
    }

    /**
     * Adds or removes LOW_PRIORITY flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function lowPriority(bool $enable = true): self
    {
        $this->setFlag('LOW_PRIORITY', $enable);
        return $this;
    }

    /**
     * Adds or removes IGNORE flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function ignore(bool $enable = true): self
    {
        $this->setFlag('IGNORE', $enable);
        return $this;
    }

    /**
     * Adds or removes QUICK flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function quick(bool $enable = true): self
    {
        $this->setFlag('QUICK', $enable);
        return $this;
    }

    /**
     * Adds a column order to the query.
     *
     * @param string[] $spec the columns and direction to order by
     */
    public function orderBy(array $spec): self
    {
        return $this->addOrderBy($spec);
    }
}
