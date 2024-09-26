<?php

declare(strict_types=1);
/**
 * This file is part of Aura for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

namespace Aura\SqlQuery\Common;

/**
 * Base builder for all query objects.
 *
 * @package Aura.SqlQuery
 */
abstract class Builder implements BuilderInterface
{
    public function buildFlags(array $flags): string
    {
        if ([] === $flags) {
            return ''; // not applicable
        }

        return ' ' . \implode(' ', \array_keys($flags));
    }

    public function buildWhere(array $where): string
    {
        if (empty($where)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'WHERE' . $this->indent($where);
    }

    public function buildOrderBy(array $order_by): string
    {
        if (empty($order_by)) {
            return ''; // not applicable
        }

        return \PHP_EOL . 'ORDER BY' . $this->indentCsv($order_by);
    }

    public function buildLimit(int $limit): string
    {
        if (empty($limit)) {
            return '';
        }
        return \PHP_EOL . "LIMIT {$limit}";
    }

    public function buildLimitOffset(int $limit, int $offset): string
    {
        $clause = '';

        if (! empty($limit)) {
            $clause .= "LIMIT {$limit}";
        }

        if (! empty($offset)) {
            $clause .= " OFFSET {$offset}";
        }

        if (! empty($clause)) {
            $clause = \PHP_EOL . \trim($clause);
        }

        return $clause;
    }

    public function indentCsv(array $list): string
    {
        return \PHP_EOL . '    '
             . \implode(',' . \PHP_EOL . '    ', $list);
    }

    public function indent(array $list): string
    {
        return \PHP_EOL . '    '
             . \implode(\PHP_EOL . '    ', $list);
    }
}
