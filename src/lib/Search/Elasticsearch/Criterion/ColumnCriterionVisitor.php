<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Search\Elasticsearch\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Elasticsearch\Query\CriterionVisitor;
use Ibexa\Contracts\Elasticsearch\Query\LanguageFilter;
use Ibexa\Contracts\FieldTypeMatrix\Search\Criterion\Column;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\BoolQuery;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\TermsQuery;
use Ibexa\Elasticsearch\ElasticSearch\QueryDSL\WildcardQuery;

final class ColumnCriterionVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion, LanguageFilter $languageFilter): bool
    {
        return $criterion instanceof Column;
    }

    /**
     * @param \Ibexa\Contracts\FieldTypeMatrix\Search\Criterion\Column $criterion
     */
    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, LanguageFilter $languageFilter): array
    {
        $name = $criterion->getFieldDefIdentifier() . '_col_' . $criterion->getColumn() . '_value_ms';

        if ($criterion->operator === Criterion\Operator::CONTAINS) {
            $bool = new BoolQuery();
            foreach ((array) $criterion->value as $value) {
                $wildcard = new WildcardQuery();
                $wildcard->withField($name);
                $wildcard->withValue('*' . $value . '*');

                $bool->addShould($wildcard);
            }
        } else {
            $terms = new TermsQuery();
            $terms->withField($name);
            $terms->withValue((array)$criterion->value);

            return $terms->toArray();
        }
    }
}
