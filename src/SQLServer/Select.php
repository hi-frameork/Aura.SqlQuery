<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\SQLServer;

use Aura\SqlQuery\Common;
use Aura\SqlQuery\Common\QuoterInterface;

/**
 * An object for Sqlsrv SELECT queries.
 *
 * @package Aura.SqlQuery
 */
class Select extends Common\Select
{
    /**
     * @param SelectBuilder $builder
     */
    public function __construct(
        protected QuoterInterface $quoter,
        protected mixed $builder,
    ) {
    }

    protected function build(): string
    {
        return $this->builder->applyLimit(
            parent::build(),
            $this->getLimit(),
            $this->offset,
        );
    }
}
