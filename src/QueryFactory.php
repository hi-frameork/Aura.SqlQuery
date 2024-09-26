<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery;

use Aura\SqlQuery\Common\QuoterInterface;

/**
 * Creates query statement objects.
 *
 * @package Aura.SqlQuery
 */
class QueryFactory
{
    /**
     * Use the 'common' driver instead of a database-specific one.
     */
    public const COMMON = 'common';

    /**
     * What database are we building for?
     */
    protected string $db;

    /**
     * Build "common" query objects regardless of database type?
     */
    protected bool $common = false;

    /**
     * A map of `table.col` names to last-insert-id names.
     */
    protected array $last_insert_id_names = [];

    /**
     * A Quoter for identifiers.
     */
    protected QuoterInterface $quoter;

    /**
     * The class of the InsertBuilder.
     */
    private string $insert_builder_class = '';

    /**
     * The class of the DeleteBuilder.
     */
    private string $delete_builder_class = '';

    /**
     * The class of the UpdateBuilder.
     */
    private string $update_builder_class = '';

    /**
     * The class of the SelectBuilder.
     */
    private string $select_builder_class = '';

    /**
     * Constructor.
     *
     * @param string $db     the database type
     * @param string $common pass the constant self::COMMON to force common
     *                       query objects instead of db-specific ones
     */
    public function __construct(string $db = '', string $common = '')
    {
        if (self::COMMON === $common) {
            $this->common = true;
            $this->db = 'Common';
        } else {
            $this->db = match (\mb_strtolower($db)) {
                'mysql' => 'MySQL',
                'pgsql' => 'Postgres',
                'postgres' => 'Postgres',
                'sqlite' => 'SQLite',
                'sqlsrv' => 'SQLServer',
                'sqlserver' => 'SQLServer',
                default => 'Common',
            };
        }

        $this->quoter = $this->newQuoter();
    }

    /**
     * Sets the last-insert-id names to be used for Insert queries..
     *
     * @param array<string,mixed> $last_insert_id_names A map of `table.col` names to
     *                                                  last-insert-id names.
     */
    public function setLastInsertIdNames(array $last_insert_id_names): void
    {
        $this->last_insert_id_names = $last_insert_id_names;
    }

    /**
     * Returns a new SELECT object.
     */
    public function newSelect(): Common\SelectInterface
    {
        if ('' === $this->select_builder_class) {
            $this->select_builder_class = $this->getBuilderClass('Select');
        }

        /** @psalm-var class-string $queryClass */
        $queryClass = "Aura\SqlQuery\\{$this->db}\Select";

        return new $queryClass(
            $this->quoter,
            new $this->select_builder_class,
        );
    }

    /**
     * Returns a new INSERT object.
     */
    public function newInsert(): Common\InsertInterface
    {
        if ('' === $this->insert_builder_class) {
            $this->insert_builder_class = $this->getBuilderClass('Insert');
        }

        /** @var class-string $queryClass */
        $queryClass = "Aura\SqlQuery\\{$this->db}\Insert";

        $insert = new $queryClass(
            $this->quoter,
            new $this->insert_builder_class,
        );
        $insert->setLastInsertIdNames($this->last_insert_id_names);

        return $insert;
    }

    /**
     * Returns a new UPDATE object.
     */
    public function newUpdate(): Common\UpdateInterface
    {
        if ('' === $this->update_builder_class) {
            $this->update_builder_class = $this->getBuilderClass('Update');
        }

        /** @var class-string $queryClass */
        $queryClass = "Aura\SqlQuery\\{$this->db}\Update";

        return new $queryClass(
            $this->quoter,
            new $this->update_builder_class,
        );
    }

    /**
     * Returns a new DELETE object.
     */
    public function newDelete(): Common\DeleteInterface
    {
        if ('' === $this->delete_builder_class) {
            $this->delete_builder_class = $this->getBuilderClass('Delete');
        }

        /** @var class-string $queryClass */
        $queryClass = "Aura\SqlQuery\\{$this->db}\Delete";

        return new $queryClass(
            $this->quoter,
            new $this->delete_builder_class,
        );
    }

    /**
     * @return class-string
     */
    protected function getBuilderClass(string $query): string
    {
        $builderClass = "Aura\SqlQuery\\{$this->db}\\{$query}Builder";

        if ($this->common || ! \class_exists($builderClass)) {
            return "Aura\SqlQuery\\Common\\{$query}Builder";
        }

        return $builderClass;
    }

    /**
     * Returns a new Quoter for the database driver.
     */
    protected function newQuoter(): QuoterInterface
    {
        $quoterClass = "Aura\SqlQuery\\{$this->db}\Quoter";
        if (! \class_exists($quoterClass)) {
            $quoterClass = Common\Quoter::class;
        }
        return new $quoterClass;
    }
}
