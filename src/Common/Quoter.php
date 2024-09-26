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
class Quoter implements QuoterInterface
{
    /**
     * The prefix to use when quoting identifier names.
     */
    protected string $quote_name_prefix = '"';

    /**
     * The suffix to use when quoting identifier names.
     */
    protected string $quote_name_suffix = '"';

    public function getQuoteNamePrefix(): string
    {
        return $this->quote_name_prefix;
    }

    public function getQuoteNameSuffix(): string
    {
        return $this->quote_name_suffix;
    }

    public function quoteName(string $spec): string
    {
        $spec = \trim($spec);
        $seps = [' AS ', ' ', '.'];
        foreach ($seps as $sep) {
            $pos = \mb_strripos($spec, $sep);
            if ($pos) {
                return $this->quoteNameWithSeparator($spec, $sep, $pos);
            }
        }
        return $this->replaceName($spec);
    }

    protected function quoteNameWithSeparator(string $spec, string $sep, int $pos): string
    {
        $len = \mb_strlen($sep);
        $part1 = $this->quoteName(\mb_substr($spec, 0, $pos));
        $part2 = $this->replaceName(\mb_substr($spec, $pos + $len));
        return "{$part1}{$sep}{$part2}";
    }

    public function quoteNamesIn(string $text): string|array
    {
        $list = $this->getListForQuoteNamesIn($text);
        $last = \count($list) - 1;
        $text = null;
        foreach ($list as $key => $val) {
            // skip elements 2, 5, 8, 11, etc. as artifacts of the back-
            // referenced split; these are the trailing/ending quote
            // portions, and already included in the previous element.
            // this is the same as skipping every third element from zero.
            if (($key + 1) % 3) {
                $text .= $this->quoteNamesInLoop($val, $key == $last);
            }
        }
        return $text;
    }

    /**
     * Returns a list of candidate elements for quoting.
     *
     * @param string $text the text to split into quoting candidates
     */
    protected function getListForQuoteNamesIn(string $text): array|bool
    {
        // look for ', ", \', or \" in the string.
        // match closing quotes against the same number of opening quotes.
        $apos = "'";
        $quot = '"';
        return \preg_split(
            "/(({$apos}+|{$quot}+|\\{$apos}+|\\{$quot}+).*?\\2)/",
            $text,
            -1,
            \PREG_SPLIT_DELIM_CAPTURE,
        );
    }

    /**
     * The in-loop functionality for quoting identifier names.
     *
     * @param string $val     the name to be quoted
     * @param bool   $is_last Is this the last loop?
     */
    protected function quoteNamesInLoop(string $val, bool $is_last): string|array
    {
        if ($is_last) {
            return $this->replaceNamesAndAliasIn($val);
        }
        return $this->replaceNamesIn($val);
    }

    /**
     * Replaces the names and alias in a string.
     *
     * @param string $val the name to be quoted
     */
    protected function replaceNamesAndAliasIn(string $val): string|array
    {
        $quoted = $this->replaceNamesIn($val);
        $pos = \mb_strripos($quoted, ' AS ');
        if (false !== $pos) {
            $alias = $this->replaceName(\mb_substr($quoted, $pos + 4));
            $quoted = \mb_substr($quoted, 0, $pos) . " AS {$alias}";
        }
        return $quoted;
    }

    /**
     * Quotes an identifier name (table, index, etc); ignores empty values and
     * values of '*'.
     *
     * return the quoted identifier name
     *
     * @param string $name the identifier name to quote
     *
     * @see quoteName()
     */
    protected function replaceName(string $name): string
    {
        $name = \trim($name);
        if ('*' === $name) {
            return $name;
        }

        return $this->quote_name_prefix
             . $name
             . $this->quote_name_suffix;
    }

    /**
     * Quotes all fully-qualified identifier names ("table.col") in a string.
     *
     * return the string with names quoted in it
     *
     * @param string $text the string in which to quote fully-qualified
     *                     identifier names to quote
     *
     * @see quoteNamesIn()
     */
    protected function replaceNamesIn(string $text): string|array
    {
        $is_string_literal = \str_contains($text, "'")
                        || \str_contains($text, '"');
        if ($is_string_literal) {
            return $text;
        }

        $word = '[a-z_][a-z0-9_]*';

        $find = "/(\\b)({$word})\\.({$word})(\\b)/i";

        $repl = '$1'
              . $this->quote_name_prefix
              . '$2'
              . $this->quote_name_suffix
              . '.'
              . $this->quote_name_prefix
              . '$3'
              . $this->quote_name_suffix
              . '$4';

        return \preg_replace($find, $repl, $text);
    }
}
