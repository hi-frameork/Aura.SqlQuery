<?php

declare(strict_types=1);

namespace Aura\SqlQuery\MySQL;

use Aura\SqlQuery\Common;

class UpdateTest extends Common\UpdateTest
{
    protected string $db_type = 'mysql';

    protected $expected_sql_with_flag = <<<'EOD'

        UPDATE%s <<t1>>
            SET
                <<c1>> = :c1,
                <<c2>> = :c2,
                <<c3>> = :c3,
                <<c4>> = NULL,
                <<c5>> = NOW()
            WHERE
                foo = :foo
                AND baz = :baz
                OR zim = gir
            LIMIT 5

EOD;

    public function testOrderByLimit(): void
    {
        $this->query->table('t1')
            ->col('c1')
            ->orderBy(['c2'])
            ->limit(10)
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            UPDATE <<t1>>
                SET
                    <<c1>> = :c1
                ORDER BY
                    c2
                LIMIT 10

EOD;

        $this->assertSameSql($expect, $actual);
    }

    public function testLowPriority(): void
    {
        $this->query->lowPriority()
            ->table('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', null)
            ->set('c5', 'NOW()')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
            ->limit(5)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, ' LOW_PRIORITY');
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $actual);
    }

    public function testIgnore(): void
    {
        $this->query->ignore()
            ->table('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', null)
            ->set('c5', 'NOW()')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
            ->limit(5)
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, ' IGNORE');
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $actual);
    }

    public function testGetterOnLimitAndOffset(): void
    {
        $this->query->table('t1')
            ->limit(5)
        ;

        $this->assertSame(5, $this->query->getLimit());
    }
}
