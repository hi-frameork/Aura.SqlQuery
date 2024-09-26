<?php

declare(strict_types=1);

namespace Aura\SqlQuery\MySQL;

use Aura\SqlQuery\Common;

class InsertTest extends Common\InsertTest
{
    protected string $db_type = 'mysql';

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

    protected $expected_sql_on_duplicate_key_update = <<<'EOD'

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
        ) ON DUPLICATE KEY UPDATE
            <<c1>> = :c1__on_duplicate_key,
            <<c2>> = :c2__on_duplicate_key,
            <<c3>> = :c3__on_duplicate_key,
            <<c4>> = NULL,
            <<c5>> = :c5__on_duplicate_key

EOD;

    protected $expected_replace_sql = <<<'EOD'

        REPLACE INTO <<t1>> (
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

    public function testHighPriority(): void
    {
        $this->query->highPriority()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'HIGH_PRIORITY');

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
        $this->assertSameSql($this->expected_replace_sql, $actual);
    }

    public function testLowPriority(): void
    {
        $this->query->lowPriority()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'LOW_PRIORITY');

        $this->assertSameSql($expect, $actual);
    }

    public function testDelayed(): void
    {
        $this->query->delayed()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'DELAYED');

        $this->assertSameSql($expect, $actual);
    }

    public function testIgnore(): void
    {
        $this->query->ignore()
            ->into('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'IGNORE');

        $this->assertSameSql($expect, $actual);
    }

    public function testOnDuplicateKeyUpdate(): void
    {
        $this->query->into('t1')
            ->cols(['c1', 'c2' => 'c2-inserted', 'c3'])
            ->set('c4', 'NOW()')
            ->set('c5', null)
            ->onDuplicateKeyUpdateCols(['c1', 'c2' => 'c2-updated', 'c3'])
            ->onDuplicateKeyUpdate('c4', null)
            ->onDuplicateKeyUpdateCol('c5', 'c5-updated')
        ;

        $actual = $this->query->__toString();
        $expect = $this->expected_sql_on_duplicate_key_update;
        $this->assertSameSql($expect, $actual);

        $expect = [
            'c2' => 'c2-inserted',
            'c2__on_duplicate_key' => 'c2-updated',
            'c5__on_duplicate_key' => 'c5-updated',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }
}
