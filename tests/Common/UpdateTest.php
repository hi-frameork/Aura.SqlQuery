<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common;

class UpdateTest extends QueryTest
{
    protected string $query_type = 'Update';

    public function testCommon(): void
    {
        $this->query->table('t1')
            ->cols(['c1', 'c2'])
            ->col('c3')
            ->set('c4', null)
            ->set('c5', 'NOW()')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            UPDATE <<t1>>
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

EOD;

        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $actual);
    }

    public function testHasCols(): void
    {
        $this->query->table('t1');
        $this->assertFalse($this->query->hasCols());
        $this->query->cols(['c1', 'c2']);
        $this->assertTrue($this->query->hasCols());
    }
}
