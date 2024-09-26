<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for DELETE queries.
 *
 * @package Aura.SqlQuery
 */
interface DeleteInterface extends QueryInterface, WhereInterface
{
    /**
     * Sets the table to delete from.
     *
     * @param string $from the table to delete from
     */
    public function from(string $table): self;
}
