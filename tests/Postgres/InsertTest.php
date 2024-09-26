<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Postgres;

use Aura\SqlQuery\Common;

class InsertTest extends Common\InsertTest
{
    protected string $db_type = 'pgsql';

    public function testReturning(): void
    {
        $this->query->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
            ->returning(['c1', 'c2'])
            ->returning(['c3'])
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>> (
                <<c1>>,
                <<c2>>,
                <<c3>>,
                <<c4>>,
                <<c5>>
            ) VALUES (
                :c1,
                :c2,
                :c3,
                NOW(),
                NULL
            )
            RETURNING
                c1,
                c2,
                c3

EOD;

        $this->assertSameSql($expect, $actual);
    }

    public function testGetLastInsertIdName_default(): void
    {
        $this->query->into('table');
        $actual = $this->query->getLastInsertIdName('col');
        $expect = 'table_col_seq';
        $this->assertSame($expect, $actual);
    }
}
