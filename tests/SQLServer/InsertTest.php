<?php

declare(strict_types=1);

namespace Aura\SqlQuery\SQLServer;

use Aura\SqlQuery\Common;

class InsertTest extends Common\InsertTest
{
    protected string $db_type = 'sqlsrv';
}
