<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AuraSqlQueryException;

class InsertTest extends QueryTest
{
    protected string $query_type = 'Insert';

    protected function newQuery()
    {
        $this->query_factory->setLastInsertIdNames([
            'tablex.colx' => 'tablex_colx_alternative_name',
        ]);
        return parent::newQuery();
    }

    public function testCommon(): void
    {
        $this->query->into('t1')
            ->cols(['c1', 'c2'])
            ->col('c3')
            ->set('c4', 'NOW()')
            ->set('c5', null)
            ->cols(['cx' => 'cx_value'])
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>> (
                <<c1>>,
                <<c2>>,
                <<c3>>,
                <<c4>>,
                <<c5>>,
                <<cx>>
            ) VALUES (
                :c1,
                :c2,
                :c3,
                NOW(),
                NULL,
                :cx
            )

EOD;

        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = ['cx' => 'cx_value'];
        $this->assertSame($expect, $actual);
    }

    public function testGetLastInsertIdName_default(): void
    {
        $this->query->into('table');
        $expect = null;
        $actual = $this->query->getLastInsertIdName('col');
        $this->assertSame($expect, $actual);
    }

    public function testGetLastInsertIdName_alternative(): void
    {
        $this->query->into('tablex');
        $expect = 'tablex_colx_alternative_name';
        $actual = $this->query->getLastInsertIdName('colx');
        $this->assertSame($expect, $actual);
    }

    public function testBindValues(): void
    {
        $this->markTestSkipped('');
        $this->assertInstanceOf(AuraSqlQueryException::class, $this->query->bindValues(['bar', 'bar value']));
    }

    public function testBindValue(): void
    {
        $this->markTestSkipped('');
        $this->assertInstanceOf(AuraSqlQueryException::class, $this->query->bindValue('bar', 'bar value'));
    }

    public function testBulkAddRow(): void
    {
        $this->query->into('t1');

        $this->query->cols(['c1' => 'v1-0', 'c2' => 'v2-0']);
        $this->query->col('c3', 'v3-0');
        $this->query->set('c4', 'NOW() - 0');

        $this->query->addRow();

        $this->query->col('c3', 'v3-1');
        $this->query->set('c4', 'NOW() - 1');
        $this->query->cols(['c2' => 'v2-1', 'c1' => 'v1-1']);

        $this->query->addRow();

        $this->query->set('c4', 'NOW() - 2');
        $this->query->col('c1', 'v1-2');
        $this->query->cols(['c2' => 'v2-2', 'c3' => 'v3-2']);

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>>
                (<<c1>>, <<c2>>, <<c3>>, <<c4>>)
            VALUES
                (:c1_0, :c2_0, :c3_0, NOW() - 0),
                (:c1_1, :c2_1, :c3_1, NOW() - 1),
                (:c1_2, :c2_2, :c3_2, NOW() - 2)

EOD;

        $this->assertSameSql($expect, $actual);

        $expect = [
            'c1_0' => 'v1-0',
            'c2_0' => 'v2-0',
            'c3_0' => 'v3-0',
            'c1_1' => 'v1-1',
            'c2_1' => 'v2-1',
            'c3_1' => 'v3-1',
            'c1_2' => 'v1-2',
            'c2_2' => 'v2-2',
            'c3_2' => 'v3-2',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testBulkMissingCol(): void
    {
        $this->query->into('t1');

        // the needed cols
        $this->query->cols(['c1' => 'v1-0', 'c2' => 'v2-0']);
        $this->query->col('c3', 'v3-0');
        $this->query->set('c4', 'NOW() - 0');

        // add another row
        $this->query->addRow();
        $this->query->set('c4', 'NOW() - 1');
        $this->query->cols(['c2' => 'v2-1', 'c1' => 'v1-1']);

        // failed to add c3, should blow up

        $this->expectException(
            AuraSqlQueryException::class,
            $this->requoteIdentifiers('Column <<c3>> missing from row 1.'),
        );
        $this->query->addRow();
    }

    public function testBulkEmptyRow(): void
    {
        $this->query->into('t1');

        $this->query->cols(['c1' => 'v1-0', 'c2' => 'v2-0']);
        $this->query->col('c3', 'v3-0');
        $this->query->set('c4', 'NOW() - 0');

        $this->query->addRow();

        $this->query->col('c3', 'v3-1');
        $this->query->set('c4', 'NOW() - 1');
        $this->query->cols(['c2' => 'v2-1', 'c1' => 'v1-1']);

        $this->query->addRow();

        $this->query->set('c4', 'NOW() - 2');
        $this->query->col('c1', 'v1-2');
        $this->query->cols(['c2' => 'v2-2', 'c3' => 'v3-2']);

        // add an empty row
        $this->query->addRow();

        // should be the same as testBulk()
        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>>
                (<<c1>>, <<c2>>, <<c3>>, <<c4>>)
            VALUES
                (:c1_0, :c2_0, :c3_0, NOW() - 0),
                (:c1_1, :c2_1, :c3_1, NOW() - 1),
                (:c1_2, :c2_2, :c3_2, NOW() - 2)

EOD;

        $this->assertSameSql($expect, $actual);

        $expect = [
            'c1_0' => 'v1-0',
            'c2_0' => 'v2-0',
            'c3_0' => 'v3-0',
            'c1_1' => 'v1-1',
            'c2_1' => 'v2-1',
            'c3_1' => 'v3-1',
            'c1_2' => 'v1-2',
            'c2_2' => 'v2-2',
            'c3_2' => 'v3-2',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testBulkAddRows(): void
    {
        $this->query->into('t1');
        $this->query->addRows([
            [
                'c1' => 'v1-0',
                'c2' => 'v2-0',
                'c3' => 'v3-0',
            ],
            [
                'c1' => 'v1-1',
                'c2' => 'v2-1',
                'c3' => 'v3-1',
            ],
            [
                'c1' => 'v1-2',
                'c2' => 'v2-2',
                'c3' => 'v3-2',
            ],
        ]);

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>>
                (<<c1>>, <<c2>>, <<c3>>)
            VALUES
                (:c1_0, :c2_0, :c3_0),
                (:c1_1, :c2_1, :c3_1),
                (:c1_2, :c2_2, :c3_2)

EOD;

        $this->assertSameSql($expect, $actual);

        $expect = [
            'c1_0' => 'v1-0',
            'c2_0' => 'v2-0',
            'c3_0' => 'v3-0',
            'c1_1' => 'v1-1',
            'c2_1' => 'v2-1',
            'c3_1' => 'v3-1',
            'c1_2' => 'v1-2',
            'c2_2' => 'v2-2',
            'c3_2' => 'v3-2',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testIssue60_addRowsWithOnlyOneRow(): void
    {
        $this->query->into('t1');
        $this->query->addRows([
            [
                'c1' => 'v1-0',
                'c2' => 'v2-0',
                'c3' => 'v3-0',
            ],
        ]);

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>> (
                <<c1>>,
                <<c2>>,
                <<c3>>
            ) VALUES (
                :c1,
                :c2,
                :c3
            )

EOD;

        $this->assertSameSql($expect, $actual);

        $expect = [
            'c1' => 'v1-0',
            'c2' => 'v2-0',
            'c3' => 'v3-0',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testIssue60_repeatedAddRowsWithOnlyOneRow(): void
    {
        $this->query->into('t1');
        $this->query->addRows([
            [
                'c1' => 'v1-0',
                'c2' => 'v2-0',
                'c3' => 'v3-0',
            ],
        ]);

        $this->query->addRows([
            [
                'c1' => 'v1-1',
                'c2' => 'v2-1',
                'c3' => 'v3-1',
            ],
        ]);

        $this->query->addRows([
            [
                'c1' => 'v1-2',
                'c2' => 'v2-2',
                'c3' => 'v3-2',
            ],
        ]);

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            INSERT INTO <<t1>>
                (<<c1>>, <<c2>>, <<c3>>)
            VALUES
                (:c1_0, :c2_0, :c3_0),
                (:c1_1, :c2_1, :c3_1),
                (:c1_2, :c2_2, :c3_2)

EOD;

        $this->assertSameSql($expect, $actual);

        $expect = [
            'c1_0' => 'v1-0',
            'c2_0' => 'v2-0',
            'c3_0' => 'v3-0',
            'c1_1' => 'v1-1',
            'c2_1' => 'v2-1',
            'c3_1' => 'v3-1',
            'c1_2' => 'v1-2',
            'c2_2' => 'v2-2',
            'c3_2' => 'v3-2',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }
}
