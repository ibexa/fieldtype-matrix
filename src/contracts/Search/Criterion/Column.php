<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\FieldTypeMatrix\Search\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator\Specifications;

final class Column extends Criterion
{
    private string $fieldDefIdentifier;

    private string $column;

    public function __construct(
        string $fieldDefIdentifier,
        string $column,
        string $value,
        string $operator = Operator::EQ
    ) {
        parent::__construct(null, $operator, $value);

        $this->fieldDefIdentifier = $fieldDefIdentifier;
        $this->column = $column;
    }

    public function getFieldDefIdentifier(): string
    {
        return $this->fieldDefIdentifier;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getSpecifications(): array
    {
        return [
            new Specifications(Operator::IN, Specifications::FORMAT_ARRAY),
            new Specifications(Operator::EQ, Specifications::FORMAT_SINGLE),
            new Specifications(Operator::CONTAINS, Specifications::FORMAT_SINGLE),
        ];
    }
}
