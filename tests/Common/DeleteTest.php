<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

class DeleteTest extends QueryTest
{
    protected string $query_type = 'Delete';

    public function testCommon(): void
    {
        $this->query->from('t1')
            ->where('foo = :foo', ['foo' => 'bar'])
            ->where('baz = :baz', ['baz' => 'dib'])
            ->orWhere('zim = gir')
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            DELETE FROM <<t1>>
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
}
