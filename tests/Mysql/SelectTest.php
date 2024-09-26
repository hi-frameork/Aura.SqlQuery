<?php

declare(strict_types=1);

namespace Aura\SqlQuery\MySQL;

use Aura\SqlQuery\Common;

class SelectTest extends Common\SelectTest
{
    protected string $db_type = 'mysql';

    protected $expected_sql_with_flag = <<<'EOD'

        SELECT %s
            <<t1>>.<<c1>>,
            <<t1>>.<<c2>>,
            <<t1>>.<<c3>>
        FROM
            <<t1>>

EOD;

    public function testMultiFlags(): void
    {
        $this->query->calcFoundRows()
            ->distinct()
            ->noCache()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_CALC_FOUND_ROWS DISTINCT SQL_NO_CACHE');
        $this->assertSameSql($expect, $actual);
    }

    public function testCalcFoundRows(): void
    {
        $this->query->calcFoundRows()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_CALC_FOUND_ROWS');
        $this->assertSameSql($expect, $actual);
    }

    public function testCache(): void
    {
        $this->query->cache()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_CACHE');
        $this->assertSameSql($expect, $actual);
    }

    public function testNoCache(): void
    {
        $this->query->noCache()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_NO_CACHE');
        $this->assertSameSql($expect, $actual);
    }

    public function testStraightJoin(): void
    {
        $this->query->straightJoin()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'STRAIGHT_JOIN');
        $this->assertSameSql($expect, $actual);
    }

    public function testHighPriority(): void
    {
        $this->query->highPriority()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'HIGH_PRIORITY');
        $this->assertSameSql($expect, $actual);
    }

    public function testSmallResult(): void
    {
        $this->query->smallResult()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_SMALL_RESULT');
        $this->assertSameSql($expect, $actual);
    }

    public function testBigResult(): void
    {
        $this->query->bigResult()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_BIG_RESULT');
        $this->assertSameSql($expect, $actual);
    }

    public function testBufferResult(): void
    {
        $this->query->bufferResult()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = \sprintf($this->expected_sql_with_flag, 'SQL_BUFFER_RESULT');
        $this->assertSameSql($expect, $actual);
    }
}
