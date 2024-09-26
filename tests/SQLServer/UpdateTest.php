<?php

declare(strict_types=1);

namespace Aura\SqlQuery\SQLServer;

use Aura\SqlQuery\Common;

class UpdateTest extends Common\UpdateTest
{
    protected string $db_type = 'sqlsrv';
}
