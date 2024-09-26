<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Postgres;

use Aura\SqlQuery\Common;

class SelectTest extends Common\SelectTest
{
    protected string $db_type = 'pgsql';
}
