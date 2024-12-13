<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Transformer;

use Ibexa\FieldTypeMatrix\FieldType\Value;
use Ibexa\FieldTypeMatrix\FieldType\Value\Row;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<\Ibexa\FieldTypeMatrix\FieldType\Value, array<string, mixed>>
 */
class FieldTypeModelTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        $hash['entries'] = [];

        if ($value === null) {
            return $hash;
        }

        foreach ($value->getRows() as $row) {
            $hash['entries'][] = $row->getCells();
        }

        return $hash;
    }

    public function reverseTransform($value)
    {
        $entries = $value['entries'] ?? [];

        foreach ($entries as $entry) {
            $row = new Row($entry);

            if (!$row->isEmpty()) {
                $rows[] = $row;
            }
        }

        return new Value($rows ?? []);
    }
}
