<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL;

use Ibexa\GraphQL\Value\Item;
use Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection;

class FieldValueResolver
{
    public function resolveMatrixFieldValue(Item $item, string $fieldDefIdentifier): RowsCollection
    {
        $silentRows = [];

        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection $rows $rows */
        $rows = $item->getContent()->getFieldValue($fieldDefIdentifier)->getRows();
        foreach ($rows as $row) {
            $silentRows[] = new SilentRow($row->getCells());
        }

        return new RowsCollection($silentRows);
    }
}

class_alias(FieldValueResolver::class, 'EzSystems\EzPlatformMatrixFieldtype\GraphQL\FieldValueResolver');
