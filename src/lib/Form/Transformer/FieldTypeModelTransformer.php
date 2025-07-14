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
final readonly class FieldTypeModelTransformer implements DataTransformerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function transform(mixed $value): array
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

    public function reverseTransform(mixed $value): Value
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
