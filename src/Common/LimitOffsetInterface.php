<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * An interface for LIMIT...OFFSET clauses.
 *
 * @package Aura.SqlQuery
 */
interface LimitOffsetInterface extends LimitInterface
{
    /**
     * Sets a limit offset on the query.
     *
     * @param int $offset start returning after this many rows
     */
    public function offset(int $offset): self;

    /**
     * Returns the OFFSET value.
     */
    public function getOffset(): int;
}
