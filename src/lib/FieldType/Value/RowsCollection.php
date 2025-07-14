<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType\Value;

use ArrayObject;

/**
 * @extends ArrayObject<int, \Ibexa\FieldTypeMatrix\FieldType\Value\Row>
 */
final class RowsCollection extends ArrayObject
{
    /**
     * @param \Ibexa\FieldTypeMatrix\FieldType\Value\Row[] $elements
     */
    public function __construct(array $elements = [])
    {
        parent::__construct();

        foreach ($elements as $index => $element) {
            $this->offsetSet($index, $element);
        }
    }
}
