<?php

declare(strict_types=1);

namespace Aura\SqlQuery\SQLServer;

use Aura\SqlQuery\Common;

class SelectTest extends Common\SelectTest
{
    protected string $db_type = 'sqlsrv';

    public function testLimitOffset(): void
    {
        $this->query->cols(['*']);
        $this->query->limit(10);
        $expect = <<<'EOD'

            SELECT TOP 10
                *

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $this->query->offset(40);
        $expect = <<<'EOD'

            SELECT
                *
            OFFSET 40 ROWS FETCH NEXT 10 ROWS ONLY

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testPage(): void
    {
        $this->query->cols(['*']);
        $this->query->page(5);
        $expect = <<<'EOD'

            SELECT
                *
            OFFSET 40 ROWS FETCH NEXT 10 ROWS ONLY

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }
}
