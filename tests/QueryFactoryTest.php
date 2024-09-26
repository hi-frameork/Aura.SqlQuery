<?php

declare(strict_types=1);

namespace Aura\SqlQuery;

use PHPUnit\Framework\TestCase;

class QueryFactoryTest extends TestCase
{
    /**
     * @dataProvider provider
     *
     * @param mixed $db_type
     * @param mixed $common
     * @param mixed $query_type
     * @param mixed $expect
     */
    public function test_Instance($db_type, $common, $query_type, $expect): void
    {
        $query_factory = new QueryFactory($db_type, $common);
        $method = 'new' . $query_type;
        $actual = $query_factory->{$method}();
        $this->assertInstanceOf($expect, $actual);
    }

    /**
     * @return array<int,array<int,mixed>>
     */
    public static function provider(): array
    {
        return [
            // db-specific
            ['Common', '', 'Select', Common\Select::class],
            ['Common', '', 'Insert', Common\Insert::class],
            ['Common', '', 'Update', Common\Update::class],
            ['Common', '', 'Delete', Common\Delete::class],
            ['Mysql', '', 'Select', Mysql\Select::class],
            ['Mysql', '', 'Insert', Mysql\Insert::class],
            ['Mysql', '', 'Update', Mysql\Update::class],
            ['Mysql', '', 'Delete', Mysql\Delete::class],
            ['Pgsql', '', 'Select', Postgres\Select::class],
            ['Pgsql', '', 'Insert', Postgres\Insert::class],
            ['Pgsql', '', 'Update', Postgres\Update::class],
            ['Pgsql', '', 'Delete', Postgres\Delete::class],
            ['postgres', '', 'Select', Postgres\Select::class],
            ['postgres', '', 'Insert', Postgres\Insert::class],
            ['postgres', '', 'Update', Postgres\Update::class],
            ['postgres', '', 'Delete', Postgres\Delete::class],
            ['Sqlite', '', 'Select', SQLite\Select::class],
            ['Sqlite', '', 'Insert', SQLite\Insert::class],
            ['Sqlite', '', 'Update', SQLite\Update::class],
            ['Sqlite', '', 'Delete', SQLite\Delete::class],
            ['Sqlsrv', '', 'Select', SQLServer\Select::class],
            ['Sqlsrv', '', 'Insert', SQLServer\Insert::class],
            ['Sqlsrv', '', 'Update', SQLServer\Update::class],
            ['Sqlsrv', '', 'Delete', SQLServer\Delete::class],

            // force common
            ['Common', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Common', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Common', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Common', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Mysql', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Mysql', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Mysql', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Mysql', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Pgsql', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Pgsql', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Pgsql', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Pgsql', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Sqlite', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Sqlite', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Sqlite', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Sqlite', QueryFactory::COMMON, 'Delete', Common\Delete::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Select', Common\Select::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Insert', Common\Insert::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Update', Common\Update::class],
            ['Sqlsrv', QueryFactory::COMMON, 'Delete', Common\Delete::class],
        ];
    }
}
