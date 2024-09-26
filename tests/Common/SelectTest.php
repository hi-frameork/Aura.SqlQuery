<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\AuraSqlQueryException;

class SelectTest extends QueryTest
{
    protected string $query_type = 'Select';

    public function testExceptionWithNoCols(): void
    {
        $this->query->from('t1');
        $this->expectException(AuraSqlQueryException::class);
        $this->query->__toString();
    }

    public function testSetAndGetPaging(): void
    {
        $expect = 88;
        $this->query->setPaging($expect);
        $actual = $this->query->getPaging();
        $this->assertSame($expect, $actual);
    }

    public function testDistinct(): void
    {
        $this->query->distinct()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = <<<'EOD'

            SELECT DISTINCT
                <<t1>>.<<c1>>,
                <<t1>>.<<c2>>,
                <<t1>>.<<c3>>
            FROM
                <<t1>>

EOD;
        $this->assertSameSql($expect, $actual);
        $this->assertTrue($this->query->isDistinct());
    }

    public function testDuplicateFlag(): void
    {
        $this->query->distinct()
            ->distinct()
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = <<<'EOD'

            SELECT DISTINCT
                <<t1>>.<<c1>>,
                <<t1>>.<<c2>>,
                <<t1>>.<<c3>>
            FROM
                <<t1>>

EOD;
        $this->assertSameSql($expect, $actual);
    }

    public function testFlagUnset(): void
    {
        $this->query->distinct()
            ->distinct(false)
            ->from('t1')
            ->cols(['t1.c1', 't1.c2', 't1.c3'])
        ;

        $actual = $this->query->__toString();

        $expect = <<<'EOD'

            SELECT
                <<t1>>.<<c1>>,
                <<t1>>.<<c2>>,
                <<t1>>.<<c3>>
            FROM
                <<t1>>

EOD;
        $this->assertSameSql($expect, $actual);
        $this->assertFalse($this->query->isDistinct());
    }

    public function testCols(): void
    {
        $this->assertFalse($this->query->hasCols());

        $this->query->cols([
            't1.c1',
            'c2' => 'a2',
            'COUNT(t1.c3)',
        ]);

        $this->assertTrue($this->query->hasCols());
        $this->assertTrue($this->query->hasCol('t1.c1'));
        $this->assertTrue($this->query->hasCol('c2'));
        $this->assertTrue($this->query->hasCol('a2'));
        $this->assertFalse($this->query->hasCol('no_such_column'));

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            SELECT
                <<t1>>.<<c1>>,
                c2 AS <<a2>>,
                COUNT(<<t1>>.<<c3>>)

EOD;
        $this->assertSameSql($expect, $actual);
    }

    public function testFrom(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1')
            ->from('t2')
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>,
                <<t2>>

EOD;
        $this->assertSameSql($expect, $actual);
    }

    public function testFromRaw(): void
    {
        $this->query->cols(['*']);
        $this->query->fromRaw('t1')
            ->fromRaw('t2')
        ;

        $actual = $this->query->__toString();
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                t1,
                t2

EOD;
        $this->assertSameSql($expect, $actual);
    }

    public function testDuplicateFromTable(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');

        $this->expectException(
            AuraSqlQueryException::class,
            "Cannot reference 'FROM t1' after 'FROM t1'",
        );
        $this->query->from('t1');
    }


    public function testDuplicateFromAlias(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');

        $this->expectException(
            AuraSqlQueryException::class,
            "Cannot reference 'FROM t2 AS t1' after 'FROM t1'",
        );
        $this->query->from('t2 AS t1');
    }

    public function testFromSubSelect(): void
    {
        $sub = 'SELECT * FROM t2';
        $this->query->cols(['*'])->fromSubSelect($sub, 'a2');
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                (
                    SELECT * FROM t2
                ) AS <<a2>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testDuplicateSubSelectTableRef(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');

        $this->expectException(
            AuraSqlQueryException::class,
            "Cannot reference 'FROM (SELECT ...) AS t1' after 'FROM t1'",
        );

        $sub = 'SELECT * FROM t2';
        $this->query->fromSubSelect($sub, 't1');
    }

    public function testFromSubSelectObject(): void
    {
        $sub = $this->newQuery();
        $sub->cols(['*'])
            ->from('t2')
            ->where('foo = :foo', ['foo' => 'bar'])
        ;

        $this->query->cols(['*'])
            ->fromSubSelect($sub, 'a2')
            ->where('a2.baz = :baz', ['baz' => 'dib'])
        ;

        $expect = <<<'EOD'

            SELECT
                *
            FROM
                (
                    SELECT
                        *
                    FROM
                        <<t2>>
                    WHERE
                        foo = :foo
                ) AS <<a2>>
            WHERE
                <<a2>>.<<baz>> = :baz

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testJoin(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');
        $this->query->join('left', 't2', 't1.id = t2.id');
        $this->query->join('inner', 't3 AS a3', 't2.id = a3.id');
        $this->query->from('t4');
        $this->query->join('natural', 't5');
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    LEFT JOIN <<t2>> ON <<t1>>.<<id>> = <<t2>>.<<id>>
                    INNER JOIN <<t3>> AS <<a3>> ON <<t2>>.<<id>> = <<a3>>.<<id>>,
                <<t4>>
                    NATURAL JOIN <<t5>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testJoinBeforeFrom(): void
    {
        $this->query->cols(['*']);
        $this->query->join('left', 't2', 't1.id = t2.id');
        $this->query->join('inner', 't3 AS a3', 't2.id = a3.id');
        $this->query->from('t1');
        $this->query->from('t4');
        $this->query->join('natural', 't5');
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    LEFT JOIN <<t2>> ON <<t1>>.<<id>> = <<t2>>.<<id>>
                    INNER JOIN <<t3>> AS <<a3>> ON <<t2>>.<<id>> = <<a3>>.<<id>>,
                <<t4>>
                    NATURAL JOIN <<t5>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testDuplicateJoinRef(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');

        $this->expectException(
            AuraSqlQueryException::class,
            "Cannot reference 'NATURAL JOIN t1' after 'FROM t1'",
        );
        $this->query->join('natural', 't1');
    }

    public function testJoinAndBind(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');
        $this->query->join(
            'left',
            't2',
            't1.id = t2.id AND t1.foo = :foo',
            ['foo' => 'bar'],
        );

        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
            LEFT JOIN <<t2>> ON <<t1>>.<<id>> = <<t2>>.<<id>> AND <<t1>>.<<foo>> = :foo

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $expect = ['foo' => 'bar'];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testLeftAndInnerJoin(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');
        $this->query->leftJoin('t2', 't1.id = t2.id');
        $this->query->innerJoin('t3 AS a3', 't2.id = a3.id');
        $this->query->join('natural', 't4');
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    LEFT JOIN <<t2>> ON <<t1>>.<<id>> = <<t2>>.<<id>>
                    INNER JOIN <<t3>> AS <<a3>> ON <<t2>>.<<id>> = <<a3>>.<<id>>
                    NATURAL JOIN <<t4>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testLeftAndInnerJoinWithBind(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');
        $this->query->leftJoin('t2', 't2.id = :t2_id', ['t2_id' => 'foo']);
        $this->query->innerJoin('t3 AS a3', 'a3.id = :a3_id', ['a3_id' => 'bar']);
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
            LEFT JOIN <<t2>> ON <<t2>>.<<id>> = :t2_id
            INNER JOIN <<t3>> AS <<a3>> ON <<a3>>.<<id>> = :a3_id

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $expect = ['t2_id' => 'foo', 'a3_id' => 'bar'];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testJoinSubSelect(): void
    {
        $sub1 = 'SELECT * FROM t2';
        $sub2 = 'SELECT * FROM t3';
        $this->query->cols(['*']);
        $this->query->from('t1');
        $this->query->joinSubSelect('left', $sub1, 'a2', 't2.c1 = a3.c1');
        $this->query->joinSubSelect('natural', $sub2, 'a3');
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    LEFT JOIN (
                        SELECT * FROM t2
                    ) AS <<a2>> ON <<t2>>.<<c1>> = <<a3>>.<<c1>>
                    NATURAL JOIN (
                        SELECT * FROM t3
                    ) AS <<a3>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testJoinSubSelectBeforeFrom(): void
    {
        $sub1 = 'SELECT * FROM t2';
        $sub2 = 'SELECT * FROM t3';
        $this->query->cols(['*']);
        $this->query->joinSubSelect('left', $sub1, 'a2', 't2.c1 = a3.c1');
        $this->query->joinSubSelect('natural', $sub2, 'a3');
        $this->query->from('t1');
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    LEFT JOIN (
                        SELECT * FROM t2
                    ) AS <<a2>> ON <<t2>>.<<c1>> = <<a3>>.<<c1>>
                    NATURAL JOIN (
                        SELECT * FROM t3
                    ) AS <<a3>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testDuplicateJoinSubSelectRef(): void
    {
        $this->query->cols(['*']);
        $this->query->from('t1');

        $this->expectException(
            AuraSqlQueryException::class,
            "Cannot reference 'NATURAL JOIN (SELECT ...) AS t1' after 'FROM t1'",
        );

        $sub2 = 'SELECT * FROM t3';
        $this->query->joinSubSelect('natural', $sub2, 't1');
    }

    public function testJoinSubSelectObject(): void
    {
        $sub = $this->newQuery();
        $sub->cols(['*'])->from('t2')->where('foo = :foo', ['foo' => 'bar']);

        $this->query->cols(['*']);
        $this->query->from('t1');
        $this->query->joinSubSelect('left', $sub, 'a3', 't2.c1 = a3.c1');
        $this->query->where('baz = :baz', ['baz' => 'dib']);

        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    LEFT JOIN (
                        SELECT
                            *
                        FROM
                            <<t2>>
                        WHERE
                            foo = :foo
                    ) AS <<a3>> ON <<t2>>.<<c1>> = <<a3>>.<<c1>>
            WHERE
                baz = :baz

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testJoinOrder(): void
    {
        $this->query->cols(['*']);
        $this->query
            ->from('t1')
            ->join('inner', 't2', 't2.id = t1.id')
            ->join('left', 't3', 't3.id = t2.id')
            ->from('t4')
            ->join('inner', 't5', 't5.id = t4.id')
        ;
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    INNER JOIN <<t2>> ON <<t2>>.<<id>> = <<t1>>.<<id>>
                    LEFT JOIN <<t3>> ON <<t3>>.<<id>> = <<t2>>.<<id>>,
                        <<t4>>
                    INNER JOIN <<t5>> ON <<t5>>.<<id>> = <<t4>>.<<id>>

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testJoinOnAndUsing(): void
    {
        $this->query->cols(['*']);
        $this->query
            ->from('t1')
            ->join('inner', 't2', 'ON t2.id = t1.id')
            ->join('left', 't3', 'USING (id)')
        ;
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<t1>>
                    INNER JOIN <<t2>> ON <<t2>>.<<id>> = <<t1>>.<<id>>
                    LEFT JOIN <<t3>> USING (id)

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testWhere(): void
    {
        $this->query->cols(['*']);
        $this->query->where('c1 = c2')
            ->where('c3 = :c3', ['c3' => 'foo'])
        ;
        $expect = <<<'EOD'

            SELECT
                *
            WHERE
                c1 = c2
                AND c3 = :c3

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = ['c3' => 'foo'];
        $this->assertSame($expect, $actual);
    }

    public function testOrWhere(): void
    {
        $this->query->cols(['*']);
        $this->query->orWhere('c1 = c2')
            ->orWhere('c3 = :c3', ['c3' => 'foo'])
        ;

        $expect = <<<'EOD'

            SELECT
                *
            WHERE
                c1 = c2
                OR c3 = :c3

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = ['c3' => 'foo'];
        $this->assertSame($expect, $actual);
    }

    public function testGroupBy(): void
    {
        $this->query->cols(['*']);
        $this->query->groupBy(['c1', 't2.c2']);
        $expect = <<<'EOD'

            SELECT
                *
            GROUP BY
                c1,
                <<t2>>.<<c2>>

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testHaving(): void
    {
        $this->query->cols(['*']);
        $this->query->having('c1 = c2')
            ->having('c3 = :c3', ['c3' => 'foo'])
        ;
        $expect = <<<'EOD'

            SELECT
                *
            HAVING
                c1 = c2
                AND c3 = :c3

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = ['c3' => 'foo'];
        $this->assertSame($expect, $actual);
    }

    public function testOrHaving(): void
    {
        $this->query->cols(['*']);
        $this->query->orHaving('c1 = c2')
            ->orHaving('c3 = :c3', ['c3' => 'foo'])
        ;
        $expect = <<<'EOD'

            SELECT
                *
            HAVING
                c1 = c2
                OR c3 = :c3

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $actual = $this->query->getBindValues();
        $expect = ['c3' => 'foo'];
        $this->assertSame($expect, $actual);
    }

    public function testOrderBy(): void
    {
        $this->query->cols(['*']);
        $this->query->orderBy(['c1', 'UPPER(t2.c2)']);
        $expect = <<<'EOD'

            SELECT
                *
            ORDER BY
                c1,
                UPPER(<<t2>>.<<c2>>)

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testGetterOnLimitAndOffset(): void
    {
        $this->query->cols(['*']);
        $this->query->limit(10);
        $this->query->offset(50);

        $this->assertSame(10, $this->query->getLimit());
        $this->assertSame(50, $this->query->getOffset());
    }

    public function testLimitOffset(): void
    {
        $this->query->cols(['*']);
        $this->query->limit(10);
        $expect = <<<'EOD'

            SELECT
                *
            LIMIT 10

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $this->query->offset(40);
        $expect = <<<'EOD'

            SELECT
                *
            LIMIT 10 OFFSET 40

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testPage(): void
    {
        $this->query->cols(['*']);
        $this->query->page(5);
        $expect = <<<'EOD'

            SELECT
                *
            LIMIT 10 OFFSET 40

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testForUpdate(): void
    {
        $this->query->cols(['*']);
        $this->query->forUpdate();
        $expect = <<<'EOD'

            SELECT
                *
            FOR UPDATE

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testUnion(): void
    {
        $this->query->cols(['c1'])
            ->from('t1')
            ->union()
            ->cols(['c2'])
            ->from('t2')
        ;
        $expect = <<<'EOD'

            SELECT
                c1
            FROM
                <<t1>>
            UNION
            SELECT
                c2
            FROM
                <<t2>>

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testUnionAll(): void
    {
        $this->query->cols(['c1'])
            ->from('t1')
            ->unionAll()
            ->cols(['c2'])
            ->from('t2')
        ;
        $expect = <<<'EOD'

            SELECT
                c1
            FROM
                <<t1>>
            UNION ALL
            SELECT
                c2
            FROM
                <<t2>>

EOD;

        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testAutobind(): void
    {
        // do these out of order
        $this->query->having('baz IN (:baz)', ['baz' => ['dib', 'zim', 'gir']]);
        $this->query->where('foo = :foo', ['foo' => 'bar']);
        $this->query->cols(['*']);

        $expect = <<<'EOD'

            SELECT
                *
            WHERE
                foo = :foo
            HAVING
                baz IN (:baz)

EOD;
        $actual = $this->query->__toString();
        $this->assertSameSql($expect, $actual);

        $expect = [
            'baz' => ['dib', 'zim', 'gir'],
            'foo' => 'bar',
        ];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testAddColWithAlias(): void
    {
        $this->query->cols([
            'foo',
            'bar',
            'table.noalias',
            'col1 as alias1',
            'col2 alias2',
            'table.proper' => 'alias_proper',
            'legacy invalid as alias still works',
            'overwrite as alias1',
        ]);

        // add separately to make sure we don't overwrite sequential keys
        $this->query->cols([
            'baz',
            'dib',
        ]);

        $actual = $this->query->__toString();

        $expect = <<<'EOD'

            SELECT
                foo,
                bar,
                <<table>>.<<noalias>>,
                overwrite AS <<alias1>>,
                col2 AS <<alias2>>,
                <<table>>.<<proper>> AS <<alias_proper>>,
                legacy invalid AS <<alias still works>>,
                baz,
                dib

EOD;
        $this->assertSameSql($expect, $actual);
    }

    public function testGetCols(): void
    {
        $this->query->cols(['valueBar' => 'aliasFoo']);

        $cols = $this->query->getCols();

        $this->assertTrue(\is_array($cols));
        $this->assertTrue(1 === \count($cols));
        $this->assertArrayHasKey('aliasFoo', $cols);
    }

    public function testRemoveColsAlias(): void
    {
        $this->query->cols(['valueBar' => 'aliasFoo', 'valueBaz' => 'aliasBaz']);

        $this->assertTrue($this->query->removeCol('aliasFoo'));
        $cols = $this->query->getCols();

        $this->assertTrue(\is_array($cols));
        $this->assertTrue(1 === \count($cols));
        $this->assertArrayNotHasKey('aliasFoo', $cols);
    }

    public function testRemoveColsName(): void
    {
        $this->query->cols(['valueBar', 'valueBaz' => 'aliasBaz']);

        $this->assertTrue($this->query->removeCol('valueBar'));
        $cols = $this->query->getCols();

        $this->assertTrue(\is_array($cols));
        $this->assertTrue(1 === \count($cols));
        $this->assertNotContains('valueBar', $cols);
    }

    public function testRemoveColsNotFound(): void
    {
        $this->assertFalse($this->query->removeCol('valueBar'));
    }

    public function testIssue47(): void
    {
        // sub select
        $sub = $this->newQuery()
            ->cols(['*'])
            ->from('table1 AS t1')
        ;
        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<table1>> AS <<t1>>

EOD;
        $actual = $sub->__toString();
        $this->assertSameSql($expect, $actual);

        // main select
        $select = $this->newQuery()
            ->cols(['*'])
            ->from('table2 AS t2')
            ->where('field IN (:field)', ['field' => $sub])
        ;

        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<table2>> AS <<t2>>
            WHERE
                field IN (SELECT
                *
            FROM
                <<table1>> AS <<t1>>)

EOD;
        $actual = $select->__toString();
        $this->assertSameSql($expect, $actual);
    }

    public function testIssue49(): void
    {
        $this->assertSame(0, $this->query->getPage());
        $this->assertSame(10, $this->query->getPaging());
        $this->assertSame(0, $this->query->getLimit());
        $this->assertSame(0, $this->query->getOffset());

        $this->query->page(3);
        $this->assertSame(3, $this->query->getPage());
        $this->assertSame(10, $this->query->getPaging());
        $this->assertSame(10, $this->query->getLimit());
        $this->assertSame(20, $this->query->getOffset());

        $this->query->limit(10);
        $this->assertSame(0, $this->query->getPage());
        $this->assertSame(10, $this->query->getPaging());
        $this->assertSame(10, $this->query->getLimit());
        $this->assertSame(0, $this->query->getOffset());

        $this->query->page(3);
        $this->query->setPaging(50);
        $this->assertSame(3, $this->query->getPage());
        $this->assertSame(50, $this->query->getPaging());
        $this->assertSame(50, $this->query->getLimit());
        $this->assertSame(100, $this->query->getOffset());

        $this->query->offset(10);
        $this->assertSame(0, $this->query->getPage());
        $this->assertSame(50, $this->query->getPaging());
        $this->assertSame(0, $this->query->getLimit());
        $this->assertSame(10, $this->query->getOffset());
    }

    public function testWhereSubSelectImportsBoundValues(): void
    {
        // sub select
        $sub = $this->newQuery()
            ->cols(['*'])
            ->from('table1 AS t1')
            ->where('t1.foo = :foo', ['foo' => 'bar'])
        ;

        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<table1>> AS <<t1>>
            WHERE
                <<t1>>.<<foo>> = :foo

EOD;
        $actual = $sub->getStatement();
        $this->assertSameSql($expect, $actual);

        // main select
        $select = $this->newQuery()
            ->cols(['*'])
            ->from('table2 AS t2')
            ->where('field IN (:field)', ['field' => $sub])
            ->where('t2.baz = :baz', ['baz' => 'dib'])
        ;

        $expect = <<<'EOD'

            SELECT
                *
            FROM
                <<table2>> AS <<t2>>
            WHERE
                field IN (SELECT
                        *
                    FROM
                        <<table1>> AS <<t1>>
                    WHERE
                        <<t1>>.<<foo>> = :foo)
            AND <<t2>>.<<baz>> = :baz

EOD;

        // B.b.: The _2_2_ means "2nd query, 2nd sequential bound value". It's
        // the 2nd bound value because the 1st one is imported fromt the 1st
        // query (the subselect).

        $actual = $select->getStatement();
        $this->assertSameSql($expect, $actual);

        $expect = [
            'foo' => 'bar',
            'baz' => 'dib',
        ];
        $actual = $select->getBindValues();
        $this->assertSame($expect, $actual);
    }

    public function testUnionSelectCanHaveSameAliasesInDifferentSelects(): void
    {
        $select = $this->query
            ->cols([
                '...',
            ])
            ->from('a')
            ->join('INNER', 'c', 'a_cid = c_id')
            ->union()
            ->cols([
                '...',
            ])
            ->from('b')
            ->join('INNER', 'c', 'b_cid = c_id')
        ;

        $expected = <<<'EOD'
SELECT
                    ...
                    FROM
                    <<a>>
                    INNER JOIN <<c>> ON a_cid = c_id
                    UNION
                    SELECT
                    ...
                    FROM
                    <<b>>
                    INNER JOIN <<c>> ON b_cid = c_id
EOD;

        $actual = (string) $select->getStatement();
        $this->assertSameSql($expected, $actual);
    }

    public function testResetUnion(): void
    {
        $select = $this->query
            ->cols([
                '...',
            ])
            ->from('a')
            ->union()
            ->cols([
                '...',
            ])
            ->from('b')
        ;

        // should remove all prior queries and just leave the last.
        $select->resetUnions();
        $expected = <<<'EOD'
SELECT
                    ...
                    FROM
                    <<b>>
EOD;

        $actual = (string) $select->getStatement();
        $this->assertSameSql($expected, $actual);
    }

    public function testWhereClosure(): void
    {
        $select = $this->query
            ->cols(['foo', 'bar'])
            ->from('baz')
            ->where(static function ($select): void {
                $select->where('foo > 1')
                    ->where('bar > 1')
                ;
            })->orWhere(static function ($select): void {
                $select->where('foo < 1')
                    ->where('bar < 1')
                ;
            })->where(static function ($select): void {
                // do nothing
            })
        ;

        $expect = <<<'EOD'

            SELECT
                foo,
                bar
            FROM
                <<baz>>
            WHERE
                (
                    foo > 1
                    AND bar > 1
                )
                OR (
                    foo < 1
                    AND bar < 1
                )

EOD;
        $actual = (string) $select->getStatement();
        $this->assertSameSql($expect, $actual);
    }

    public function testHavingClosure(): void
    {
        $select = $this->query
            ->cols(['foo', 'bar'])
            ->from('baz')
            ->having(static function (SelectInterface $select): void {
                $select->having('foo > 1')
                    ->having('bar > 1')
                ;
            })->orHaving(static function (SelectInterface $select): void {
                $select->having('foo < 1')
                    ->having('bar < 1')
                ;
            })->having(static function ($select): void {
                // do nothing
            })
        ;

        $expect = <<<'EOD'

            SELECT
                foo,
                bar
            FROM
                <<baz>>
            HAVING
                (
                    foo > 1
                    AND bar > 1
                )
                OR (
                    foo < 1
                    AND bar < 1
                )

EOD;
        $actual = (string) $select->getStatement();
        $this->assertSameSql($expect, $actual);
    }
}
