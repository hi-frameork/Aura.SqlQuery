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
 * Quote for MySQL.
 *
 * @package Aura.SqlQuery
 */
class Quoter extends Common\Quoter
{
    /**
     * The prefix to use when quoting identifier names.
     */
    protected string $quote_name_prefix = '`';

    /**
     * The suffix to use when quoting identifier names.
     */
    protected string $quote_name_suffix = '`';
}
