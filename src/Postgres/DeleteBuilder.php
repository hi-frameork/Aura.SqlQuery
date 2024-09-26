<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Postgres;

use Aura\SqlQuery\Common;

/**
 * DELETE builder for Postgres.
 *
 * @package Aura.SqlQuery
 */
class DeleteBuilder extends Common\DeleteBuilder
{
    use BuildReturningTrait;
}
