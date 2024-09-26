<?php

declare(strict_types=1);

namespace Aura\SqlQuery\MySQL;

use Aura\SqlQuery\Common;

class DeleteTest extends Common\DeleteTest
{
    protected string $db_type = 'mysql';

    protected $expected_sql_with_flag = <<<'EOD'

        DELETE %s FROM <<t1>>
            WHERE
                foo = :foo
                AND baz = :baz
                OR zim = gir

EOD;

    public function testOrderByLimit(): void
    {
        $this->query->from('t1')
            ->orderBy(['c1', 'c2'])
            ->limit(10)
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            DELETE FROM <<t1>>
                ORDER BY
                    c1,
                    c2
                LIMIT 10

EOD;
        $this->assertSameSql($expect, $actual);
    }

    public function testLowPriority(): void
    {
        $this->query->lowPriority()
            ->from('t1')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'LOW_PRIORITY');
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $actual);
    }

    public function testQuick(): void
    {
        $this->query->quick()
            ->from('t1')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'QUICK');
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
            ->from('t1')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
        ;

        $actual = $this->query->__toString();
        $expect = \sprintf($this->expected_sql_with_flag, 'IGNORE');
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
        $this->query->from('t1')
            ->limit(5)
        ;

        $this->assertSame(5, $this->query->getLimit());

    }
}
