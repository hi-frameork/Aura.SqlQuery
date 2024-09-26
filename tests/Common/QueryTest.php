<?php

declare(strict_types=1);

namespace Aura\SqlQuery\Common;

use Aura\SqlQuery\QueryFactory;
use PHPUnit\Framework\TestCase;

abstract class QueryTest extends TestCase
{
    protected QueryFactory $query_factory;

    protected string $query_type;

    protected string $db_type = 'Common';

    protected SelectInterface|InsertInterface|DeleteInterface|UpdateInterface $query;

    protected function setUp(): void
    {
        parent::setUp();
        $this->query_factory = new QueryFactory($this->db_type);
        $this->query = $this->newQuery();
    }

    protected function newQuery()
    {
        $method = 'new' . $this->query_type;
        return $this->query_factory->{$method}();
    }

    protected function assertSameSql($expect, $actual): void
    {
        // remove leading and trailing whitespace per block and line
        $expect = \trim($expect);
        $expect = \preg_replace('/^[ \t]*/m', '', $expect);
        $expect = \preg_replace('/[ \t]*$/m', '', $expect);

        // convert "<<" and ">>" to the correct identifier quotes
        $expect = $this->requoteIdentifiers($expect);

        // remove leading and trailing whitespace per block and line
        $actual = \trim($actual);
        $actual = \preg_replace('/^[ \t]*/m', '', $actual);
        $actual = \preg_replace('/[ \t]*$/m', '', $actual);

        // normalize line endings to be sure tests will pass on windows and mac
        $expect = \preg_replace('/\r\n|\n|\r/', \PHP_EOL, $expect);
        $actual = \preg_replace('/\r\n|\n|\r/', \PHP_EOL, $actual);

        // are they the same now?
        $this->assertSame($expect, $actual);
    }

    protected function requoteIdentifiers($string)
    {
        $string = \str_replace('<<', $this->query->getQuoteNamePrefix(), $string);
        return \str_replace('>>', $this->query->getQuoteNameSuffix(), $string);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testBindValues(): void
    {
        $actual = $this->query->getBindValues();
        $this->assertSame([], $actual);

        $expect = ['foo' => 'bar', 'baz' => 'dib'];
        $this->query->bindValues($expect);
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);

        $this->query->bindValues(['zim' => 'gir']);
        $expect = ['foo' => 'bar', 'baz' => 'dib', 'zim' => 'gir'];
        $actual = $this->query->getBindValues();
        $this->assertSame($expect, $actual);

        $this->query->resetBindValues();
        $this->assertEmpty($this->query->getBindValues());
    }
}
