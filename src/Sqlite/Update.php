<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\SQLite;

use Aura\SqlQuery\Common;

/**
 * An object for Sqlite UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
class Update extends Common\Update implements Common\OrderByInterface, Common\LimitOffsetInterface
{
    use Common\LimitOffsetTrait;

    protected function build(): string
    {
        return parent::build()
            . $this->builder->buildLimitOffset($this->getLimit(), $this->offset);
    }

    /**
     * Adds or removes OR ABORT flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function orAbort(bool $enable = true): self
    {
        $this->setFlag('OR ABORT', $enable);
        return $this;
    }

    /**
     * Adds or removes OR FAIL flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function orFail(bool $enable = true): self
    {
        $this->setFlag('OR FAIL', $enable);
        return $this;
    }

    /**
     * Adds or removes OR IGNORE flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function orIgnore(bool $enable = true): self
    {
        $this->setFlag('OR IGNORE', $enable);
        return $this;
    }

    /**
     * Adds or removes OR REPLACE flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function orReplace(bool $enable = true): self
    {
        $this->setFlag('OR REPLACE', $enable);
        return $this;
    }

    /**
     * Adds or removes OR ROLLBACK flag.
     *
     * @param bool $enable set or unset flag (default true)
     */
    public function orRollback(bool $enable = true): self
    {
        $this->setFlag('OR ROLLBACK', $enable);
        return $this;
    }

    public function orderBy(array $spec): self
    {
        return $this->addOrderBy($spec);
    }
}
