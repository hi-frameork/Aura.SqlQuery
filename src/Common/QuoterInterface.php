<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * A quoting mechanism for identifier names (not values).
 *
 * @package Aura.SqlQuery
 */
interface QuoterInterface
{
    /**
     * Returns the prefix to use when quoting identifier names.
     */
    public function getQuoteNamePrefix(): string;

    /**
     * Returns the suffix to use when quoting identifier names.
     */
    public function getQuoteNameSuffix(): string;

    /**
     * Quotes a single identifier name (table, table alias, table column,
     * index, sequence).
     *
     * If the name contains `' AS '`, this method will separately quote the
     * parts before and after the `' AS '`.
     *
     * If the name contains a space, this method will separately quote the
     * parts before and after the space.
     *
     * If the name contains a dot, this method will separately quote the
     * parts before and after the dot.
     *
     * return the quoted identifier name
     *
     * @param string $spec the identifier name to quote
     */
    public function quoteName(string $spec): string;

    /**
     * Quotes all fully-qualified identifier names ("table.col") in a string,
     * typically an SQL snippet for a SELECT clause.
     *
     * Does not quote identifier names that are string literals (i.e., inside
     * single or double quotes).
     *
     * Looks for a trailing ' AS alias' and quotes the alias as well.
     *
     * return the string with names quoted in it
     *
     * @param string $text the string in which to quote fully-qualified
     *                     identifier names to quote
     */
    public function quoteNamesIn(string $text): string|array;
}
