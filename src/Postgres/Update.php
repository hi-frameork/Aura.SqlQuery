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
 * An object for PgSQL UPDATE queries.
 *
 * @package Aura.SqlQuery
 */
class Update extends Common\Update implements ReturningInterface
{
    use ReturningTrait;

    /**
     * @param UpdateBuilder $builder
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
}
