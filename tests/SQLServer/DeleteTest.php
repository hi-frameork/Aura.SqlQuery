<?php

declare(strict_types=1);

namespace Aura\SqlQuery\SQLServer;

use Aura\SqlQuery\Common;

class DeleteTest extends Common\DeleteTest
{
    protected string $db_type = 'sqlsrv';
}
