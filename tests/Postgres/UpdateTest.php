<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Postgres;

use Aura\SqlQuery\Common;

class UpdateTest extends Common\UpdateTest
{
    protected string $db_type = 'pgsql';

    public function testReturning(): void
    {
        $this->query->table('t1')
            ->cols(['c1', 'c2', 'c3'])
            ->set('c4', null)
            ->set('c5', 'NOW()')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
            ->returning(['c1', 'c2'])
            ->returning(['c3'])
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
            RETURNING
                c1,
                c2,
                c3

EOD;
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $this->assertSame($expect, $actual);
    }
}
