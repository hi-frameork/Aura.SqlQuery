<?php

declare(strict_types=1);

namespace Aura\SqlQuery\SQLIte;

use Aura\SqlQuery\Common;

class InsertTest extends Common\InsertTest
{
    protected string $db_type = 'sqlite';

    protected $expected_sql_with_flag = <<<'EOD'

        INSERT %s INTO <<t1>> (
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

EOD;

    public function testOrAbort(): void
    {
        $this->query->orAbort()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'OR ABORT');

        $this->assertSameSql($expect, $actual);
    }

    public function testOrFail(): void
    {
        $this->query->orFail()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'OR FAIL');

        $this->assertSameSql($expect, $actual);
    }

    public function testOrIgnore(): void
    {
        $this->query->orIgnore()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'OR IGNORE');

        $this->assertSameSql($expect, $actual);
    }

    public function testOrReplace(): void
    {
        $this->query->orReplace()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'OR REPLACE');

        $this->assertSameSql($expect, $actual);
    }

    public function testOrRollback(): void
    {
        $this->query->orRollback()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'OR ROLLBACK');

        $this->assertSameSql($expect, $actual);
    }
}
