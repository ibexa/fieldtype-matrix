<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Search\Solr;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\FieldTypeMatrix\Search\Criterion\Column;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

final class ColumnCriterionVisitor extends CriterionVisitor
{
    public function canVisit(Criterion $criterion): bool
    {
        return $criterion instanceof Column;
    }

    /**
     * @param \Ibexa\Contracts\FieldTypeMatrix\Search\Criterion\Column $criterion
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null): string
    {
        $name = $criterion->getFieldDefIdentifier() . '_col_' . $criterion->getColumn() . '_value_ms';

        $queries = [];
        foreach ((array)$criterion->value as $value) {
            if ($criterion->operator === Operator::CONTAINS) {
                $queries[] = $name . ':*' . $this->escapeExpressions($value) . '*';
            } else {
                $queries[] = $name . ':"' . $this->escapeQuote($value, true) . '"';
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
