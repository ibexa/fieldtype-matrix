<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType;

use Ibexa\Core\FieldType\Value as BaseValue;
use Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection;

class Value extends BaseValue
{
    /** @var \Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection */
    protected $rows;

    /**
     * @param array $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = new RowsCollection($rows);
    }

    /**
     * @return \Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection
     */
    public function getRows(): RowsCollection
    {
        return $this->rows;
    }

    /**
     * @param \Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection $rows
     */
    public function setRows(RowsCollection $rows): void
    {
        $this->rows = $rows;
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }
}
