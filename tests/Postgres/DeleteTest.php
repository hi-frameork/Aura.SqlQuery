<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Postgres;

use Aura\SqlQuery\Common;

class DeleteTest extends Common\DeleteTest
{
    protected string $db_type = 'pgsql';

    public function testReturning(): void
    {
        $this->query->from('t1')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
            ->returning(['foo', 'baz', 'zim'])
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            DELETE FROM <<t1>>
            WHERE
                foo = :foo
                AND baz = :baz
                OR zim = gir
            RETURNING
                foo,
                baz,
                zim

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
