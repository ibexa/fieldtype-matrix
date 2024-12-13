<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL;

use Ibexa\Core\Base\Exceptions\BadStateException;
use Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection;

class FieldValueResolver
{
    /** @var iterable<\Ibexa\FieldTypeMatrix\GraphQL\Strategy\ContentResolvingStrategyInterface> */
    private iterable $strategies;

    /**
     * @param iterable<\Ibexa\FieldTypeMatrix\GraphQL\Strategy\ContentResolvingStrategyInterface> $strategies
     */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     */
    public function resolveMatrixFieldValue(object $item, string $fieldDefIdentifier): RowsCollection
    {
        $silentRows = [];
        $content = null;

        foreach ($this->strategies as $strategy) {
            if (!$strategy->supports($item)) {
                continue;
            }

            $content = $strategy->resolveContent($item);
        }

        if ($content === null) {
            throw new BadStateException(
                '$item',
                'GraphQL item cannot be resolved to a content.'
            );
        }

        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value|null $fieldValue */
        $fieldValue = $content->getFieldValue($fieldDefIdentifier);
        if ($fieldValue === null) {
            return new RowsCollection();
        }
        foreach ($fieldValue->getRows() as $row) {
            $silentRows[] = new SilentRow($row->getCells());
        }

        return new RowsCollection($silentRows);
    }
}
