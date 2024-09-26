<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Postgres;

use Aura\SqlQuery\Common;
use Aura\SqlQuery\Common\QuoterInterface;

/**
 * An object for PgSQL INSERT queries.
 *
 * @package Aura.SqlQuery
 */
class Insert extends Common\Insert implements ReturningInterface
{
    use ReturningTrait;

    /**
     * @param InsertBuilder $builder
     */
    public function __construct(
        protected QuoterInterface $quoter,
        protected mixed $builder,
    ) {
    }

    protected function build(): string
    {
        return parent::build()
            . $this->builder->buildReturning($this->returning);
    }

    public function getLastInsertIdName($col): ?string
    {
        $name = parent::getLastInsertIdName($col);
        if (! $name) {
            $name = "{$this->into_raw}_{$col}_seq";
        }
        return $name;
    }
}
